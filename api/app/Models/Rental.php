<?php

namespace App\Models;

use App\Enums\RentalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rental extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'warehouse_id',
        'date_debut',
        'date_fin',
        'date_retour_effective',
        'statut',
        'montant_caution',
        'montant_total',
    ];

    protected function casts(): array
    {
        return [
            'statut' => RentalStatus::class,
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_retour_effective' => 'date',
            'montant_caution' => 'decimal:2',
            'montant_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function installation(): HasOne
    {
        return $this->hasOne(Installation::class);
    }
}