<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticateQueryToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if token is in Authorization header first
        if ($request->bearerToken()) {
            return $next($request);
        }

        // Check if token is in query parameter
        $token = $request->query('token');
        if ($token) {
            try {
                $accessToken = PersonalAccessToken::findToken($token);
                if ($accessToken) {
                    $request->setUserResolver(function () use ($accessToken) {
                        return $accessToken->tokenable;
                    });
                }
            } catch (\Exception $e) {
                // Token invalid, continue without authentication
            }
        }

        return $next($request);
    }
}
