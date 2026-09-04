<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['delivery_document_id','equipment_id', 'observations'])]
class DeliveryDocumentDetail extends Model
{
    protected $table = 'delivery_document_details';

    public function delivery_documents(){
        return $this->belongsTo(DeliveryDocument::class);
    }

    public function equipment(){
        return $this->hasOne(Equipment::class, 'id', 'equipment_id');
    }
}
