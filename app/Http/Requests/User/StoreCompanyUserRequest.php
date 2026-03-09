<?php

namespace App\Http\Requests\User;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyUserRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'role' => ['required', 'string', Rule::in(array_map(fn (RoleEnum $role) => $role->value, RoleEnum::cases()))],
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
        ];
    }
}
