<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Facades\Tenancy;

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
        // If the request is from a tenant
    // if (Tenancy::getTenant()) {
    //     // Use the tenant's domain to generate asset URLs
    //     URL::forceRootUrl(Tenancy::getTenant()->domains->first()->domain);
    // } else {
    //     // For central domain, fallback to APP_URL
    //     URL::forceRootUrl(config('app.url'));
    // }

    // // Force HTTPS if applicable
    // if (config('app.env') !== 'local') {
    //     URL::forceScheme('https');
    // }
    }
}
