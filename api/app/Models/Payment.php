<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'rental_id', 'montant', 'methode', 'operateur',
        'telephone', 'statut', 'reference_client', 'reference_intouch', 'payload_retour',
    ];

    protected function casts(): array
    {
        return ['payload_retour' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}