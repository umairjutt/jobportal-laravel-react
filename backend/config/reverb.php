<?php

return [
    'default' => 'reverb',
    'servers' => [
        'reverb' => [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => env('REVERB_SERVER_PORT', 8080),
            'hostname' => env('REVERB_HOST'),
            'options' => ['tls' => []],
            'max_request_size' => 10_000,
            'scaling' => ['enabled' => false],
            'pulse_ingest_interval' => 15,
            'telescope_ingest_interval' => 15,
        ],
    ],
    'apps' => [
        'provider' => 'config',
        'apps' => [
            [
                'app_id' => env('REVERB_APP_ID', 'local'),
                'key' => env('REVERB_APP_KEY', 'localkey'),
                'secret' => env('REVERB_APP_SECRET', 'localsecret'),
                'options' => ['host' => env('REVERB_HOST'), 'port' => env('REVERB_PORT', 443), 'scheme' => env('REVERB_SCHEME', 'https'), 'useTLS' => true],
                'allowed_origins' => ['*'],
                'ping_interval' => 60,
                'max_message_size' => 10_000,
            ],
        ],
    ],
];
