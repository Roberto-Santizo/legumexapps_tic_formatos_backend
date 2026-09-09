<?php

namespace App\Http\Requests;

use App\Models\DeliveryDocumentDetail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateReturnDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Cuerpo `multipart/form-data` de `POST /return_documents`. Los equipos que
     * se devuelven viajan en `items`, cada uno apuntando al detalle de la
     * entrega original: sin entrega no puede haber devolución.
     *
     * `return_date` y `user_id` los asigna el servidor.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'responsable_signature' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
            'administrador_signature' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
            'observations' => ['nullable'],
            'delivery_document_id' => ['required', 'exists:delivery_documents,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.delivery_document_detail_id' => [
                'required',
                'distinct',
                'exists:delivery_document_details,id',
            ],
            'items.*.observations' => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'responsable_signature.required' => 'La firma del responsable es requerida',
            'administrador_signature.required' => 'La firma del administrador es requerida',
            'delivery_document_id.required' => 'El documento de entrega es requerido',
            'delivery_document_id.exists' => 'El documento de entrega seleccionado no existe',
            'items.required' => 'Debe devolver al menos un equipo',
            'items.min' => 'Debe devolver al menos un equipo',
            'items.*.delivery_document_detail_id.distinct' => 'No se puede devolver el mismo equipo dos veces en el mismo documento',
            'items.*.delivery_document_detail_id.required' => 'Cada equipo devuelto debe indicar el detalle de la entrega',
            'items.*.delivery_document_detail_id.exists' => 'El detalle de entrega seleccionado no existe',
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validarDetallesDeLaEntrega($validator),
            fn (Validator $validator) => $this->validarDetallesNoDevueltos($validator),
            fn (Validator $validator) => $this->validarFirmasDistintas($validator),
        ];
    }

    /**
     * RN-06: las dos firmas no pueden ser el mismo archivo.
     */
    private function validarFirmasDistintas(Validator $validator): void
    {
        $responsable = $this->file('responsable_signature');
        $administrador = $this->file('administrador_signature');

        if (! $responsable || ! $administrador) {
            return;
        }

        if (hash_file('sha256', $responsable->getRealPath()) === hash_file('sha256', $administrador->getRealPath())) {
            $validator->errors()->add(
                'administrador_signature',
                'La firma del administrador no puede ser la misma que la del responsable'
            );
        }
    }

    /**
     * RN-08: los detalles deben ser del documento de entrega enviado.
     */
    private function validarDetallesDeLaEntrega(Validator $validator): void
    {
        $items = $this->input('items', []);
        $deliveryDocumentId = $this->input('delivery_document_id');

        if (! $deliveryDocumentId || $items === []) {
            return;
        }

        $detallesValidos = DeliveryDocumentDetail::query()
            ->where('delivery_document_id', $deliveryDocumentId)
            ->pluck('id')
            ->all();

        foreach ($items as $index => $item) {
            if (! in_array($item['delivery_document_detail_id'] ?? null, $detallesValidos)) {
                $validator->errors()->add(
                    "items.{$index}.delivery_document_detail_id",
                    'El equipo no pertenece al documento de entrega indicado'
                );
            }
        }
    }

    /**
     * RN-10: el detalle no puede tener ya una devolución registrada.
     */
    private function validarDetallesNoDevueltos(Validator $validator): void
    {
        $items = $this->input('items', []);

        $yaDevueltos = DeliveryDocumentDetail::query()
            ->whereIn('id', collect($items)->pluck('delivery_document_detail_id')->filter())
            ->whereHas('returnDetail')
            ->pluck('id')
            ->all();

        foreach ($items as $index => $item) {
            if (in_array($item['delivery_document_detail_id'] ?? null, $yaDevueltos)) {
                $validator->errors()->add(
                    "items.{$index}.delivery_document_detail_id",
                    'El equipo ya fue devuelto en otro documento'
                );
            }
        }
    }
}
