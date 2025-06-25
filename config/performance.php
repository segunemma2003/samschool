<?php

// 1. Create a config file for caching strategies
// config/performance.php
return [
    'cache' => [
        'teacher_lookup_ttl' => 300, // 5 minutes
        'form_options_ttl' => 600,   // 10 minutes
        'result_data_ttl' => 1800,   // 30 minutes
        'academic_data_ttl' => 3600, // 1 hour
    ],

    'query_optimization' => [
        'enable_eager_loading' => true,
        'enable_query_caching' => true,
        'max_records_per_page' => 25,
    ]
];
