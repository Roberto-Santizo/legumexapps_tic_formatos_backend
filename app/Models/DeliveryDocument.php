<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['location', 'delivery_date', 'responsable_signature', 'administrador_signature', 'employee_id', 'user_id', 'observations'])]
class DeliveryDocument extends Model
{
    protected $table = 'delivery_documents';

    protected $casts = [
        'delivery_date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(DeliveryDocumentDetail::class);
    }

    public function return_documents()
    {
        return $this->hasMany(ReturnDocument::class);
    }

    /**
     * Detalles de la entrega que todavía no tienen devolución registrada.
     */
    public function pendingDetails()
    {
        return $this->details()->whereDoesntHave('returnDetail');
    }

    /**
     * Estado de la entrega según cuántos equipos se han devuelto.
     *
     * @return 'pendiente'|'parcial'|'devuelto'
     */
    public function status(): string
    {
        $details = $this->relationLoaded('details') ? $this->details : $this->details()->with('returnDetail')->get();

        $total = $details->count();
        $returned = $details->filter(fn (DeliveryDocumentDetail $detail) => $detail->returnDetail !== null)->count();

        if ($returned === 0) {
            return 'pendiente';
        }

        return $returned < $total ? 'parcial' : 'devuelto';
    }
}
