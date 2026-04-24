<?php

namespace App\Services\Facturador;

use App\Models\Provider;
use Illuminate\Support\Collection;

class ProviderService
{
    /**
     * Busca proveedores de la empresa activa por nombre o número de documento.
     */
    public function search(string $term): Collection
    {
        return Provider::forActiveCompany()
            ->where('activo', true)
            ->where(function ($q) use ($term): void {
                $q->where('nombre_razon_social', 'like', "%{$term}%")
                  ->orWhere('numero_documento', 'like', "%{$term}%");
            })
            ->orderBy('nombre_razon_social')
            ->limit(20)
            ->get(['id', 'tipo_documento', 'numero_documento', 'nombre_razon_social', 'nombre_comercial']);
    }

    /**
     * Busca o crea un proveedor a partir del RUC/doc del comprobante recibido.
     */
    public function findOrCreate(string $tipoDoc, string $numeroDoc, string $razonSocial): Provider
    {
        $companyId = session('company_id');

        return Provider::firstOrCreate(
            [
                'company_id'       => $companyId,
                'numero_documento' => $numeroDoc,
            ],
            [
                'tipo_documento'     => $tipoDoc,
                'nombre_razon_social' => $razonSocial,
            ]
        );
    }

    public function all(): Collection
    {
        return Provider::forActiveCompany()
            ->where('activo', true)
            ->orderBy('nombre_razon_social')
            ->get(['id', 'tipo_documento', 'numero_documento', 'nombre_razon_social']);
    }
}
