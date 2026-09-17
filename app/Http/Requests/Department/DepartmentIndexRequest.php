<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\PaginatedIndexRequest;

/**
 * `GET /departments`: sólo acepta los parámetros de paginación `limit` y `page`.
 */
class DepartmentIndexRequest extends PaginatedIndexRequest
{
    //
}
