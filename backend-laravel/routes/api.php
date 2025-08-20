<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TraccarController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Debug endpoint to test CORS and basic connectivity
Route::get('/debug', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'cors_test' => 'CORS working',
        'env' => [
            'APP_ENV' => config('app.env'),
            'APP_DEBUG' => config('app.debug'),
            'APP_URL' => config('app.url'),
        ]
    ]);
});

// Health check endpoint
Route::get('/health', [TraccarController::class, 'healthCheck']);

// Traccar data endpoints
Route::get('/traccar/devices', [TraccarController::class, 'devices']);
Route::get('/traccar/positions', [TraccarController::class, 'positions']);
Route::get('/traccar/positions-flask', [TraccarController::class, 'positionsFromFlask']);
Route::get('/traccar/positions-cached', [TraccarController::class, 'cachedPositions']);
