<?php

namespace App\Http\Requests\Sunat;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarComprobanteSunatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $companyId = (int) $this->input('empresa_id');

        if ($companyId !== (int) session('company_id')) {
            return false;
        }

        $company = Company::find($companyId);

        return $company !== null && $this->user()?->can('view', $company);
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['required', 'integer', 'exists:companies,id'],
            'numRuc' => ['required', 'regex:/^[0-9]{11}$/'],
            'codComp' => ['required', Rule::in(['01', '03', '04', '07', '08', 'R1', 'R7'])],
            'numeroSerie' => ['required', 'string', 'max:4'],
            'numero' => ['required', 'integer', 'min:1', 'max:99999999'],
            'fechaEmision' => ['required', 'date_format:d/m/Y'],
            'monto' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'numRuc.regex' => 'El RUC emisor debe tener 11 dígitos.',
            'codComp.in' => 'Selecciona un tipo de comprobante SUNAT válido.',
            'numeroSerie.max' => 'La serie debe tener máximo 4 caracteres.',
            'fechaEmision.date_format' => 'La fecha debe tener formato dd/mm/aaaa.',
        ];
    }
}
