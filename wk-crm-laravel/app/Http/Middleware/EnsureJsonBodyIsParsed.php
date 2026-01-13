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
        // ALWAYS parse JSON if content-type is JSON, regardless of whether request->all() is empty
        if ($request->isJson()) {
            try {
                $rawContent = $request->getContent();
                
                if (!empty($rawContent)) {
                    $data = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($data)) {
                        // Use replace to fully replace request data
                        $request->replace($data);
                    }
                }
            } catch (\JsonException $e) {
                \Log::warning('[EnsureJsonBodyIsParsed] JSON parsing error', [
                    'error' => $e->getMessage(),
                    'content_length' => strlen($rawContent ?? ''),
                ]);
            }
        }

        return $next($request);
    }
}


