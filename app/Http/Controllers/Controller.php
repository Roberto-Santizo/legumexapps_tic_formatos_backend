<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginatedIndexRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

abstract class Controller
{
    /**
     * Resuelve un listado según el parámetro `limit` de la petición: si viene,
     * pagina la consulta con ese tamaño y la envuelve en `$paginatedResource`;
     * si no, devuelve todos los registros, transformados con `$itemResource`
     * cuando el recurso tiene uno.
     *
     * @param  class-string<JsonResource>  $paginatedResource
     * @param  class-string<JsonResource>|null  $itemResource
     */
    protected function paginateOrAll(Builder $query, PaginatedIndexRequest $request, string $paginatedResource, ?string $itemResource = null): JsonResource|Collection
    {
        $limit = $request->limit();

        if ($limit !== null) {
            return new $paginatedResource($query->paginate($limit));
        }

        $items = $query->get();

        return $itemResource ? $itemResource::collection($items) : $items;
    }
}
