<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Part;
use App\Models\StockMovement;
use App\Observers\SystemActionObserver;
use Illuminate\Support\ServiceProvider;

// Purpose: Boots application services such as model observers.
class AppServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        
    }

    
    public function boot(): void
    {
        Part::observe(SystemActionObserver::class);
        Customer::observe(SystemActionObserver::class);
        JobOrder::observe(SystemActionObserver::class);
        StockMovement::observe(SystemActionObserver::class);
    }
}
