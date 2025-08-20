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

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'health',
        'up'
    ],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3001',
        'https://thumbworx.vercel.app',
        'https://thumbworx-*.vercel.app',
        'https://*.vercel.app'
    ],
    
    'allowed_origins_patterns' => [
        'https://*-thumbworx*.vercel.app',
        'https://thumbworx*.vercel.app',
        'https://thumbworx-production-*.vercel.app'
    ],
    
    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Origin',
        'Cache-Control',
        'Pragma'
    ],
    
    'exposed_headers' => [
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma'
    ],
    
    'max_age' => 86400, // 24 hours
    
    'supports_credentials' => true,

];
