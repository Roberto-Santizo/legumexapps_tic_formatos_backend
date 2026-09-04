<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['observations','delivery_document_detail_id', 'return_document_id'])]
class ReturnDocumentDetail extends Model
{
    protected $table = 'return_document_details';

    public function delivery_document_details(){
        return $this->belongsTo(DeliveryDocumentDetail::class,'delivery_document_detail_id');
    }

    public function return_document(){
        return $this->belongsTo(ReturnDocument::class);    
    }
}
