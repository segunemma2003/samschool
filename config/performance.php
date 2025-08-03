<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Settings
    |--------------------------------------------------------------------------
    */

    // Database query optimization
    'database' => [
        'query_timeout' => 30,
        'max_connections' => 100,
        'connection_timeout' => 10,
        'enable_query_log' => false,
        'enable_slow_query_log' => true,
        'slow_query_threshold' => 2.0, // seconds
    ],

    // Cache optimization
    'cache' => [
        'default_ttl' => 3600, // 1 hour
        'session_ttl' => 1800, // 30 minutes
        'view_ttl' => 7200,    // 2 hours
        'route_ttl' => 86400,  // 24 hours
    ],

    // File optimization
    'files' => [
        'max_upload_size' => '10M',
        'enable_compression' => true,
        'optimize_images' => true,
    ],

    // Session optimization
    'session' => [
        'lifetime' => 120, // 2 hours
        'expire_on_close' => false,
        'secure_cookies' => true,
    ],

    // Queue optimization
    'queue' => [
        'default_timeout' => 60,
        'max_attempts' => 3,
        'retry_after' => 90,
    ],
];
