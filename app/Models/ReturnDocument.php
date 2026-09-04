<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['return_date', 'responsable_signature','administrador_signature','observations','delivery_document_id', 'employee_id','user_id'])]
class ReturnDocument extends Model
{
    protected $table = 'return_documents';

    protected $casts = [
        'return_date' => 'datetime'
    ];

    public function delivery_document(){
        return $this->belongsTo(DeliveryDocument::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function details(){
        return $this->hasMany(ReturnDocumentDetail::class);
    }
}
