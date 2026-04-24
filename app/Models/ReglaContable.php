<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Regla de asignación contable automática por empresa.
 * Se evalúan en orden ascendente de `prioridad`; aplica la primera que coincida.
 */
class ReglaContable extends Model
{
    use HasFactory, BelongsToActiveCompany;

    protected $table = 'reglas_contables';

    protected $fillable = [
        'company_id',
        'nombre',
        'prioridad',
        'proveedor_id',
        'ruc_proveedor',
        'keyword_glosa',
        'tipo_documento',
        'cuenta_gasto',
        'cuenta_igv',
        'tipo_compra',
        'tipo_operacion',
        'tipo_gasto',
        'codigo_producto_servicio',
        'centro_costo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo'    => 'boolean',
            'prioridad' => 'integer',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'proveedor_id');
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    /**
     * Devuelve true si esta regla hace match contra los datos de una compra.
     * Todos los criterios configurados deben cumplirse (AND).
     */
    public function matches(array $purchaseData): bool
    {
        if ($this->proveedor_id && (int) ($purchaseData['provider_id'] ?? 0) !== (int) $this->proveedor_id) {
            return false;
        }

        if ($this->ruc_proveedor && ($purchaseData['numero_doc_proveedor'] ?? '') !== $this->ruc_proveedor) {
            return false;
        }

        if ($this->tipo_documento && ($purchaseData['codigo_tipo_documento'] ?? '') !== $this->tipo_documento) {
            return false;
        }

        if ($this->keyword_glosa) {
            $glosa = strtolower($purchaseData['glosa'] ?? '');
            if (! str_contains($glosa, strtolower($this->keyword_glosa))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retorna array con los campos contables que esta regla asigna.
     */
    public function toAccountingFields(): array
    {
        return array_filter([
            'cuenta_contable'          => $this->cuenta_gasto,
            'cuenta_igv'               => $this->cuenta_igv,
            'tipo_compra'              => $this->tipo_compra,
            'tipo_operacion'           => $this->tipo_operacion,
            'tipo_gasto'               => $this->tipo_gasto,
            'codigo_producto_servicio' => $this->codigo_producto_servicio,
            'centro_costo'             => $this->centro_costo,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
