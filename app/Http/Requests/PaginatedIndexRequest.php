<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base de los FormRequest de listado. Añade a los filtros propios de cada
 * recurso los parámetros de paginación `limit` y `page`: si llega `limit` el
 * listado se pagina con ese tamaño; si no, se devuelven todos los registros.
 */
abstract class PaginatedIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Filtros propios del recurso; se combinan con los de paginación.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return [];
    }

    /**
     * Mensajes de los filtros propios del recurso.
     *
     * @return array<string, string>
     */
    protected function filterMessages(): array
    {
        return [];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->filterRules(),
            'limit' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->filterMessages(),
            'limit.integer' => 'El límite debe ser un valor numérico',
            'limit.min' => 'El límite debe ser mayor a cero',
            'page.integer' => 'La página debe ser un valor numérico',
            'page.min' => 'La página debe ser mayor a cero',
        ];
    }

    /**
     * Tamaño de página solicitado, o `null` para devolver todos los registros.
     */
    public function limit(): ?int
    {
        $limit = $this->validated('limit');

        return $limit === null || $limit === '' ? null : (int) $limit;
    }
}
