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
        if (app()->environment('local', 'development')) {
            DB::listen(function ($query) {
                if ($query->time > 100) { // Lower threshold
                    Log::warning('Slow Query Detected', [
                        'sql' => $query->sql,
                        'time' => $query->time . 'ms',
                        'bindings' => $query->bindings,
                        'location' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
                    ]);
                }
            });

            // Memory usage monitoring
            register_shutdown_function(function() {
                $memory = memory_get_peak_usage(true) / 1024 / 1024;
                if ($memory > 128) { // MB
                    Log::warning("High memory usage: {$memory}MB");
                }
            });
        }
    }
}
