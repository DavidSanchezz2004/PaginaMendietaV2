<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
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
        $companyId = (int) $this->route('company')?->id;

        return [
            'ruc' => [
                'required',
                'string',
                'size:11',
                'regex:/^[0-9]{11}$/',
                Rule::unique('companies', 'ruc')->ignore($companyId),
            ],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'facturador_enabled' => ['nullable', 'boolean'],
        ];
    }
}
