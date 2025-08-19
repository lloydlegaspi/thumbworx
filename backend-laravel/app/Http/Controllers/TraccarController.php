<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Position;
use App\Models\Device;
use Carbon\Carbon;

class TraccarController extends Controller
{
    private function getTraccarConfig()
    {
        return [
            'base_url' => config('services.traccar.base_url', 'https://demo.traccar.org'),
            'user' => config('services.traccar.user', 'demo'),
            'pass' => config('services.traccar.pass', 'demo')
        ];
    }

    private function makeTraccarRequest($endpoint, $params = [])
    {
        $config = $this->getTraccarConfig();
        
        try {
            $response = Http::timeout(15)
                ->withBasicAuth($config['user'], $config['pass'])
                ->get("{$config['base_url']}/api/{$endpoint}", $params);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error("Traccar API error for {$endpoint}: " . $response->status() . " - " . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Traccar connection error for {$endpoint}: " . $e->getMessage());
            return null;
        }
    }

    public function devices()
    {
        // Try to get devices from cache first
        $cacheKey = 'traccar_devices';
        $cachedDevices = Cache::get($cacheKey);
        
        if ($cachedDevices) {
            return response()->json($cachedDevices);
        }

        // Fetch from Traccar
        $devices = $this->makeTraccarRequest('devices');
        
        if ($devices === null) {
            // Return fallback data if Traccar is unavailable
            return response()->json([
                'error' => 'Unable to connect to Traccar server',
                'fallback_devices' => []
            ], 503);
        }

        // Cache for 5 minutes
        Cache::put($cacheKey, $devices, 300);

        return response()->json($devices);
    }

    public function positions()
    {
        // Get fresh positions from Traccar
        $positions = $this->makeTraccarRequest('positions');
        
        if ($positions === null) {
            // Return cached positions from database if Traccar is unavailable
            $fallbackPositions = Position::with('device')
                ->latest('device_time')
                ->take(50)
                ->get()
                ->map(function ($position) {
                    return [
                        'id' => $position->id,
                        'deviceId' => $position->device_id,
                        'latitude' => (float) $position->latitude,
                        'longitude' => (float) $position->longitude,
                        'speed' => (float) $position->speed,
                        'deviceTime' => $position->device_time->toISOString(),
                        'attributes' => $position->attributes ?: new \stdClass()
                    ];
                });

            return response()->json($fallbackPositions);
        }

        // Persist to database for fallback and analytics
        $this->persistPositions($positions);

        return response()->json($positions);
    }

    private function persistPositions($positions)
    {
        try {
            foreach ($positions as $positionData) {
                // Parse device time
                $deviceTime = null;
                if (isset($positionData['deviceTime'])) {
                    try {
                        $deviceTime = Carbon::parse($positionData['deviceTime']);
                    } catch (\Exception $e) {
                        $deviceTime = Carbon::now();
                        Log::warning("Invalid device time format: " . $positionData['deviceTime']);
                    }
                } else {
                    $deviceTime = Carbon::now();
                }

                // Create or update position
                Position::updateOrCreate(
                    [
                        'device_id' => $positionData['deviceId'] ?? null,
                        'device_time' => $deviceTime
                    ],
                    [
                        'latitude' => $positionData['latitude'] ?? null,
                        'longitude' => $positionData['longitude'] ?? null,
                        'speed' => $positionData['speed'] ?? 0,
                        'attributes' => json_encode($positionData['attributes'] ?? [])
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error("Error persisting positions: " . $e->getMessage());
        }
    }
    
    public function positionsFromFlask()
    {
        // Proxy to Flask microservice
        $flaskUrl = env('FLASK_SERVICE_URL', 'https://thumbworx.onrender.com');
        
        try {
            $response = Http::timeout(15)->get("{$flaskUrl}/api/traccar/positions");
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error("Flask service error: " . $response->status());
                return response()->json(['error' => 'Flask service unavailable'], 503);
            }
        } catch (\Exception $e) {
            Log::error("Flask connection error: " . $e->getMessage());
            return response()->json(['error' => 'Unable to connect to Flask service'], 503);
        }
    }
    
    public function cachedPositions()
    {
        // First try Flask cached positions
        $flaskUrl = env('FLASK_SERVICE_URL', 'https://thumbworx.onrender.com');
        
        try {
            $response = Http::timeout(10)->get("{$flaskUrl}/api/positions_cached");
            
            if ($response->successful() && !empty($response->json())) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            Log::warning("Flask cached positions unavailable: " . $e->getMessage());
        }

        // Fallback to database positions
        $dbPositions = Position::latest()
            ->latest('device_time')
            ->take(20)
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'deviceId' => $position->device_id,
                    'latitude' => (float) $position->latitude,
                    'longitude' => (float) $position->longitude,
                    'speed' => (float) $position->speed,
                    'deviceTime' => $position->device_time ? $position->device_time->toISOString() : null,
                    'attributes' => $position->attributes ?: new \stdClass()
                ];
            });

        return response()->json($dbPositions);
    }

    public function healthCheck()
    {
        $config = $this->getTraccarConfig();
        $status = [
            'traccar' => false,
            'flask' => false,
            'database' => false
        ];

        // Test Traccar connection
        try {
            $traccarTest = $this->makeTraccarRequest('devices');
            $status['traccar'] = $traccarTest !== null;
        } catch (\Exception $e) {
            Log::error("Traccar health check failed: " . $e->getMessage());
        }

        // Test Flask connection
        $flaskUrl = env('FLASK_SERVICE_URL', 'https://thumbworx.onrender.com');
        try {
            $flaskResponse = Http::timeout(10)->get("{$flaskUrl}/health");
            $status['flask'] = $flaskResponse->successful();
        } catch (\Exception $e) {
            Log::error("Flask health check failed: " . $e->getMessage());
        }

        // Test database
        try {
            Position::count();
            $status['database'] = true;
        } catch (\Exception $e) {
            Log::error("Database health check failed: " . $e->getMessage());
        }

        return response()->json([
            'status' => 'ok',
            'services' => $status,
            'config' => [
                'traccar_url' => $config['base_url'],
                'traccar_user' => $config['user'],
                'flask_url' => $flaskUrl
            ],
            'timestamp' => Carbon::now()->toISOString()
        ]);
    }
}
