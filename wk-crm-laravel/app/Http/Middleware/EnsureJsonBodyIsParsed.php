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
                // getContent() is from Symfony\HttpFoundation\Request
                $rawContent = $request->getContent();
                
                \Log::debug('EnsureJsonBodyIsParsed middleware', [
                    'isJson' => $request->isJson(),
                    'isEmpty' => empty($request->all()),
                    'rawContent' => $rawContent,
                    'rawContentLength' => strlen($rawContent),
                ]);
                
                if (!empty($rawContent)) {
                    $data = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($data)) {
                        // Use replace to fully replace request data
                        $request->replace($data);
                        \Log::debug('Replaced request data', ['data' => $data]);
                    }
                }
            } catch (\JsonException $e) {
                \Log::error('JSON parse error', ['error' => $e->getMessage()]);
            }
        }

        return $next($request);
    }
}


