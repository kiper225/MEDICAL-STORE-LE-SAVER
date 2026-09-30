<?php

namespace App\Models;

use App\Enums\InstallationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installation extends Model
{
    protected $fillable = [
        'order_id',
        'rental_id',
        'technicien_id',
        'adresse_installation',
        'date_prevue',
        'creneau_horaire',
        'statut',
        'signature_client',
        'rapport_intervention',
    ];

    protected function casts(): array
    {
        return [
            'statut' => InstallationStatus::class,
            'date_prevue' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }
}