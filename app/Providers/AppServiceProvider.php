<?php

namespace App\Providers;

use App\Models\Inventory;
use App\Models\Payment;
use App\Observers\InventoryObserver;
use App\Observers\PaymentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);
        Inventory::observe(InventoryObserver::class);
    }
}
