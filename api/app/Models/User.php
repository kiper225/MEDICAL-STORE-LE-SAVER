<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- ajouter
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable; // <-- ajouter HasFactory ici

    protected $fillable = [
        'nom', 'email', 'password', 'role', 'genre', 'telephone', 'adresse', 'photo', 'google_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function installationsAssignees(): HasMany
    {
        return $this->hasMany(Installation::class, 'technicien_id');
    }

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}