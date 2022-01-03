<?php

namespace App\Providers;

use App\Models\Organization;
use App\Services\BigQueryService;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BigQueryService::class, function($app) {
            return new BigQueryService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cashier::useCustomerModel(Organization::class);
        // Cashier::calculateTaxes();
    }
}
