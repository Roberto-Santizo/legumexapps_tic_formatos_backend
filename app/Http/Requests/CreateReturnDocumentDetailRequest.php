<?php

namespace App\Http\Requests;

use App\Models\DeliveryDocumentDetail;
use App\Models\ReturnDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'delivery_document_detail_id.required' => 'El equipo devuelto debe indicar el detalle de la entrega',
            'delivery_document_detail_id.exists' => 'El detalle de entrega seleccionado no existe',
            'return_document_id.required' => 'El documento de devolución es requerido',
            'return_document_id.exists' => 'El documento de devolución seleccionado no existe',
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validarDetalleNoDevuelto($validator),
            fn (Validator $validator) => $this->validarDetalleDeLaMismaEntrega($validator),
        ];
    }

    /**
     * RN-10: el detalle no puede tener ya una devolución registrada.
     */
    private function validarDetalleNoDevuelto(Validator $validator): void
    {
        if (! $this->input('delivery_document_detail_id')) {
            return;
        }

        $yaDevuelto = DeliveryDocumentDetail::query()
            ->where('id', $this->input('delivery_document_detail_id'))
            ->whereHas('returnDetail')
            ->exists();

        if ($yaDevuelto) {
            $validator->errors()->add(
                'delivery_document_detail_id',
                'El equipo ya fue devuelto en otro documento'
            );
        }
    }

    /**
     * RN-13: el detalle agregado debe pertenecer a la entrega de esta devolución.
     */
    private function validarDetalleDeLaMismaEntrega(Validator $validator): void
    {
        $returnDocument = ReturnDocument::find($this->input('return_document_id'));
        $detail = DeliveryDocumentDetail::find($this->input('delivery_document_detail_id'));

        if (! $returnDocument || ! $detail) {
            return;
        }

        if ($detail->delivery_document_id !== $returnDocument->delivery_document_id) {
            $validator->errors()->add(
                'delivery_document_detail_id',
                'El equipo no pertenece al documento de entrega de esta devolución'
            );
        }
    }
}
