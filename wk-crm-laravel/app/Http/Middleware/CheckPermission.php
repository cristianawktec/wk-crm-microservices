<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Usage:
     * Route::get('/customers', [CustomerController::class, 'index'])
     *     ->middleware('permission:read_customers');
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user() || !$request->user()->hasPermissionTo($permission)) {
            return response()->json([
                'success' => false,
                'message' => "User does not have permission to {$permission}",
            ], 403);
        }

        return $next($request);
    }
}
