<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:4200',
        'http://localhost:3000',
        'https://offers-sampling-stable-sunrise.trycloudflare.com',
    ],

    'allowed_origins_patterns' => [
        '#^https://.*\.trycloudflare\.com$#',
        '#^http://.*\.trycloudflare\.com$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];