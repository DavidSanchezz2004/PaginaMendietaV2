<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'period_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'format' => ['required', 'string', 'in:pdf,excel,powerbi'],
            'status' => ['nullable', 'string', 'in:draft,published'],
            
            'file' => ['required_if:format,pdf,excel', 'nullable', 'file', 'max:20480', 'mimes:pdf,xlsx,xls,csv'],
            'external_url' => ['required_if:format,powerbi', 'nullable', 'url', 'max:2000'],
        ];
    }
}
