<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Guarda la configuración de informacion_adicional para una empresa.
 * Estos valores se envían automáticamente en el JSON a Feasy/SUNAT
 * al emitir una Factura (01), Boleta (03) o comprobante con SPOT.
 *
 * Ruta: PUT /facturador/configuracion/informacion-adicional
 */
class InformacionAdicionalConfigController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // La empresa activa proviene de la sesión (misma clave que EnsureFacturadorEnabled)
        $companyId = session('company_id');
        $company   = Company::findOrFail($companyId);

        $validated = $request->validate([
            'informacion_adicional' => ['nullable', 'array', 'max:10'],
            'informacion_adicional.*' => ['nullable', 'string', 'max:500'],
        ]);

        // Construir el JSON con claves informacion_adicional_1..10
        $values = [];
        foreach (array_values($validated['informacion_adicional'] ?? []) as $i => $v) {
            if ($v !== null && trim((string) $v) !== '') {
                $values['informacion_adicional_' . ($i + 1)] = trim((string) $v);
            }
        }

        $company->update([
            'informacion_adicional_config' => empty($values) ? null : $values,
        ]);

        return back()->with('success', 'Información adicional guardada correctamente.');
    }
}
