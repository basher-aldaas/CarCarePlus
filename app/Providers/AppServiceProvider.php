<?php

namespace App\Providers;

use App\Models\Car;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Observers\AuditLogObserver;
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
     *
     * Notification events (App\Events\Operations\*) are matched to their
     * listeners (App\Listeners\Operations\*) automatically via Laravel's event
     * discovery, so they don't need to be registered here.
     */
    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);
        Inventory::observe(InventoryObserver::class);
    }
}
