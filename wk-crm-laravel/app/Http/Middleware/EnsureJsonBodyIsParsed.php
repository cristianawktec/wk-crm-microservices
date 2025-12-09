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
            try {
                // Get the Symfony request from Laravel request
                $symRequest = $request->getSymfonyRequest();
                $rawContent = $symRequest->getContent();
                
                if (!empty($rawContent)) {
                    $data = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($data)) {
                        // Use replace instead of merge to fully replace request data
                        $request->replace($data);
                    }
                }
            } catch (\JsonException $e) {
                // Continue with empty request - validation will handle it
            }
        }

        return $next($request);
    }
}

