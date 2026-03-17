<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditDebitNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'credit_debit_notes';

    protected $fillable = [
        'company_id',
        'user_id',
        'invoice_id',
        'codigo_tipo_documento',
        'codigo_tipo_nota',
        'serie_documento',
        'numero_documento',
        'codigo_interno',
        'fecha_emision',
        'hora_emision',
        'observacion',
        'correo',
        'monto_total_gravado',
        'monto_total_inafecto',
        'monto_total_exonerado',
        'monto_total_igv',
        'monto_total',
        'porcentaje_igv',
        'lista_items',
        'informacion_documento_referencia',
        'estado',
        'codigo_respuesta_feasy',
        'mensaje_respuesta_feasy',
        'url_pdf_feasy',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'datetime',
            'lista_items' => 'array',
            'informacion_documento_referencia' => 'array',
            'monto_total_gravado' => 'float',
            'monto_total_igv' => 'float',
            'monto_total' => 'float',
            'porcentaje_igv' => 'float',
        ];
    }

    // ── Relaciones ────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeForActiveCompany($query)
    {
        return $query->where('company_id', session('company_id'));
    }

    public function scopeCreditos($query)
    {
        return $query->where('codigo_tipo_documento', '07');
    }

    public function scopeDebitos($query)
    {
        return $query->where('codigo_tipo_documento', '08');
    }

    public function scopeSent($query)
    {
        return $query->where('estado', 'sent');
    }

    public function scopeError($query)
    {
        return $query->where('estado', 'error');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isCreditNote(): bool
    {
        return $this->codigo_tipo_documento === '07';
    }

    public function isDebitNote(): bool
    {
        return $this->codigo_tipo_documento === '08';
    }

    public function getTypeLabel(): string
    {
        return $this->isCreditNote() ? 'Nota de Crédito' : 'Nota de Débito';
    }

    public function getNotaTypeLabel(): string
    {
        $map = [
            '01' => 'Descuento',
            '02' => 'Devolución',
            '03' => 'Bonificación',
            '04' => 'Otros conceptos',
        ];

        return $map[$this->codigo_tipo_nota] ?? "Tipo {$this->codigo_tipo_nota}";
    }

    public function getEstadoLabel(): string
    {
        return match ($this->estado) {
            'draft' => 'Borrador',
            'sent' => 'Enviada',
            'error' => 'Error',
            'consulted' => 'Consultada',
            'voided' => 'Anulada',
            default => $this->estado,
        };
    }
}
