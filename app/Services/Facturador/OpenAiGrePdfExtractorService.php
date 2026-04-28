<?php

namespace App\Services\Facturador;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

class OpenAiGrePdfExtractorService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
        $this->model  = config('services.openai.model', 'gpt-4o-mini');
    }

    public function extract(UploadedFile $pdf): array
    {
        if ($this->apiKey === '') {
            throw new \RuntimeException('OPENAI_API_KEY no configurada en .env');
        }

        $text = $this->extractText($pdf);

        if (strlen(trim($text)) < 30) {
            throw new \RuntimeException('No se pudo extraer texto del PDF. El archivo puede estar escaneado o protegido.');
        }

        return $this->callOpenAi($text);
    }

    private function extractText(UploadedFile $pdf): string
    {
        $document = (new Parser())->parseFile($pdf->getRealPath());
        $lines = explode("\n", $document->getText());
        $cleaned = [];

        foreach ($lines as $line) {
            $line = preg_replace('/[^\x20-\x7EáéíóúÁÉÍÓÚñÑ°%$.,\-:\/()]/u', ' ', $line);
            $line = preg_replace('/\s{2,}/', ' ', trim((string) $line));
            if ($line !== '') {
                $cleaned[] = $line;
            }
        }

        return implode("\n", $cleaned);
    }

    private function callOpenAi(string $cleanText): array
    {
        $text = mb_substr($cleanText, 0, 8000);

        $prompt = <<<PROMPT
Eres un extractor de Guías de Remisión Electrónica (GRE) SUNAT Perú.
Devuelve SOLO JSON válido, sin markdown ni comentarios.

IMPORTANTE:
- NO copies serie, número ni fecha/hora de emisión del PDF. Esos datos pertenecen al emisor original y el sistema generará los propios.
- Extrae solo datos operativos para prellenar un formulario editable.
- Si un dato no aparece, usa null.
- No inventes ubigeos. Si no aparecen explícitos, usa null.
- Normaliza números quitando separadores de miles: "5,025.03" => 5025.03.

Mapeo requerido:
- Motivo "Venta" => codigo_motivo_traslado "01", descripcion "Venta".
- Modalidad "Privado" => codigo_modalidad_traslado "02"; "Público"/"Publico" => "01".
- Unidad de peso "KGM" o "Kilogramos" => "KGM"; "TNE"/"TNM"/"Toneladas" => "TNE".
- Unidad de item "MILLAR"/"MILLARES" => "MIL"; "KGM" => "KGM"; "UNIDAD" => "NIU"; si no sabes, "NIU".
- Para conductor con nombre completo, separa nombre y apellido si es razonable; si no, pon todo en nombre y apellido null.

Formato JSON exacto:
{
  "codigo_motivo_traslado": "01|02|03|04|05|06|13|null",
  "descripcion_motivo_traslado": "string|null",
  "fecha_inicio_traslado": "YYYY-MM-DD|null",
  "peso_bruto_total": 0,
  "codigo_unidad_medida_peso_bruto": "KGM|TNE|LBR|null",
  "codigo_modalidad_traslado": "01|02|null",
  "gre_destinatario": {
    "codigo_tipo_documento_destinatario": "6|1|4|7|null",
    "numero_documento_destinatario": "string|null",
    "nombre_razon_social_destinatario": "string|null"
  },
  "gre_punto_partida": {
    "ubigeo_punto_partida": "string|null",
    "direccion_punto_partida": "string|null"
  },
  "gre_punto_llegada": {
    "ubigeo_punto_llegada": "string|null",
    "direccion_punto_llegada": "string|null"
  },
  "gre_vehiculos": [
    {"numero_placa": "string", "indicador_principal": true}
  ],
  "gre_conductores": [
    {
      "codigo_tipo_documento": "1",
      "numero_documento": "string|null",
      "nombre": "string|null",
      "apellido": "string|null",
      "numero_licencia": "string|null",
      "indicador_principal": true
    }
  ],
  "items": [
    {
      "descripcion": "string",
      "codigo_unidad_medida": "NIU|KGM|MIL|ZZ|MTR|LTR|TNE",
      "codigo_interno": "string",
      "cantidad": 0
    }
  ],
  "documento_relacionado": "string|null"
}

TEXTO DEL PDF:
{$text}
PROMPT;

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => $this->model,
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0,
                'max_tokens'  => 1800,
            ]);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Error desconocido de OpenAI');
            throw new \RuntimeException("OpenAI error: {$error}");
        }

        $content = trim((string) $response->json('choices.0.message.content', ''));
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', (string) $content);

        $data = json_decode((string) $content, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            throw new \RuntimeException('OpenAI no devolvió JSON válido: ' . $content);
        }

        return $this->normalize($data);
    }

    private function normalize(array $data): array
    {
        $data['items'] = array_values(array_filter(array_map(function ($item) {
            if (! is_array($item)) {
                return null;
            }

            $descripcion = trim((string) ($item['descripcion'] ?? ''));
            if ($descripcion === '') {
                return null;
            }

            return [
                'descripcion' => $descripcion,
                'codigo_unidad_medida' => $this->normalizeUnit($item['codigo_unidad_medida'] ?? 'NIU'),
                'codigo_interno' => trim((string) ($item['codigo_interno'] ?? 'GRE')),
                'cantidad' => round((float) ($item['cantidad'] ?? 1), 4),
            ];
        }, $data['items'] ?? [])));

        $data['gre_vehiculos'] = array_values(array_filter(array_map(function ($vehiculo) {
            if (! is_array($vehiculo)) {
                return null;
            }
            $placa = strtoupper(str_replace(['-', ' '], '', (string) ($vehiculo['numero_placa'] ?? '')));
            return $placa === '' ? null : [
                'numero_placa' => $placa,
                'indicador_principal' => (bool) ($vehiculo['indicador_principal'] ?? true),
            ];
        }, $data['gre_vehiculos'] ?? [])));

        $data['gre_conductores'] = array_values(array_filter(array_map(function ($conductor) {
            if (! is_array($conductor)) {
                return null;
            }
            $dni = trim((string) ($conductor['numero_documento'] ?? ''));
            $licencia = trim((string) ($conductor['numero_licencia'] ?? ''));
            $nombre = trim((string) ($conductor['nombre'] ?? ''));
            $apellido = trim((string) ($conductor['apellido'] ?? ''));

            if ($dni === '' && $licencia === '' && $nombre === '' && $apellido === '') {
                return null;
            }

            return [
                'codigo_tipo_documento' => (string) ($conductor['codigo_tipo_documento'] ?? '1'),
                'numero_documento' => $dni,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'numero_licencia' => $licencia,
                'indicador_principal' => (bool) ($conductor['indicador_principal'] ?? true),
            ];
        }, $data['gre_conductores'] ?? [])));

        $data['codigo_unidad_medida_peso_bruto'] = $this->normalizeWeightUnit($data['codigo_unidad_medida_peso_bruto'] ?? null);

        return $data;
    }

    private function normalizeUnit(mixed $unit): string
    {
        $value = strtoupper(trim((string) $unit));

        return match (true) {
            str_contains($value, 'MILLAR'), $value === 'MIL' => 'MIL',
            str_contains($value, 'KGM'), str_contains($value, 'KILO') => 'KGM',
            str_contains($value, 'TON'), $value === 'TNE', $value === 'TNM' => 'TNE',
            str_contains($value, 'SERV') => 'ZZ',
            str_contains($value, 'METRO') => 'MTR',
            str_contains($value, 'LIT') => 'LTR',
            default => $value !== '' ? $value : 'NIU',
        };
    }

    private function normalizeWeightUnit(mixed $unit): ?string
    {
        $value = strtoupper(trim((string) $unit));

        return match (true) {
            $value === '' => null,
            str_contains($value, 'KGM'), str_contains($value, 'KILO') => 'KGM',
            str_contains($value, 'TON'), $value === 'TNE', $value === 'TNM' => 'TNE',
            str_contains($value, 'LIB'), $value === 'LBR' => 'LBR',
            default => $value,
        };
    }
}
