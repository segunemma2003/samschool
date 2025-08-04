<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearCachesSilently extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-silent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all caches without showing deprecation warnings';

    /**
     * Execute the console command.
     */
        public function handle()
    {
        // Suppress deprecation warnings temporarily
        $originalErrorReporting = error_reporting();
        error_reporting($originalErrorReporting & ~E_DEPRECATED);

        // Also suppress deprecation warnings from specific packages
        set_error_handler(function ($severity, $message, $file, $line) {
            // Suppress deprecation warnings from various packages
            if ($severity === E_DEPRECATED) {
                $suppressPatterns = [
                    'unisharp/laravel-filemanager',
                    'mohsenabrishami/stethoscope',
                    'phar://',
                    'vendor/'
                ];

                foreach ($suppressPatterns as $pattern) {
                    if (strpos($file, $pattern) !== false) {
                        return true; // Suppress the warning
                    }
                }
            }

            return false; // Let other errors through
        }, E_DEPRECATED);

        try {
            $this->info('🧹 Clearing application caches...');

            // Clear various caches
            Artisan::call('cache:clear');
            $this->info('✓ Application cache cleared');

            Artisan::call('config:clear');
            $this->info('✓ Configuration cache cleared');

            Artisan::call('route:clear');
            $this->info('✓ Route cache cleared');

            Artisan::call('view:clear');
            $this->info('✓ View cache cleared');

            // Note: bootstrap:clear doesn't exist in Laravel 11, using optimize:clear instead
            Artisan::call('optimize:clear');
            $this->info('✓ Optimize cache cleared');

            $this->info('✅ All caches cleared successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Error clearing caches: ' . $e->getMessage());
            return 1;
        } finally {
            // Restore original error reporting
            error_reporting($originalErrorReporting);
        }

        return 0;
    }
}
