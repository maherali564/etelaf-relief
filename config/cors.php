<?php

return [
    'paths' => ['api/*', 'chat/*', '*/currency/rates', '*/donations/latest'],
    'allowed_methods' => ['GET', 'POST'],
    'allowed_origins' => [env('APP_URL', 'http://localhost')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'X-CSRF-TOKEN'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
