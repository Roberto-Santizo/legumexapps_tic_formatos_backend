<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['location','delivery_date','responsable_signature','administrador_signature','employee_id','user_id','observations'])]
class DeliveryDocument extends Model
{
    protected $table = 'delivery_documents';

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'id');
    }

    public function details(){
        return $this->hasMany(DeliveryDocumentDetail::class);
    }
}
