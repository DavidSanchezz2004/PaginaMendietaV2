<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Client;
use App\Services\Facturador\QuoteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function __construct(
        private readonly QuoteService $quoteService,
    ) {
    }

    public function create(): View
    {
        [$company, $settings] = $this->settingsForActiveCompany();
        $this->ensureQuoteEnabled($settings);

        return view('facturador.quotations.create', [
            'company' => $company,
            'settings' => $settings,
        ]);
    }

    public function serviceProposal(string $regimen = 'rer'): View
    {
        $paquetes = [
            'rer' => [
                'titulo'      => 'Propuesta de Servicio Contable – Régimen Especial (RER)',
                'regimen'     => 'RER',
                'descripcion' => 'Nuestro paquete mensual para el Régimen Especial de Renta le ofrece un servicio integral que cubre todas sus obligaciones contables y tributarias ante SUNAT, permitiéndole concentrarse en su negocio con total tranquilidad.',
                'servicios'   => [
                    [
                        'titulo' => 'Registro Contable Mensual',
                        'items'  => [
                            'Registro de compras y ventas',
                            'Control de comprobantes de pago',
                            'Organización de documentación contable',
                        ],
                    ],
                    [
                        'titulo' => 'Declaraciones Tributarias',
                        'items'  => [
                            'Elaboración y presentación de impuestos mensuales (IGV – Renta RER)',
                            'Determinación de tributos a pagar',
                            'Presentación PDT 621',
                        ],
                    ],
                    [
                        'titulo' => 'Libros Contables Electrónicos',
                        'items'  => [
                            'Registro de Compras (PLE SUNAT)',
                            'Registro de Ventas (PLE SUNAT)',
                        ],
                    ],
                    [
                        'titulo' => 'Planilla Electrónica (PLAME)',
                        'items'  => [
                            'Elaboración y presentación mensual del PLAME',
                            'Cálculo de Essalud, AFP/ONP y retenciones',
                            'Registro de trabajadores (T-Registro)',
                        ],
                    ],
                    [
                        'titulo' => 'Asesoría Contable y Tributaria',
                        'items'  => [
                            'Orientación permanente sobre obligaciones SUNAT',
                            'Absolución de consultas contables y tributarias',
                        ],
                    ],
                    [
                        'titulo' => 'Reportes Básicos Mensuales',
                        'items'  => [
                            'Resumen mensual de impuestos',
                            'Control de ingresos y gastos',
                        ],
                    ],
                    [
                        'titulo' => 'Acceso al Facturador Electrónico',
                        'items'  => [
                            'Acceso a la plataforma MS Contables incluido en el servicio',
                            'Emisión de facturas, boletas y notas de crédito/débito',
                            'Envío automático a SUNAT (OSE/SOL)',
                            'Soporte y capacitación en el uso del sistema',
                        ],
                    ],
                ],
            ],
            'rus' => [
                'titulo'      => 'Propuesta de Servicio Contable – Nuevo RUS',
                'regimen'     => 'NRUS',
                'descripcion' => 'Paquete mensual para personas naturales o pequeñas empresas bajo el Nuevo Régimen Único Simplificado (NRUS). Cobertura completa de sus obligaciones tributarias.',
                'servicios'   => [
                    [
                        'titulo' => 'Cuota Mensual NRUS',
                        'items'  => [
                            'Determinación y pago de cuota mensual',
                            'Control de categoría según ingresos',
                        ],
                    ],
                    [
                        'titulo' => 'Control de Límites',
                        'items'  => [
                            'Monitoreo de límites de ingresos y compras',
                            'Alerta ante cambio de categoría',
                        ],
                    ],
                    [
                        'titulo' => 'Asesoría Tributaria',
                        'items'  => [
                            'Orientación permanente sobre obligaciones SUNAT',
                            'Absolución de consultas',
                        ],
                    ],
                    [
                        'titulo' => 'Reportes Mensuales',
                        'items'  => [
                            'Resumen de cumplimiento tributario',
                            'Control de ingresos y compras',
                        ],
                    ],
                    [
                        'titulo' => 'Acceso al Facturador Electrónico',
                        'items'  => [
                            'Acceso a la plataforma MS Contables incluido en el servicio',
                            'Emisión de facturas y boletas electrónicas',
                            'Envío automático a SUNAT (OSE/SOL)',
                        ],
                    ],
                ],
            ],
            'mype' => [
                'titulo'      => 'Propuesta de Servicio Contable – MYPE Régimen General',
                'regimen'     => 'MYPE',
                'descripcion' => 'Servicio contable completo para empresas bajo el Régimen MYPE Tributario o Régimen General. Gestión integral de todas las obligaciones formales y tributarias.',
                'servicios'   => [
                    [
                        'titulo' => 'Registro Contable Mensual',
                        'items'  => [
                            'Registro de compras, ventas y operaciones',
                            'Control de comprobantes de pago',
                        ],
                    ],
                    [
                        'titulo' => 'Declaraciones Tributarias',
                        'items'  => [
                            'PDT 621 mensual (IGV/Renta)',
                            'Declaración anual del Impuesto a la Renta',
                        ],
                    ],
                    [
                        'titulo' => 'Libros Electrónicos (PLE)',
                        'items'  => [
                            'Registro de Compras y Ventas',
                            'Libro Diario y Mayor simplificado',
                        ],
                    ],
                    [
                        'titulo' => 'Planilla y Recursos Humanos',
                        'items'  => [
                            'Planilla electrónica PLAME mensual',
                            'Cálculo de beneficios sociales',
                            'T-Registro de trabajadores',
                        ],
                    ],
                    [
                        'titulo' => 'Estados Financieros',
                        'items'  => [
                            'Balance general mensual',
                            'Estado de resultados',
                        ],
                    ],
                    [
                        'titulo' => 'Asesoría y Reportes',
                        'items'  => [
                            'Asesoría contable y tributaria permanente',
                            'Reportes de ingresos, gastos e impuestos',
                        ],
                    ],
                    [
                        'titulo' => 'Acceso al Facturador Electrónico',
                        'items'  => [
                            'Acceso a la plataforma MS Contables incluido en el servicio',
                            'Emisión de facturas, boletas y notas de crédito/débito',
                            'Envío automático a SUNAT (OSE/SOL)',
                            'Soporte y capacitación en el uso del sistema',
                        ],
                    ],
                ],
            ],
        ];

        $data = $paquetes[$regimen] ?? $paquetes['rer'];

        return view('facturador.quotations.service-proposal', $data + ['regimen' => $data['regimen']]);
    }

    public function preview(Request $request): View|JsonResponse
    {
        [$company, $settings] = $this->settingsForActiveCompany();
        $this->ensureQuoteEnabled($settings);

        $validated = $request->validate([
            'cot_number'         => 'required|string|max:30',
            'fecha_emision'      => 'required|date',
            'fecha_vencimiento'  => 'required|date',
            'cliente_tipo_doc'   => 'required|in:1,6',
            'cliente_numero_doc' => 'required|string|max:20',
            'cliente_nombre'     => 'required|string|max:200',
            'descripcion'        => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.servicio'   => 'required|string|max:200',
            'items.*.cantidad'   => 'required|numeric|min:0',
            'items.*.precio'     => 'required|numeric|min:0',
            'aplica_igv'         => 'nullable|boolean',
        ]);

        $items = collect($validated['items'])->map(function ($item) {
            return [
                'servicio'  => $item['servicio'],
                'cantidad'  => (float) $item['cantidad'],
                'precio'    => (float) $item['precio'],
                'total'     => (float) $item['cantidad'] * (float) $item['precio'],
            ];
        });

        $subtotal  = $items->sum('total');
        $aplicaIgv = (bool) ($validated['aplica_igv'] ?? false);
        $igv       = $aplicaIgv ? round($subtotal * 0.18, 2) : 0;
        $total     = $subtotal + $igv;

        $quote = DB::transaction(function () use ($company, $validated, $items, $aplicaIgv): \App\Models\Quote {
            $client = Client::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'numero_documento' => $validated['cliente_numero_doc'],
                ],
                [
                    'codigo_tipo_documento' => $validated['cliente_tipo_doc'],
                    'nombre_razon_social' => strtoupper($validated['cliente_nombre']),
                    'codigo_pais' => 'PE',
                    'activo' => true,
                ]
            );

            $client->fill([
                'codigo_tipo_documento' => $validated['cliente_tipo_doc'],
                'nombre_razon_social' => strtoupper($validated['cliente_nombre']),
                'activo' => true,
            ])->save();

            $quoteItems = $items->map(function (array $item) use ($aplicaIgv): array {
                $valorTotal = round((float) $item['total'], 2);
                $montoIgv = $aplicaIgv ? round($valorTotal * 0.18, 2) : 0;

                return [
                    'tipo' => 'S',
                    'descripcion' => $item['servicio'],
                    'codigo_unidad_medida' => 'UND',
                    'cantidad' => $item['cantidad'],
                    'monto_valor_unitario' => $item['precio'],
                    'monto_precio_unitario' => $aplicaIgv ? round($item['precio'] * 1.18, 6) : $item['precio'],
                    'monto_valor_total' => $valorTotal,
                    'codigo_indicador_afecto' => $aplicaIgv ? '10' : '20',
                    'monto_igv' => $montoIgv,
                    'monto_total' => round($valorTotal + $montoIgv, 2),
                ];
            })->all();

            $quote = $this->quoteService->create(
                $company->id,
                (int) auth()->id(),
                $client->id,
                [
                    'fecha_emision' => $validated['fecha_emision'],
                    'fecha_vencimiento' => $validated['fecha_vencimiento'],
                    'observacion' => $validated['descripcion'] ?? null,
                    'codigo_moneda' => 'PEN',
                    'porcentaje_igv' => $aplicaIgv ? 18 : 0,
                    'estado' => 'draft',
                ],
                $quoteItems
            );

            $quote->codigo_interno = $validated['cot_number'];
            $quote->save();

            return $quote->fresh('items', 'client');
        });

        if ($request->expectsJson()) {
            $pdfUrl = route('facturador.cotizaciones.pdf', $quote);
            $clientName = $quote->client?->nombre_cliente ?: strtoupper($validated['cliente_nombre']);
            $validUntil = \Carbon\Carbon::parse($validated['fecha_vencimiento'])->format('d/m/Y');
            $formattedTotal = 'PEN ' . number_format($total, 2);
            $issuerName = $settings->company_name ?: $company->name;
            $subject = "Cotización {$quote->codigo_interno} - {$issuerName}";
            $emailBody = "Hola,\n\n📄 Te enviamos la cotización {$quote->codigo_interno} para tu revisión.\n\n🗓️ Vigencia: {$validUntil}\n\nAdjuntamos el PDF con el detalle completo.\n\nQuedamos atentos a tus comentarios. Gracias.";
            $whatsappMessage = "Hola 👋 Te compartimos la cotización {$quote->codigo_interno}. 📄\n\n🗓️ Vigencia: {$validUntil}\n\nTe enviamos el PDF con el detalle para tu revisión. Quedamos atentos.";

            return response()->json([
                'ok' => true,
                'quote_id' => $quote->id,
                'quote_number' => $quote->codigo_interno,
                'client_name' => $clientName,
                'total' => $formattedTotal,
                'valid_until' => $validUntil,
                'pdf_url' => $pdfUrl,
                'show_url' => route('facturador.cotizaciones.show', $quote),
                'email_subject' => $subject,
                'email_body' => $emailBody,
                'whatsapp_message' => $whatsappMessage,
            ]);
        }

        return view('facturador.quotations.preview', [
            'cotNumber'         => $validated['cot_number'],
            'fechaEmision'      => $validated['fecha_emision'],
            'fechaVencimiento'  => $validated['fecha_vencimiento'],
            'clienteTipoDoc'    => $validated['cliente_tipo_doc'],
            'clienteNumeroDoc'  => $validated['cliente_numero_doc'],
            'clienteNombre'     => strtoupper($validated['cliente_nombre']),
            'descripcion'       => $validated['descripcion'] ?? '',
            'items'             => $items,
            'subtotal'          => $subtotal,
            'igv'               => $igv,
            'total'             => $total,
            'aplicaIgv'         => $aplicaIgv,
            'company'           => $company,
            'settings'          => $settings,
            'quote'             => $quote,
        ]);
    }

    private function settingsForActiveCompany(): array
    {
        $company = Company::findOrFail((int) session('company_id'));

        $settings = CompanySetting::firstOrCreate(
            ['company_id' => $company->id],
            [
                'quote_enabled' => true,
                'primary_color' => '#013b33',
                'secondary_color' => '#eef7f5',
                'company_name' => $company->name,
                'ruc' => $company->ruc,
            ]
        );

        return [$company, $settings];
    }

    private function ensureQuoteEnabled(CompanySetting $settings): void
    {
        $role = auth()->user()?->role?->value ?? (string) auth()->user()?->role;

        if ($role !== 'admin' && $settings->quote_enabled === false) {
            abort(403, 'El cotizador no está habilitado para esta empresa.');
        }
    }
}
