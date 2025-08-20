<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get allowed origins from config
        $allowedOrigins = [
            'http://localhost:3000',
            'http://localhost:3001',
            'https://thumbworx.vercel.app',
            'https://thumbworx-git-main-john-lloyd-legaspis-projects.vercel.app',
        ];
        
        // Additional patterns for Vercel deployments
        $allowedPatterns = [
            'https://thumbworx-*.vercel.app',
            'https://*-thumbworx*.vercel.app',
            'https://thumbworx*.vercel.app'
        ];
        
        $origin = $request->header('Origin');
        $allowedOrigin = null;
        
        // Check if origin is in exact matches
        if ($origin && in_array($origin, $allowedOrigins)) {
            $allowedOrigin = $origin;
        }
        
        // Check if origin matches patterns
        if (!$allowedOrigin && $origin) {
            foreach ($allowedPatterns as $pattern) {
                if (fnmatch($pattern, $origin)) {
                    $allowedOrigin = $origin;
                    break;
                }
            }
        }
        
        // Fallback for development or if no specific origin
        if (!$allowedOrigin) {
            $allowedOrigin = $origin ?: '*';
        }

        // Handle preflight OPTIONS requests
        if ($request->getMethod() === "OPTIONS") {
            return response('', 204)
                ->header('Access-Control-Allow-Origin', $allowedOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-Requested-With, Origin, Cache-Control, Pragma, X-CSRF-TOKEN, X-XSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        // Add CORS headers to actual requests
        $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-Requested-With, Origin, Cache-Control, Pragma, X-CSRF-TOKEN, X-XSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
