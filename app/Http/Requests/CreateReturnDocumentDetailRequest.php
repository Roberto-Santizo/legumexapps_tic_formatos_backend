<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateReturnDocumentDetailRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'observations' => ['nullable'],
                'delivery_document_detail_id' => ['required', 'exists:delivery_document_details,id'],  
                'return_document_id' => ['required', 'exists:return_documents,id'],  
        ];
    }
}
