<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('AI_SERVICE_URL', 'http://localhost:8000'), '/');
    }

    public function analyzeOpportunity(array $payload): array
    {
        $url = $this->baseUrl . '/ai/opportunity-insights';

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return is_array($data)
                    ? $data
                    : $this->fallback($payload, 'Invalid response format');
            }

            return $this->fallback($payload, 'HTTP ' . $response->status());
        } catch (\Throwable $e) {
            return $this->fallback($payload, $e->getMessage());
        }
    }

    private function fallback(array $payload, string $reason = 'fallback'): array
    {
        return [
            'risk_score' => 0.4,
            'risk_label' => 'medium',
            'next_action' => 'Reengajar o cliente',
            'recommendation' => 'Configurar serviço IA ou tente novamente.',
            'summary' => 'Fallback IA: ' . $reason,
            'model' => 'stub',
            'cached' => true,
        ];
    }
}
