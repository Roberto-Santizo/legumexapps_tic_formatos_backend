<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name','brand_id', 'original',  'model', 'serie', 'registerdBy', 'type'])]
class Equipment extends Model
{
    protected $table = 'equipments';

    public function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'registerdBy', 'id');
    }
}
