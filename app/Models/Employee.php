<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code','name','department_id'])]
class Employee extends Model
{
    public function department(){
        return $this->belongsTo(Department::class);
    }
}
