<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    private $allowedOrigins = [
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:8080',
        'http://localhost:8081',
        'https://app.consultoriawk.com',
        'https://api.consultoriawk.com',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
    ];

    /**
     * Handle an incoming request by adding CORS headers.
     */
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->headers->get('Origin');
        $allowOrigin = in_array($origin, $this->allowedOrigins) ? $origin : null;

        $headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, Accept, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
        ];

        if ($allowOrigin) {
            $headers['Access-Control-Allow-Origin'] = $allowOrigin;
        }

        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }
        return $response;
    }
}
