<?php

namespace App\Http\Requests\Equipment;

use App\Http\Requests\PaginatedIndexRequest;

/**
 * `GET /equipments`: sólo acepta los parámetros de paginación `limit` y `page`.
 */
class EquipmentIndexRequest extends PaginatedIndexRequest
{
    //
}
