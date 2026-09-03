<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['return_date','responsable_signature','administrador_signature','observations','delivery_document_id'])]
class ReturnDocument extends Model
{
    protected $table = 'return_documents';

    public function delivery_documents(){
        return $this->belongsTo(DeliveryDocument::class);
    }
}
