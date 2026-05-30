<?php

return [
    'default' => env('MAIL_MAILER', 'log'),
    'mailers' => [
        'smtp' => ['transport' => 'smtp', 'host' => env('MAIL_HOST'), 'port' => env('MAIL_PORT', 587), 'encryption' => env('MAIL_ENCRYPTION'), 'username' => env('MAIL_USERNAME'), 'password' => env('MAIL_PASSWORD')],
        'log' => ['transport' => 'log'],
        'array' => ['transport' => 'array'],
    ],
    'from' => ['address' => env('MAIL_FROM_ADDRESS', 'noreply@jobs.test'), 'name' => env('MAIL_FROM_NAME', 'Job Portal')],
];
