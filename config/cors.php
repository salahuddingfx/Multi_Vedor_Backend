<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter(array_merge(
        explode(',', env('ADMIN_URL', 'http://localhost:5173,http://127.0.0.1:5173')),
        explode(',', env('TAJASHUTKI_URL', 'http://localhost:5174,http://127.0.0.1:5174')),
        explode(',', env('ACHARU_URL', 'http://localhost:5175,http://127.0.0.1:5175'))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

