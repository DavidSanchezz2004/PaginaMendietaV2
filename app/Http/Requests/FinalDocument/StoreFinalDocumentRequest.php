<?php

namespace App\Http\Requests\FinalDocument;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinalDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:10240'], // Max 10MB
        ];
    }
}
