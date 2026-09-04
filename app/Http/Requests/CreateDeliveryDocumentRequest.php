<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateDeliveryDocumentRequest extends FormRequest
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
                'location' => 'required',
                'responsable_signature' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
                'administrador_signature' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
                'employee_id' => ['required', 'exists:employees,id'],
                'observations' => ['nullable'],
            ];
    }

    public function messages(){
        return [
            'location.required' => 'La planta es requerida'
        ];
    }
}
