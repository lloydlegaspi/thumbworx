<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | (CORS). This is a security feature that restricts cross-origin HTTP
    | requests from scripts running in the browser.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS') ? 
        explode(',', env('CORS_ALLOWED_ORIGINS')) : 
        ['http://localhost:3000', 'https://thumbworx.vercel.app', 'https://*.vercel.app'],
    'allowed_origins_patterns' => ['https://*-thumbworx*.vercel.app', 'https://thumbworx*.vercel.app'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,

];
