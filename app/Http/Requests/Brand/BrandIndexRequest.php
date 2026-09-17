<?php

namespace App\Http\Requests\Brand;

use App\Http\Requests\PaginatedIndexRequest;

/**
 * `GET /brands`: sólo acepta los parámetros de paginación `limit` y `page`.
 */
class BrandIndexRequest extends PaginatedIndexRequest
{
    //
}
