<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'equipment_id'])]
class Caracteristic extends Model
{
    public function equipment(){
        return $this->belongsTo(Equipment::class);
    }
}
