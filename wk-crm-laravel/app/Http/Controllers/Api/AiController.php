<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Models\AiAnalysis;
use App\Services\AiService;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    /**
     * FastAPI service URL
     * Use host.docker.internal for Docker environments
     */
    private $aiServiceUrl;

    public function __construct()
    {
        // Docker environments need host.docker.internal, local dev uses localhost
        $this->aiServiceUrl = env('AI_SERVICE_URL', 'http://host.docker.internal:8080');
    }

    /**
     * Analyze opportunity with AI
     * POST /api/opportunities/{id}/ai-analysis
     */
    public function analyzeOpportunity(Request $request, $opportunityId)
    {
        try {
            \Log::info('[AiController@analyzeOpportunity] START', [
                'opportunity_id' => $opportunityId,
                'user_id' => optional($request->user())->id
            ]);

            // Buscar oportunidade
            $opportunity = Opportunity::with(['client', 'seller'])->findOrFail($opportunityId);

            // Validar que o usuário tem permissão (é admin, manager ou é o seller)
            $user = $request->user();
            if ($user->cannot('view', $opportunity)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Preparar dados para análise
            $analysisData = [
                'title' => $opportunity->title,
                'value' => $opportunity->value ?? 0,
                'probability' => $opportunity->probability ?? 50,
                'sector' => $opportunity->sector ?? 'General',
                'status' => $opportunity->status ?? 'open',
                'description' => $opportunity->description ?? $opportunity->title,
                'client' => $opportunity->client?->name ?? 'Unknown',
                'seller' => $opportunity->seller?->name ?? 'Unknown',
            ];

            // Chamar FastAPI para análise
            $analysis = $this->callAiService('analyze', $analysisData);

            if (!$analysis) {
                return response()->json([
                    'error' => 'AI Service unavailable',
                    'message' => 'Serviço de IA não respondeu'
                ], 503);
            }

            // Salvar análise no banco
            $aiAnalysis = AiAnalysis::create([
                'opportunity_id' => $opportunityId,
                'user_id' => $user->id,
                'analysis_type' => 'risk_assessment',
                'prompt' => json_encode($analysisData),
                'response' => json_encode($analysis),
                'model' => 'gemini-pro',
                'tokens_used' => $analysis['tokens_used'] ?? null,
                'processing_time_ms' => $analysis['processing_time_ms'] ?? null,
            ]);

            \Log::info('[AiController@analyzeOpportunity] Analysis saved', [
                'analysis_id' => $aiAnalysis->id,
                'opportunity_id' => $opportunityId
            ]);

            // Retornar resultado formatado
            return response()->json([
                'status' => 'success',
                'analysis_id' => $aiAnalysis->id,
                'opportunity_id' => $opportunityId,
                'analysis' => $analysis,
                'created_at' => $aiAnalysis->created_at,
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Opportunity not found'], 404);
        } catch (\Exception $e) {
            \Log::error('[AiController@analyzeOpportunity] ERROR', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'error' => 'Analysis failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get previous analyses for an opportunity
     * GET /api/opportunities/{id}/ai-analysis
     */
    public function getAnalyses(Request $request, $opportunityId)
    {
        try {
            $opportunity = Opportunity::findOrFail($opportunityId);

            // Validar permissão
            if ($request->user()->cannot('view', $opportunity)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Buscar análises
            $analyses = AiAnalysis::where('opportunity_id', $opportunityId)
                ->orderByDesc('created_at')
                ->with('user')
                ->get();

            return response()->json([
                'status' => 'success',
                'opportunity_id' => $opportunityId,
                'analyses_count' => $analyses->count(),
                'analyses' => $analyses,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Opportunity not found'], 404);
        } catch (\Exception $e) {
            \Log::error('[AiController@getAnalyses] ERROR', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch analyses'], 500);
        }
    }

    /**
     * Chat with AI
     * POST /api/ai/chat
     */
    public function chat(Request $request)
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string|max:1000',
                'context' => 'nullable|string',
            ]);

            \Log::info('[AiController@chat] START', [
                'user_id' => optional($request->user())->id,
                'question_length' => strlen($validated['question'])
            ]);

            // Chamar FastAPI
            $response = $this->callAiService('chat', [
                'question' => $validated['question'],
                'context' => $validated['context'] ?? '',
            ]);

            if (!$response) {
                return response()->json([
                    'error' => 'AI Service unavailable'
                ], 503);
            }

            return response()->json([
                'status' => 'success',
                'question' => $validated['question'],
                'answer' => $response['answer'] ?? $response,
                'processing_time_ms' => $response['processing_time_ms'] ?? null,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('[AiController@chat] ERROR', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Chat failed'], 500);
        }
    }

    /**
     * Check AI service health
     * GET /api/ai/health
     */
    public function health()
    {
        try {
            $client = new Client();
            $response = $client->get("{$this->aiServiceUrl}/health", [
                'connect_timeout' => 2,
                'timeout' => 5,
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json([
                'status' => 'ok',
                'ai_service' => $data,
                'laravel_service' => 'ok',
            ]);

        } catch (\Exception $e) {
            \Log::warning('[AiController@health] AI Service unreachable', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'warning',
                'ai_service' => 'unreachable',
                'laravel_service' => 'ok',
                'error' => 'Cannot reach AI service at ' . $this->aiServiceUrl,
            ], 503);
        }
    }

    /**
     * Call FastAPI service
     */
    private function callAiService($endpoint, $data)
    {
        try {
            $client = new Client();
            $url = "{$this->aiServiceUrl}/{$endpoint}";

            \Log::info('[AiController] Calling AI service', [
                'endpoint' => $endpoint,
                'url' => $url,
            ]);

            $response = $client->post($url, [
                'json' => $data,
                'connect_timeout' => 5,
                'timeout' => 30,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $result = json_decode($response->getBody(), true);

            \Log::info('[AiController] AI service response', [
                'endpoint' => $endpoint,
                'status_code' => $response->getStatusCode(),
                'response_keys' => array_keys($result),
            ]);

            return $result;

        } catch (RequestException $e) {
            \Log::error('[AiController] AI service request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'response' => $e->getResponse()?->getBody()?->getContents()
            ]);

            return null;
        } catch (\Exception $e) {
            \Log::error('[AiController] Unexpected error calling AI service', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate AI insights for opportunity (ORIGINAL METHOD - DO NOT REMOVE)
     * POST /api/ai/opportunity-insights
     * Used by Vue Customer App
     */
    public function opportunityInsights(Request $request, AiService $aiService)
    {
        $data = $request->validate([
            'id' => ['nullable', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:100'],
        ]);

        $insight = $aiService->analyzeOpportunity($data);

        return response()->json([
            'success' => true,
            'data' => $insight,
        ]);
    }
}
