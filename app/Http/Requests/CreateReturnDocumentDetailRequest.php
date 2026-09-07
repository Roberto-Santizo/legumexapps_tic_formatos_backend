<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\DeliveryDocument;
use App\Models\DeliveryDocumentDetail;
use App\Models\ReturnDocument;
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
    private function validarDetalleNoDevuelto(Validator $validator): void
    {
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
    private function validarFechaDeDevolucion(Validator $validator): void
    {
        $delivery = DeliveryDocument::find($this->input('delivery_document_id'));

        if (! $delivery || ! $this->input('return_date')) {
            return;
        }

        if ($this->date('return_date')->lt($delivery->delivery_date)) {
            $validator->errors()->add(
                'return_date',
                'La fecha de devolución no puede ser anterior a la de entrega'
            );
        }
    }

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
