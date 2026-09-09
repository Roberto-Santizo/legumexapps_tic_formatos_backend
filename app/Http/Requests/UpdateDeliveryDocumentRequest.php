<?php

namespace App\Http\Requests;

use App\Enums\Plant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sólo se puede corregir el encabezado. Las firmas, la fecha de entrega, el
     * empleado y el usuario que registró quedan fijos una vez firmado el
     * documento.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location' => ['required', 'integer', Rule::enum(Plant::class)],
            'observations' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'location.enum' => 'La planta seleccionada no es válida',
            'location.integer' => 'La planta debe ser un valor numérico',
        ];
    }
}
