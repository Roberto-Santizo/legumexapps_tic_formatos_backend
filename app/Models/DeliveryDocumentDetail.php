<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['document_id','equipmet_id', 'observations'])]
class DeliveryDocumentDetail extends Model
{
    protected $table = 'delivery_documents_details';

    public function delivery_documents(){
        return $this->belongsTo(DeliveryDocument::class);
    }

    public function equipmets(){
        return $this->hasOne(Equipment::class);
    }
}
