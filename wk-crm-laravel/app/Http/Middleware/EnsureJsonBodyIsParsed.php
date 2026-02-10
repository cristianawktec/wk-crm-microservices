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
                    'raw_content' => substr($rawContent, 0, 100),
                    'content_length' => strlen($rawContent),
                    'is_json' => $request->isJson(),
                ]);
                
                if (!empty($rawContent)) {
                    // Try to decode JSON without throwing on error
                    $data = @json_decode($rawContent, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        \Log::warning('[EnsureJsonBodyIsParsed] JSON decode error', [
                            'error' => json_last_error_msg(),
                            'raw_content' => substr($rawContent, 0, 200),
                        ]);
                    } else if (is_array($data)) {
                        // Log what we're replacing with
                        \Log::info('[EnsureJsonBodyIsParsed] Replacing request data', [
                            'parsed_data_keys' => array_keys($data),
                        ]);
                        
                        // Use replace to fully replace request data
                        $request->replace($data);
                        
                        // Verify replacement worked
                        \Log::info('[EnsureJsonBodyIsParsed] After replace', [
                            'request_all_keys' => array_keys($request->all()),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('[EnsureJsonBodyIsParsed] Exception', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }
}


