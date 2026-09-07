<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['return_date', 'responsable_signature', 'administrador_signature', 'observations', 'delivery_document_id', 'user_id'])]
class ReturnDocument extends Model
{
    protected $table = 'return_documents';

    protected $casts = [
        'return_date' => 'datetime',
    ];

    public function delivery_document()
    {
        return $this->belongsTo(DeliveryDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(ReturnDocumentDetail::class);
    }
}
