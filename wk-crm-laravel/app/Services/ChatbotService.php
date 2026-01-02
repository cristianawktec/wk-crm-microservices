<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ChatbotService
{
    private string $aiServiceUrl;
    private ?string $geminiApiKey;

    public function __construct()
    {
        $this->aiServiceUrl = config('services.ai_service.url', 'http://localhost:8000');
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Send a question to the AI service and get an answer
     */
    public function askQuestion(string $question, array $context = []): array
    {
        try {
            Log::info('Chatbot question received', [
                'question' => $question,
                'context' => $context
            ]);

            // First, try to call the AI service
            $response = $this->callAiService($question, $context);

            return [
                'success' => true,
                'answer' => $response,
                'source' => 'ai_service'
            ];
        } catch (Exception $e) {
            Log::error('Chatbot error', [
                'error' => $e->getMessage(),
                'question' => $question
            ]);

            // Fallback response if AI service fails
            return [
                'success' => true,
                'answer' => $this->getFallbackResponse($question),
                'source' => 'fallback'
            ];
        }
    }

    /**
     * Call the Python FastAPI AI service
     */
    private function callAiService(string $question, array $context): string
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->aiServiceUrl}/api/v1/chat", [
                    'question' => $question,
                    'context' => $context,
                    'api_key' => $this->geminiApiKey
                ]);

            if ($response->successful()) {
                return $response->json('answer') ?? 'Desculpe, não consegui gerar uma resposta.';
            }

            throw new Exception('AI Service returned status: ' . $response->status());
        } catch (Exception $e) {
            Log::warning('AI Service unavailable, using fallback', [
                'error' => $e->getMessage(),
                'url' => $this->aiServiceUrl
            ]);

            throw $e;
        }
    }

    /**
     * Provide intelligent fallback responses based on the question
     */
    private function getFallbackResponse(string $question): string
    {
        $question = strtolower($question);

        // Risk-related questions
        if (str_contains($question, 'risco')) {
            return 'Análise de risco: Para melhor análise, conecte a API do Google Gemini. ' .
                   'Enquanto isso, considere fatores como: prazo de fechamento, valor em risco e histórico do cliente. ' .
                   'Oportunidades com prazo < 30 dias e valor alto tendem a ter maior risco.';
        }

        // Ticket average questions
        if (str_contains($question, 'ticket') || str_contains($question, 'média')) {
            return 'Ticket médio: Este é um KPI importante que varia por setor. ' .
                   'Verifique o Dashboard de Analytics para ver seu ticket médio e comparar com tendências. ' .
                   'Você pode filtrar por período para análises mais detalhadas.';
        }

        // Opportunity questions
        if (str_contains($question, 'oportunid')) {
            return 'Oportunidades: Você tem acesso à lista completa no dashboard. ' .
                   'Cada oportunidade tem análise IA com: ' .
                   '• Risco de fechamento ' .
                   '• Próximo passo recomendado ' .
                   '• Tempo estimado até fechamento. ' .
                   'Use os filtros para segmentar por status, valor ou período.';
        }

        // Pipeline questions
        if (str_contains($question, 'pipeline') || str_contains($question, 'distribuição')) {
            return 'Distribuição do Pipeline: ' .
                   '• Aberta: Oportunidades em prospecção ' .
                   '• Em Progresso: Negociações ativas ' .
                   '• Ganha: Negócios fechados ' .
                   '• Perdida: Oportunidades não convertidas. ' .
                   'O gráfico de funil no Dashboard mostra a proporção de cada etapa.';
        }

        // General help
        return 'Como posso ajudar? Você pode me fazer perguntas sobre:' .
               '\n• Análise de risco de oportunidades' .
               '\n• Seu ticket médio e pipeline' .
               '\n• Tendências de vendas' .
               '\n• Próximos passos recomendados' .
               '\n• Padrões de sucesso. ' .
               '\nExperimente perguntar sobre qualquer aspecto do seu negócio!';
    }

    /**
     * Generate insights for an opportunity
     */
    public function generateOpportunityInsights(int $opportunityId): array
    {
        try {
            $opportunity = \App\Models\Opportunity::findOrFail($opportunityId);

            $prompt = "Analise esta oportunidade de venda:\n" .
                     "- Título: {$opportunity->title}\n" .
                     "- Valor: R$ " . number_format($opportunity->value, 2, ',', '.') . "\n" .
                     "- Status: {$opportunity->status}\n" .
                     "- Probabilidade: {$opportunity->probability}%\n" .
                     "- Prazo: " . ($opportunity->expected_close_date ? $opportunity->expected_close_date->format('d/m/Y') : 'não definido') . "\n" .
                     "Forneça: score de risco (0-1), próximo passo recomendado, e uma recomendação curta.";

            return $this->askQuestion($prompt, ['opportunity_id' => $opportunityId]);
        } catch (Exception $e) {
            Log::error('Error generating opportunity insights', [
                'opportunity_id' => $opportunityId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao gerar insights'
            ];
        }
    }
}
