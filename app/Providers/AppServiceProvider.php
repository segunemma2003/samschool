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
         $this->conditionallyRegisterPanels();
    }



    private function conditionallyRegisterPanels(): void
    {
        $request = request();

        // Only load the panel that's actually being accessed
        if ($request->is('admin/*')) {
            $this->app->register(\App\Providers\Filament\AdminPanelProvider::class);
        } elseif ($request->is('app/*')) {
            $this->app->register(\App\Providers\Filament\AppPanelProvider::class);
        } elseif ($request->is('teacher/*')) {
            // dd("test run");
            $this->app->register(\App\Providers\Filament\TeacherPanelProvider::class);
        } elseif ($request->is('student/*')) {
            $this->app->register(\App\Providers\Filament\OurstudentPanelProvider::class);
        } elseif ($request->is('parent/*')) {
            $this->app->register(\App\Providers\Filament\OurparentPanelProvider::class);
        } elseif ($request->is('finance/*')) {
            $this->app->register(\App\Providers\Filament\FinancePanelProvider::class);
        } elseif ($request->is('hostel/*')) {
            $this->app->register(\App\Providers\Filament\HostelPanelProvider::class);
        } elseif ($request->is('library/*')) {
            $this->app->register(\App\Providers\Filament\LibraryPanelProvider::class);
        } else {
            // Default to app panel for other routes
            $this->app->register(\App\Providers\Filament\AppPanelProvider::class);
        }
    }

    /**
     *
     * Bootstrap any application services.
     */
    public function boot(): void
{
    // Force close database connections after each request
    if (!app()->runningInConsole()) {
        register_shutdown_function(function() {
            try {
                // Disconnect all connections
                DB::disconnect();
                DB::purge('mysql');
                DB::purge('dynamic');

                // Force garbage collection
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            } catch (\Exception $e) {
                // Log but don't break the response
                Log::error('Connection cleanup error: ' . $e->getMessage());
            }
        });
    }

    // Suppress deprecation warnings from specific packages
    if (app()->environment('production') || app()->environment('staging')) {
        set_error_handler(function ($severity, $message, $file, $line) {
            // Suppress deprecation warnings from unisharp/laravel-filemanager
            if ($severity === E_DEPRECATED && strpos($file, 'unisharp/laravel-filemanager') !== false) {
                return true; // Suppress the warning
            }

            // Suppress deprecation warnings from composer itself
            if ($severity === E_DEPRECATED && strpos($file, 'phar://') !== false) {
                return true; // Suppress the warning
            }

            // Let other errors through
            return false;
        }, E_DEPRECATED);
    }
}
}
