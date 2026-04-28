<?php

namespace App\Http\Requests\Sunat;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

class StoreSunatApiCredentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        $companyId = (int) $this->input('empresa_id');

        if ($companyId !== (int) session('company_id')) {
            return false;
        }

        $company = Company::find($companyId);

        return $company !== null && $this->user()?->can('updateSunatCredentials', $company);
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['required', 'integer', 'exists:companies,id'],
            'ruc_consultante' => ['required', 'regex:/^[0-9]{11}$/'],
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
