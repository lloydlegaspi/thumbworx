<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Test route without Vite
Route::get('/test', function () {
    return '<h1>Laravel is working!</h1><p>Database: ' . config('database.default') . '</p>';
});

// Test route with simple view
Route::get('/simple', function () {
    return view('simple');
});
