<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Rental;
use App\Observers\OrderObserver;
use App\Observers\RentalObserver;
use Illuminate\Support\ServiceProvider; // <-- ajouter cet import

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Rental::observe(RentalObserver::class);
    }
}