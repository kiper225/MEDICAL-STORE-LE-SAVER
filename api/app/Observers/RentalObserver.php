<?php

namespace App\Observers;

use App\Models\Rental;
use App\Models\Installation;
use App\Enums\{RentalStatus, InstallationStatus};

class RentalObserver
{
    public function created(Rental $rental): void
    {
        if ($rental->statut !== RentalStatus::Reserve) {
            return;
        }

        if ($rental->product->necessite_installation && !$rental->installation()->exists()) {
            Installation::create([
                'rental_id' => $rental->id,
                'adresse_installation' => $rental->user->adresse ?? 'Adresse à confirmer avec le client',
                'date_prevue' => $rental->date_debut,
                'creneau_horaire' => 'a_planifier',
                'statut' => InstallationStatus::Planifiee,
            ]);
        }
    }
}