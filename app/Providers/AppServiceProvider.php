<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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


    if (app()->environment('local', 'development')) {
        DB::listen(function ($query) {
            if ($query->time > 1000) {
                Log::warning('Slow Query', [
                    'sql' => $query->sql,
                    'time' => $query->time,
                    'bindings' => $query->bindings
                ]);
            }
        });
    }
    }
}
