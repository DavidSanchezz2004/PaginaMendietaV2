<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\CompanySpotDetraccionPreset;
use App\Models\SpotDetraccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpotDetraccionPresetController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'codigo_bbss_sujeto_detraccion' => ['required', 'string', 'max:5'],
            'porcentaje_detraccion' => ['required', 'numeric', 'min:0', 'max:100'],
            'cuenta_banco_detraccion' => ['nullable', 'string', 'max:20'],
            'codigo_medio_pago_detraccion' => ['nullable', 'string', 'max:5'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $companyId = (int) session('company_id');
        $codigo = $data['codigo_bbss_sujeto_detraccion'];
        $spot = SpotDetraccion::where('codigo', $codigo)->first();

        $cuenta = preg_replace('/[^0-9]/', '', (string) ($data['cuenta_banco_detraccion'] ?? ''));
        if ($cuenta !== '' && strlen($cuenta) !== 11) {
            return response()->json([
                'message' => 'La cuenta del Banco de la Nación debe tener exactamente 11 dígitos.',
            ], 422);
        }

        $isDefault = (bool) ($data['is_default'] ?? false);
        if ($isDefault) {
            CompanySpotDetraccionPreset::where('company_id', $companyId)->update(['is_default' => false]);
        }

        $preset = CompanySpotDetraccionPreset::updateOrCreate(
            [
                'company_id' => $companyId,
                'name' => trim($data['name']),
            ],
            [
                'spot_detraccion_id' => $spot?->id,
                'codigo_bbss_sujeto_detraccion' => $codigo,
                'porcentaje_detraccion' => round((float) $data['porcentaje_detraccion'], 2),
                'cuenta_banco_detraccion' => $cuenta !== '' ? $cuenta : null,
                'codigo_medio_pago_detraccion' => $data['codigo_medio_pago_detraccion'] ?? '001',
                'is_default' => $isDefault,
            ]
        );

        return response()->json([
            'message' => 'Preset de detracción guardado.',
            'preset' => $this->serializePreset($preset),
        ]);
    }

    /** @return array<string, mixed> */
    private function serializePreset(CompanySpotDetraccionPreset $preset): array
    {
        return [
            'id' => $preset->id,
            'name' => $preset->name,
            'codigo_bbss_sujeto_detraccion' => $preset->codigo_bbss_sujeto_detraccion,
            'porcentaje_detraccion' => (float) $preset->porcentaje_detraccion,
            'cuenta_banco_detraccion' => $preset->cuenta_banco_detraccion,
            'codigo_medio_pago_detraccion' => $preset->codigo_medio_pago_detraccion,
            'is_default' => (bool) $preset->is_default,
        ];
    }
}
