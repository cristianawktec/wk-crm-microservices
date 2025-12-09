<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJsonBodyIsParsed
{
    /**
     * Handle an incoming request.
     *
     * Ensures that JSON request bodies are properly parsed, even if
     * consumed by previous middleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If request claims to be JSON but has no data, try reading raw input
        if ($request->isJson() && empty($request->all())) {
            $rawInput = file_get_contents('php://input');
            if (!empty($rawInput)) {
                try {
                    $data = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($data)) {
                        // Replace request data with parsed JSON
                        $request->replace($data);
                    }
                } catch (\JsonException $e) {
                    // Continue with empty request
                }
            }
        }

        return $next($request);
    }
}
