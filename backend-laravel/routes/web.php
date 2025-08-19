<?php

use Illuminate\Support\Facades\Route;

// Most basic test - no Laravel features
Route::get('/basic', function () {
    return 'PHP is working! Time: ' . date('Y-m-d H:i:s');
});

// Test with some Laravel features
Route::get('/test', function () {
    try {
        return '<h1>Laravel Test</h1>' .
               '<p>App Name: ' . config('app.name', 'Not Set') . '</p>' .
               '<p>Environment: ' . config('app.env', 'Not Set') . '</p>' .
               '<p>Database: ' . config('database.default', 'Not Set') . '</p>' .
               '<p>Time: ' . now() . '</p>';
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Test route with simple view
Route::get('/simple', function () {
    return view('simple');
});

Route::get('/', function () {
    return view('welcome');
});
