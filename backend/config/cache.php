<?php

return [
    'default' => env('CACHE_STORE', 'redis'),
    'stores' => [
        'array' => ['driver' => 'array'],
        'redis' => ['driver' => 'redis', 'connection' => 'cache'],
    ],
    'prefix' => env('CACHE_PREFIX', 'jobs_cache'),
];
