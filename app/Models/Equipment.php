<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'brand_id', 'original',  'model', 'serie', 'registerdBy', 'type'])]
class Equipment extends Model
{
    protected $table = 'equipments';

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'registerdBy', 'id');
    }

    public function deliveryDetail()
    {
        return $this->hasMany(DeliveryDocumentDetail::class);
    }

    public function caracteristics()
    {
        return $this->hasMany(Caracteristic::class);
    }

    /**
     * Equipos sin entrega activa: no tienen ningún detalle de entrega
     * pendiente de devolución.
     *
     * @param  Builder<Equipment>  $query
     * @return Builder<Equipment>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereDoesntHave('deliveryDetail', function (Builder $detail) {
            $detail->whereDoesntHave('returnDetail');
        });
    }

    /**
     * Equipos con una entrega activa (entregados y aún no devueltos).
     *
     * @param  Builder<Equipment>  $query
     * @return Builder<Equipment>
     */
    public function scopeAssigned(Builder $query): Builder
    {
        return $query->whereHas('deliveryDetail', function (Builder $detail) {
            $detail->whereDoesntHave('returnDetail');
        });
    }
}
