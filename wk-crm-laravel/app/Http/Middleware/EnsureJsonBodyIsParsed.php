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
                
                \Log::info('[EnsureJsonBodyIsParsed] Processing JSON', [
                    'raw_content' => $rawContent,
                    'content_length' => strlen($rawContent),
                    'is_json' => $request->isJson(),
                ]);
                
                if (!empty($rawContent)) {
                    $data = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($data)) {
                        // Log what we're replacing with
                        \Log::info('[EnsureJsonBodyIsParsed] Replacing request data', [
                            'parsed_data' => $data,
                        ]);
                        
                        // Use replace to fully replace request data
                        $request->replace($data);
                        
                        // Verify replacement worked
                        \Log::info('[EnsureJsonBodyIsParsed] After replace', [
                            'request_all' => $request->all(),
                        ]);
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


