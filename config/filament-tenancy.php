<?php

return [
    "central_domain" => env('CENTRAL_DOMAIN', 'localhost'),

    "features" => [
        "homepage" => true,
        "auth" => true,
        "impersonation" => true,
    ],

      'cache_tenant' => true,
    'cache_ttl' => 3600, // 1 hour in seconds
    'tenant_finder' => [
        'cache' => true,
        'cache_ttl' => 1800, // 30 minutes
    ],

    // Enable tenant-specific caching
    'tenant_cache_prefix' => true,
];
