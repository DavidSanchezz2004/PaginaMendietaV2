<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function create(): View
    {
        return view('facturador.quotations.create');
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

    public function preview(Request $request): View
    {
        $validated = $request->validate([
            'cot_number'         => 'required|string|max:50',
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
        ]);
    }
}
