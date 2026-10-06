<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ProductAvailability;
use App\Enums\ProductStatus;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'nom', 'slug', 'description', 'category_id', 'vendor_id', 'marque', 'reference',
        'type_disponibilite', 'necessite_installation', 'necessite_certification',
        'prix_vente', 'prix_location_jour', 'prix_location_semaine',
        'prix_location_mois', 'caution_location', 'poids', 'dimensions', 'statut',
        'prix_promo', 'promo_debut', 'promo_fin',
    ];


    protected function casts(): array
    {
        return [
            'type_disponibilite' => ProductAvailability::class,
            'statut' => ProductStatus::class,
            'necessite_installation' => 'boolean',
            'necessite_certification' => 'boolean',
            'prix_vente' => 'decimal:2',
            'prix_location_jour' => 'decimal:2',
            'prix_promo' => 'decimal:2',
            'promo_debut' => 'datetime',
            'promo_fin' => 'datetime',
            'couleurs' => 'array',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('ordre');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    // Scopes utiles pour le catalogue
    public function scopeDisponiblesALaVente($query)
    {
        return $query->whereIn('type_disponibilite', [
            ProductAvailability::Vente,
            ProductAvailability::LesDeux,
        ]);
    }

    public function scopeDisponiblesALaLocation($query)
    {
        return $query->whereIn('type_disponibilite', [
            ProductAvailability::Location,
            ProductAvailability::LesDeux,
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $nomNormalise = \Normalizer::normalize($product->nom, \Normalizer::FORM_C);
            $base = Str::slug($nomNormalise);
            $slug = $base;
            $i = 1;
            while (static::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$i}";
                $i++;
            }
            $product->slug = $slug;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getEnPromoAttribute(): bool
    {
        if (!$this->prix_promo || !$this->promo_fin) {
            return false;
        }
        $maintenant = now();
        $debutOk = !$this->promo_debut || $this->promo_debut <= $maintenant;
        return $debutOk && $this->promo_fin >= $maintenant;
    }

    public function getPourcentageReductionAttribute(): ?int
    {
        if (!$this->en_promo || !$this->prix_vente) {
            return null;
        }
        return (int) round((1 - $this->prix_promo / $this->prix_vente) * 100);
    }

}

