<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:5173')),
    'guard' => ['web'],
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 43200),
];
