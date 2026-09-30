<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Installation;
use App\Enums\InstallationStatus;

class OrderObserver
{
    public function updated(Order $order): void
    {
        // On déclenche l'installation seulement quand la commande passe à "payé"
        if (!$order->wasChanged('statut') || $order->statut->value !== 'paye') {
            return;
        }

        $necessiteInstallation = $order->items()
            ->whereHas('product', fn($q) => $q->where('necessite_installation', true))
            ->exists();

        if ($necessiteInstallation && !$order->installation()->exists()) {
            Installation::create([
                'order_id' => $order->id,
                'adresse_installation' => $order->user->adresse,
                'date_prevue' => now()->addDays(3), // valeur par défaut, à ajuster manuellement ensuite
                'creneau_horaire' => 'a_planifier',
                'statut' => InstallationStatus::Planifiee,
            ]);
        }
    }
}