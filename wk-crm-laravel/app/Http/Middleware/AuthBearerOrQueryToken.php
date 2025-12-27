<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthBearerOrQueryToken
{
    public function handle(Request $request, Closure $next)
    {
        // Try Bearer token first (standard)
        if ($request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            if ($token) {
                Auth::setUser($token->tokenable);
                return $next($request);
            }
        }

        // Fallback to query param token (for SSE/EventSource)
        if ($request->query('token')) {
            $token = PersonalAccessToken::findToken($request->query('token'));
            if ($token) {
                Auth::setUser($token->tokenable);
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthenticated'], 401);
    }
}
