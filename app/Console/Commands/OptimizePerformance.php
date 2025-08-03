<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class OptimizePerformance extends Command
{
    protected $signature = 'app:optimize-performance';
    protected $description = 'Optimize Laravel application performance';

    public function handle()
    {
        $this->info('Starting performance optimization...');

        // 1. Clear all caches
        $this->info('Clearing caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // 2. Optimize autoloader
        $this->info('Optimizing autoloader...');
        exec('composer dump-autoload --optimize');

        // 3. Cache configuration
        $this->info('Caching configuration...');
        Artisan::call('config:cache');

        // 4. Cache routes
        $this->info('Caching routes...');
        Artisan::call('route:cache');

        // 5. Cache views
        $this->info('Caching views...');
        Artisan::call('view:cache');

        // 6. Optimize database
        $this->optimizeDatabase();

        // 7. Clear old logs
        $this->clearOldLogs();

        $this->info('Performance optimization completed!');
    }

    private function optimizeDatabase()
    {
        $this->info('Optimizing database...');

        try {
            // Analyze tables for better query planning
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                DB::statement("ANALYZE TABLE `{$tableName}`");
            }
        } catch (\Exception $e) {
            $this->warn('Database optimization failed: ' . $e->getMessage());
        }
    }

    private function clearOldLogs()
    {
        $this->info('Clearing old logs...');

        $logPath = storage_path('logs');
        $files = File::glob($logPath . '/*.log');

        foreach ($files as $file) {
            if (File::size($file) > 10 * 1024 * 1024) { // 10MB
                File::delete($file);
                $this->line("Deleted large log file: " . basename($file));
            }
        }
    }
}
