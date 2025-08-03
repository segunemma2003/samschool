<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\AwsRdsOptimizer;

class PerformanceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AwsRdsOptimizer::class, function ($app) {
            return new AwsRdsOptimizer();
        });
    }

    public function boot()
    {
        // Initialize AWS RDS optimizations
        $rdsOptimizer = app(AwsRdsOptimizer::class);

        // Enable query logging in development
        if (config('app.debug') && config('performance.database.enable_query_log')) {
            $rdsOptimizer->monitorPerformance();
        }

        // Optimize for production
        if (app()->environment('production')) {
            try {
                $rdsOptimizer->optimizeConnection();
                $rdsOptimizer->enableQueryCache();
            } catch (\Exception $e) {
                Log::warning('Failed to optimize AWS RDS connection: ' . $e->getMessage());
            }
        }

        // Optimize view caching
        if (app()->environment('production')) {
            $this->app['view']->addLocation(storage_path('framework/views'));
        }
    }
}
