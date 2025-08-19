<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TraccarController extends Controller
{
    public function devices()
    {
        $base = config('services.traccar.base_url');
        $user = config('services.traccar.user');
        $pass = config('services.traccar.pass');
        $res = Http::withBasicAuth($user, $pass)->get("{$base}/api/devices");
        return response()->json($res->json());
    }

    public function positions()
    {
        $base = config('services.traccar.base_url');
        $user = config('services.traccar.user');
        $pass = config('services.traccar.pass');
        $res = Http::withBasicAuth($user, $pass)->get("{$base}/api/positions");
        // optional: persist to DB or publish to Redis using predis
        return response()->json($res->json());
    }
    
    public function positionsFromFlask()
    {
        // Proxy to Flask microservice
        $flaskUrl = env('FLASK_SERVICE_URL', 'http://flask:5000');
        $res = Http::get("{$flaskUrl}/api/traccar/positions");
        return response()->json($res->json());
    }
    
    public function cachedPositions()
    {
        // Get cached positions from Flask
        $flaskUrl = env('FLASK_SERVICE_URL', 'http://flask:5000');
        $res = Http::get("{$flaskUrl}/api/positions_cached");
        return response()->json($res->json());
    }
}
