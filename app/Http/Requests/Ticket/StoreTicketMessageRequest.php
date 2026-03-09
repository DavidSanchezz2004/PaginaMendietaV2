<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketMessageRequest extends FormRequest
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
            'message' => ['required_without:attachment', 'nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120'], // Max 5MB
        ];
    }
}
