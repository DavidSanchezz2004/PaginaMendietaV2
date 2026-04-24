<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

/**
 * Trait aplicado en TODO modelo con company_id del módulo Facturador.
 * Garantiza que las queries estén siempre scoped a la empresa activa.
 *
 * REGLA NO NEGOCIABLE: ninguna query del facturador puede correr
 * sin company_id == session('company_id'). Anti-IDOR.
 */
trait BelongsToActiveCompany
{
    /**
     * Scope local: filtra automáticamente por la empresa activa en sesión.
     *
     * Uso:   Product::forActiveCompany()->get()
     *        Invoice::forActiveCompany()->where('estado', 'sent')->get()
     */
    public function scopeForActiveCompany(Builder $query): Builder
    {
        $companyId = Session::get('company_id');

        if (! $companyId) {
            // Si no hay empresa activa, no devolver NADA (Zero Trust)
            $query->whereRaw('1 = 0');
            return $query;
        }

        return $query->where($this->getTable() . '.company_id', $companyId);
    }

    /**
     * Verifica en runtime que el modelo pertenece a la empresa activa.
     * Usar en Policies y Services antes de operar.
     */
    public function belongsToActiveCompany(): bool
    {
        return (int) $this->company_id === (int) Session::get('company_id');
    }
}
