<?php

namespace App\Http\Requests\Facturador;

use App\Models\CompanyUser;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida que la empresa seleccionada exista y que el usuario
 * tenga membresía activa en ella con rol admin|client.
 */
class SetActiveCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ];
    }

    /**
     * Validación extra: el usuario debe pertenecer a la empresa en el pivot.
     * Los admin globales pueden seleccionar cualquier empresa.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($v) {
            $user      = $this->user();
            $companyId = $this->input('company_id');

            if (! $companyId) {
                return;
            }

            $globalRole = $user->role instanceof \App\Enums\RoleEnum
                ? $user->role->value
                : (string) $user->role;

            // Admin global puede seleccionar cualquier empresa
            if ($globalRole === 'admin') {
                return;
            }

            $hasMembership = CompanyUser::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->whereIn('role', ['admin', 'client'])
                ->exists();

            if (! $hasMembership) {
                $v->errors()->add(
                    'company_id',
                    'No tienes acceso al Facturador para la empresa seleccionada.'
                );
            }
        });
    }
}
