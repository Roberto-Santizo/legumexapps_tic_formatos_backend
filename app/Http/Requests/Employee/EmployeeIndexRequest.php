<?php

namespace App\Http\Requests\Employee;

use App\Http\Requests\PaginatedIndexRequest;

/**
 * `GET /employees`: sólo acepta los parámetros de paginación `limit` y `page`.
 */
class EmployeeIndexRequest extends PaginatedIndexRequest
{
    //
}
