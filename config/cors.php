<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',      // React dev server
        'http://localhost:5173',      // Vite dev server  
        'http://localhost:5601',      // Your mobile app
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5601',
        'https://localhost:3000',     // HTTPS versions
        'https://localhost:5173',
        'https://localhost:5601',
        'https://127.0.0.1:3000',
        'https://127.0.0.1:5173',
        'https://127.0.0.1:5601',
        // Add your production domains here
        // 'https://yourdomain.com',
        // 'https://app.yourdomain.com',
    ],

    'allowed_origins_patterns' => [
        // Allow any localhost port
        '/^http:\/\/localhost:\d+$/',
        '/^https:\/\/localhost:\d+$/',
        '/^http:\/\/127\.0\.0\.1:\d+$/',
        '/^https:\/\/127\.0\.0\.1:\d+$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];