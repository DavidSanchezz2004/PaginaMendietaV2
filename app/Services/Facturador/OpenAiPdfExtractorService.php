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

        // Preservar saltos de línea para que la IA pueda leer tablas fila por fila.
        // Solo colapsar espacios múltiples dentro de cada línea, no los newlines.
        $lines = explode("\n", $raw);
        $cleaned = [];
        foreach ($lines as $line) {
            $line = preg_replace('/[^\x20-\x7EáéíóúÁÉÍÓÚñÑ°%$.,\-:\/]/u', ' ', $line);
            $line = preg_replace('/\s{2,}/', ' ', trim($line));
            if ($line !== '') {
                $cleaned[] = $line;
            }
        }

        return implode("\n", $cleaned);
    }

    // ── Llamada a OpenAI ────────────────────────────────────────────────────

    private function callOpenAi(string $cleanText): array
    {
        // Limitar a 6 000 caracteres (más margen con saltos de línea)
        $text = mb_substr($cleanText, 0, 6000);

        $prompt = <<<PROMPT
Eres un extractor de comprobantes SUNAT (Perú). Analiza el texto y devuelve SOLO JSON válido, sin markdown ni explicaciones.

══════════════════════════════════════════
REGLAS CRÍTICAS PARA NÚMEROS (LEE CON CUIDADO)
══════════════════════════════════════════
1. SEPARADOR DE MILES: la coma (,) es separador de miles en estos documentos.
   Ejemplos: "5,025.060" = 5025.06 | "10,150.62" = 10150.62 | "1,827.11" = 1827.11
   NUNCA interpretes "5,025.060" como 5.025 ni como 2.025.

2. COLUMNAS DE PRECIO EN ÍTEMS — hay dos columnas distintas:
   - "V.U." / "Unit Value" / "Valor Unitario" / "V. Unit." = valor SIN IGV → usar para valor_unitario.
   - "P.U." / "Unit Price" / "Precio Unitario" / "P. Unit." = valor CON IGV incluido → NO usar.

3. VERIFICACIÓN OBLIGATORIA por cada ítem:
   Tras extraer cantidad y valor_unitario, verifica: cantidad × valor_unitario ≈ importe_venta.
   Si la diferencia es mayor al 1%: recalcula cantidad = importe_venta / valor_unitario.
   Si aún no coincide: recalcula valor_unitario = importe_venta / cantidad.

══════════════════════════════════════════
CAMPOS COMPROBANTE (factura/boleta/NC/ND)
══════════════════════════════════════════
numero_doc_proveedor (RUC 11d del EMISOR),
razon_social_proveedor (razón social del EMISOR),
codigo_tipo_documento (01=factura, 03=boleta, 07=NC, 08=ND),
serie_documento, numero_documento (solo dígitos),
fecha_emision (YYYY-MM-DD), fecha_vencimiento (YYYY-MM-DD|null),
base_imponible_gravadas (Total Valor de Venta / Total Sale Value — SIN IGV, elimina comas de miles),
igv_gravadas (IGV / Taxes, elimina comas de miles),
monto_total (Importe Total / Total Value — CON IGV, elimina comas de miles),
codigo_moneda (PEN si S/ o SOLES | USD si DOLAR AMERICANO / AMERICAN DOLLAR),
items: [{
  descripcion,
  unidad_medida,
  cantidad       (número sin coma de miles — ej: 5025.06, NUNCA "5,025.06"),
  valor_unitario (columna V.U. / Unit Value, SIN IGV),
  descuento      (0 si no hay),
  importe_venta  (columna Valor Venta / Sale Value — número sin coma de miles),
  icbper         (0 si no aplica)
}],
contable_tipo_operacion (0401=compra interna, 0402=no domiciliado, 0409=DUA, 0412=NC, 0413=ND),
contable_tipo_compra (NG|NI|EX|GR|MX),
contable_cuenta_contable (601=mercaderías, 602=almacenes, 603=suministros, 632=servicios),
contable_codigo_producto_servicio (Cat.25 SUNAT),
contable_forma_pago (01=contado | 02=crédito — si dice "DÍAS", "LETRA" o "CRÉDITO" usar 02),
contable_glosa (ej: "Compra F002-38109 - OPP FILM S.A."),
es_sujeto_detraccion (boolean), monto_detraccion (float|null),
informacion_detraccion: {leyenda, bien_codigo, bien_descripcion, medio_pago, numero_cuenta, porcentaje}|null,
es_sujeto_retencion (boolean), retention_base (float|null), retention_percentage (float|null),
retention_amount (float|null), net_total (float|null),
retention_info: {tipo, concepto, referencia_sunat}|null

══════════════════════════════════════════
CAMPOS GRE (solo si el PDF es una Guía de Remisión)
══════════════════════════════════════════
gre_numero (ej: "EG07-00000103", "T001-12345"),
gre_fecha_inicio_traslado (YYYY-MM-DD),
gre_motivo_traslado (ej: "Venta", "Compra", "Devolución"),
gre_punto_partida, gre_punto_llegada,
gre_destinatario_ruc, gre_destinatario_razon_social,
gre_documento_relacionado (ej: "Factura E001-223"),
gre_bienes_descripcion, gre_cantidad_bienes, gre_unidad_medida,
gre_peso_bruto, gre_unidad_medida_peso ("KGM"|"TNM"|null),
gre_placa_vehiculo, gre_conductor_nombre, gre_conductor_dni, gre_conductor_licencia,
gre_privado_transporte (boolean), gre_retorno_vehiculo_vacio (boolean), gre_transbordo_programado (boolean)

══════════════════════════════════════════
REGLAS FINALES
══════════════════════════════════════════
- Dato no encontrado → null.
- Si el PDF es GRE: llenar campos gre_*. Si NO es GRE: todos gre_* = null.
- Si detectas SPOT/detracción → es_sujeto_detraccion=true + llenar informacion_detraccion.
- Si detectas "Información de la retención" → es_sujeto_retencion=true.
- Sin ítems → items:[].

TEXTO DEL COMPROBANTE:
{$text}
PROMPT;

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => $this->model,
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0,
                'max_tokens'  => 2000,
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

        // ── Post-procesado: corregir confusión de columnas en ítems ──────────
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$item) {
                $qty = floatval($item['cantidad']       ?? 0);
                $vu  = floatval($item['valor_unitario'] ?? 0);
                $imp = floatval($item['importe_venta']  ?? 0);

                if ($qty <= 0 || $vu <= 0 || $imp <= 0) {
                    continue;
                }

                $diff = abs(($qty * $vu) - $imp) / $imp;

                if ($diff > 0.005) {
                    // Probable confusión de columnas: IA asignó la cantidad al campo
                    // valor_unitario y viceversa.  Si el "valor_unitario" es
                    // sustancialmente mayor que la "cantidad" y la cantidad es pequeña
                    // (< 100), probablemente están invertidos.
                    if ($vu > $qty && $qty < 100) {
                        // Intercambiar: el número grande es la cantidad (ej. 5025 KGM)
                        $item['cantidad']       = $vu;
                        $item['valor_unitario'] = $imp > 0 ? round($imp / $vu, 6) : $vu;
                    } else {
                        // Solo recalcular valor_unitario a partir de importe / cantidad
                        $item['valor_unitario'] = round($imp / $qty, 6);
                    }
                }
            }
            unset($item);
        }

        return $data;
    }
}
