<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TraccarController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/traccar/devices', [TraccarController::class, 'devices']);
Route::get('/traccar/positions', [TraccarController::class, 'positions']);
Route::get('/traccar/positions-flask', [TraccarController::class, 'positionsFromFlask']);
Route::get('/traccar/positions-cached', [TraccarController::class, 'cachedPositions']);
