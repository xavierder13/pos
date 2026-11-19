<?php

return [

    // The SPA (frontend/) calls the API cross-origin with a bearer token —
    // no cookies, so supports_credentials stays false.

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // comma-separated origins the React app is served from (.env FRONTEND_URLS)
    'allowed_origins' => array_filter(array_map('trim', explode(',', (string) env('FRONTEND_URLS', 'http://localhost:5173')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // file downloads: the SPA reads the file name
    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 0,

    'supports_credentials' => false,

];
