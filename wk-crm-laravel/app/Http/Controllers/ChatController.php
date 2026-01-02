<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * POST /api/chat/ask
     * Send a question to the chatbot
     */
    public function ask(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string|min:3|max:500',
                'context' => 'sometimes|array'
            ]);

            Log::info('Chat request received', [
                'user_id' => auth()->id(),
                'question' => $validated['question']
            ]);

            $context = $validated['context'] ?? [];
            $context['user_id'] = auth()->id();
            $context['timestamp'] = now()->toIso8601String();

            $result = $this->chatbotService->askQuestion(
                $validated['question'],
                $context
            );

            return response()->json([
                'success' => true,
                'answer' => $result['answer'],
                'source' => $result['source'] ?? 'ai_service'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Chat error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pergunta'
            ], 500);
        }
    }

    /**
     * GET /api/chat/suggestions
     * Get suggested questions based on user context
     */
    public function suggestions(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            return response()->json([
                'success' => true,
                'suggestions' => [
                    'Qual é o risco desta oportunidade?',
                    'Qual é o meu ticket médio?',
                    'Quais são minhas melhores oportunidades?',
                    'Como é a distribuição do meu pipeline?',
                    'Qual é minha taxa de conversão?',
                    'Qual setor tem melhor performance?'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar sugestões'
            ], 500);
        }
    }

    /**
     * POST /api/opportunities/:id/insights
     * Generate AI insights for a specific opportunity
     */
    public function opportunityInsights(int $id): JsonResponse
    {
        try {
            // Verify user owns this opportunity
            $opportunity = \App\Models\Opportunity::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            Log::info('Generating insights for opportunity', [
                'opportunity_id' => $id,
                'user_id' => auth()->id()
            ]);

            $result = $this->chatbotService->generateOpportunityInsights($id);

            return response()->json($result);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oportunidade não encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error generating insights', [
                'opportunity_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar insights'
            ], 500);
        }
    }
}
