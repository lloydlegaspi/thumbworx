<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'health',
        'up',
    ],

    'allowed_methods' => ['*'],

    // Hardcode the allowed origins directly
    'allowed_origins' => [
        'https://thumbworx.vercel.app',
        'https://thumbworx-production.vercel.app',
        'http://localhost:3000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Origin',
        'Cache-Control',
        'Pragma',
    ],

    'exposed_headers' => [
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma',
    ],

    'max_age' => 86400, // 24 hours

    'supports_credentials' => true,

];