<?php

namespace App\Services\Facturador;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

/**
 * Extrae datos contables de un PDF de comprobante usando:
 *  1. smalot/pdfparser → texto plano
 *  2. OpenAI Chat Completions → JSON estructurado
 */
class OpenAiPdfExtractorService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
        $this->model  = config('services.openai.model', 'gpt-4o-mini');
    }

    /**
     * Procesa un PDF subido y retorna el array de datos extraídos.
     *
     * @throws \RuntimeException si no hay API key o falla la extracción
     */
    public function extract(UploadedFile $pdf): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY no configurada en .env');
        }

        $text = $this->extractText($pdf);

        if (strlen(trim($text)) < 30) {
            throw new \RuntimeException('No se pudo extraer texto del PDF. El archivo puede estar escaneado o protegido.');
        }

        return $this->callOpenAi($text);
    }

    // ── Extracción de texto ─────────────────────────────────────────────────

    private function extractText(UploadedFile $pdf): string
    {
        $parser   = new Parser();
        $document = $parser->parseFile($pdf->getRealPath());

        $raw = $document->getText();

        // Limpiar igual que el nodo n8n "Limpiar Texto1"
        return preg_replace(
            ['/\s+/', '/[^\x20-\x7EáéíóúÁÉÍÓÚñÑ°%$.,\-:\/]/u'],
            [' ', ''],
            trim($raw)
        );
    }

    // ── Llamada a OpenAI ────────────────────────────────────────────────────

    private function callOpenAi(string $cleanText): array
    {
        // Limitar el texto a 3 000 caracteres — suficiente para un comprobante SUNAT
        $text = mb_substr($cleanText, 0, 3000);

        $prompt = <<<PROMPT
Extrae datos del comprobante SUNAT. Responde SOLO JSON válido, sin markdown.

CAMPOS:
numero_doc_proveedor(RUC 11d), razon_social_proveedor, codigo_tipo_documento(01=factura,03=boleta,07=NC,08=ND), serie_documento, numero_documento(solo número), fecha_emision(YYYY-MM-DD), fecha_vencimiento(YYYY-MM-DD|null), base_imponible_gravadas, igv_gravadas, monto_total, codigo_moneda(PEN|USD),
items:[{descripcion,unidad_medida,cantidad,valor_unitario,descuento,importe_venta,icbper}],
contable_tipo_operacion(0401=compra interna,0402=no domiciliado,0409=DUA,0412=NC,0413=ND),
contable_tipo_compra(NG|NI|EX|GR|MX),
contable_cuenta_contable(mercaderías→601,servicios→632,suministros→603,tributos→40),
contable_codigo_producto_servicio(Cat.25 SUNAT),
contable_forma_pago(01=contado,02=crédito|null),
contable_glosa(ej:"Compra F001-123 - EMPRESA SAC"),
es_sujeto_detraccion(boolean: true si menciona SPOT o detracción o pago de obligaciones tributarias, false por defecto),
monto_detraccion(float si es_sujeto_detraccion=true, null si no),
informacion_detraccion:{*si es_sujeto_detraccion=true:
  leyenda(texto completo de SPOT/detracción),
  bien_codigo(código SUNAT ej: "027"),
  bien_descripcion(ej:"Servicio de transporte de carga"),
  medio_pago(ej:"001" o "Depósito en cuenta"),
  numero_cuenta(del Banco de la Nación),
  porcentaje(%, ej:4.00),
  *si no hay detracción: null}

REGLAS: dato no encontrado→null. S/=PEN, USD=USD. Si detectas SPOT/detracción→es_sujeto_detraccion=true + llena informacion_detraccion. Siempre sugerir campos contables si es posible. Sin ítems→items:[].

TEXTO:
{$text}
PROMPT;

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => $this->model,
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0,
                'max_tokens'  => 1000,
            ]);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Error desconocido de OpenAI');
            throw new \RuntimeException("OpenAI error: {$error}");
        }

        $content = $response->json('choices.0.message.content', '');

        // Quitar bloque ```json ... ``` si OpenAI lo incluye por error
        $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
        $content = preg_replace('/\s*```$/', '', $content);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('OpenAI no devolvió JSON válido: ' . $content);
        }

        return $data;
    }
}
