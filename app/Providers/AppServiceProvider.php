<?php

namespace App\Providers;

use App\Models\Sale;
use App\Models\Transaction;
use App\Observers\SaleObserver;
use App\Observers\TransactionObserver;
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
        // Register observers
        Transaction::observe(TransactionObserver::class);
        Sale::observe(SaleObserver::class);
    }
}
