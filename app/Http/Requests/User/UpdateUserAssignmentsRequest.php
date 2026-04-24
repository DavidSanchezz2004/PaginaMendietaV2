<?php

namespace App\Http\Requests\User;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserAssignmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roleValues = array_map(fn (RoleEnum $role) => $role->value, RoleEnum::cases());

        return [
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', 'distinct', Rule::exists('companies', 'id')],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', 'string', Rule::in($roleValues)],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
