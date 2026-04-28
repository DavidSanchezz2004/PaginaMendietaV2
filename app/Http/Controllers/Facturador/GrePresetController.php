<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\CompanyGrePreset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrePresetController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'partida_ubigeo' => ['nullable', 'string', 'max:10'],
            'partida_direccion' => ['nullable', 'string', 'max:300'],
            'llegada_ubigeo' => ['nullable', 'string', 'max:10'],
            'modalidad' => ['required', 'in:01,02'],
            'unidad_peso' => ['required', 'string', 'max:5'],
            'placa' => ['nullable', 'string', 'max:10'],
            'conductor_dni' => ['nullable', 'string', 'max:20'],
            'conductor_nombre' => ['nullable', 'string', 'max:100'],
            'conductor_apellido' => ['nullable', 'string', 'max:100'],
            'conductor_licencia' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $companyId = (int) session('company_id');
        $isDefault = (bool) ($data['is_default'] ?? false);

        if ($isDefault) {
            CompanyGrePreset::where('company_id', $companyId)->update(['is_default' => false]);
        }

        $preset = CompanyGrePreset::updateOrCreate(
            ['company_id' => $companyId, 'name' => trim($data['name'])],
            [
                'partida_ubigeo' => $data['partida_ubigeo'] ?? null,
                'partida_direccion' => $data['partida_direccion'] ?? null,
                'llegada_ubigeo' => $data['llegada_ubigeo'] ?? null,
                'modalidad' => $data['modalidad'],
                'unidad_peso' => $data['unidad_peso'],
                'placa' => strtoupper(str_replace(['-', ' '], '', (string) ($data['placa'] ?? ''))) ?: null,
                'conductor_dni' => $data['conductor_dni'] ?? null,
                'conductor_nombre' => $data['conductor_nombre'] ?? null,
                'conductor_apellido' => $data['conductor_apellido'] ?? null,
                'conductor_licencia' => $data['conductor_licencia'] ?? null,
                'is_default' => $isDefault,
            ]
        );

        return response()->json([
            'message' => 'Carga rápida GRE guardada.',
            'preset' => $preset,
        ]);
    }
}
