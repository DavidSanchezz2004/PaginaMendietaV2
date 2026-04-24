<?php

namespace App\Services\Facturador;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;

/**
 * Extrae datos de comprobantes de VENTA (factura/boleta) desde PDF usando OpenAI.
 * 
 * Detecta automáticamente:
 * - Retenciones (si están presentes en el PDF)
 * - Moneda y tipo de cambio
 * - Estructura general del comprobante
 * 
 * Similar a OpenAiPdfExtractorService pero optimizado para ventas.
 */
class OpenAiSalesPdfExtractorService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
    }

    /**
     * Procesa un PDF de venta y retorna datos extraídos con detección de retención.
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
            throw new \RuntimeException(
                'No se pudo extraer texto del PDF. El archivo puede estar escaneado o protegido.'
            );
        }

        return $this->callOpenAi($text);
    }

    // ── Extracción de texto ─────────────────────────────────────────────────

    private function extractText(UploadedFile $pdf): string
    {
        $parser = new Parser();
        $document = $parser->parseFile($pdf->getRealPath());

        $raw = $document->getText();

        // Limpiar igual que OpenAiPdfExtractorService
        return preg_replace(
            ['/\s+/', '/[^\x20-\x7EáéíóúÁÉÍÓÚñÑ°%$.,\-:\/]/u'],
            [' ', ''],
            trim($raw)
        );
    }

    // ── Llamada a OpenAI ────────────────────────────────────────────────────

    private function callOpenAi(string $cleanText): array
    {
        // Limitar el texto a 3 000 caracteres
        $text = mb_substr($cleanText, 0, 3000);

        $prompt = <<<'PROMPT'
Extrae datos del comprobante de VENTA SUNAT. Responde SOLO JSON válido, sin markdown.

CAMPOS PRINCIPALES:
numero_doc_cliente(RUC 11d o DNI 8d), 
razon_social_cliente, 
codigo_tipo_documento(01=factura,03=boleta,07=NC,08=ND), 
serie_documento, 
numero_documento(solo número), 
fecha_emision(YYYY-MM-DD), 
fecha_vencimiento(YYYY-MM-DD|null), 
base_imponible_gravadas, 
igv_gravadas, 
monto_total, 
codigo_moneda(PEN|USD),

ITEMS:
items:[{descripcion,unidad_medida,cantidad,valor_unitario,descuento,importe_venta}],

DETECCIÓN CRÍTICA DE RETENCIONES:
Si el PDF contiene cualquiera de estas frases:
- "Información de la retención"
- "Base imponible de la retención"
- "Porcentaje de retención"
- "Monto de la retención"
- "Retención del" (ej: "Retención del 3%")
- "RET" junto a porcentaje
- "Agente de retención"

ENTONCES llenar:
retention_enabled: true
retention_base: float (base imponible para la retención)
retention_percentage: float (%, ej: 3.00)
retention_amount: float (monto retenido = base * (% / 100))
retention_leyenda: texto exacto donde aparece la retención

SI NO HAY RETENCIÓN:
retention_enabled: false
retention_base: null
retention_percentage: null
retention_amount: null
retention_leyenda: null

CAMPOS CONTABLES (sugerir si es posible):
contable_tipo_operacion(0101=venta interna,0112=NC,0113=ND),
contable_tipo_venta(IN=interna,EX=exportación,NC=nota crédito),
contable_cuenta_contable(ventas→701,servicios→702),
contable_codigo_producto_servicio(Cat.25 SUNAT),
contable_forma_pago(01=contado,02=crédito|null),

REGLAS:
- Si detectas RETENCIÓN → retention_enabled=true SIEMPRE
- NO confundir retención con detracción (SPOT)
- dato no encontrado → null
- S/=PEN, USD=USD
- Sugerir campos contables si es posible
- Sin ítems → items:[]

TEXTO:
{$text}
PROMPT;

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0,
                'max_tokens' => 1500,
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

        return $data ?? [];
    }
}
