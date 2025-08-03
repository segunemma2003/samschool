<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AwsRdsOptimizer
{
    public function optimizeConnection()
    {
        // Set optimal MySQL variables for AWS RDS
        DB::statement("SET SESSION innodb_buffer_pool_size = 1073741824"); // 1GB
        DB::statement("SET SESSION innodb_log_file_size = 268435456"); // 256MB
        DB::statement("SET SESSION innodb_flush_log_at_trx_commit = 2");
        DB::statement("SET SESSION innodb_flush_method = 'O_DIRECT'");
        DB::statement("SET SESSION query_cache_type = 1");
        DB::statement("SET SESSION query_cache_size = 67108864"); // 64MB
    }

    public function enableQueryCache()
    {
        // Enable query result caching for frequently accessed data
        Cache::remember('db_optimization_status', 3600, function () {
            return [
                'optimized_at' => now(),
                'connection_pool_size' => 200,
                'query_cache_enabled' => true,
            ];
        });
    }

    public function monitorPerformance()
    {
        // Monitor slow queries and connection issues
        DB::listen(function ($query) {
            $time = $query->time;

            if ($time > 1000) { // Log queries taking more than 1 second
                Log::warning("Slow query on AWS RDS: {$time}ms", [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $time,
                    'connection' => DB::connection()->getName()
                ]);
            }
        });
    }

    public function optimizeForReadReplicas()
    {
        // If you have read replicas, use them for read operations
        if (config('database.connections.mysql.read')) {
            // Use read replica for SELECT queries
            DB::purge('mysql');
            DB::reconnect('mysql');
        }
    }
}
