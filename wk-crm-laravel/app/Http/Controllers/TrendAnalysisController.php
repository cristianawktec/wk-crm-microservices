<?php

namespace App\Http\Controllers;

use App\Services\TrendAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TrendAnalysisController extends Controller
{
    protected TrendAnalysisService $trendService;

    public function __construct(TrendAnalysisService $trendService)
    {
        $this->trendService = $trendService;
    }

    /**
     * GET /api/trends/analyze
     * Get comprehensive trend analysis
     */
    public function analyze(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'period' => 'sometimes|in:month,quarter,year'
            ]);

            Log::info('Trend analysis requested', [
                'user_id' => auth()->id(),
                'period' => $validated['period'] ?? 'year'
            ]);

            $result = $this->trendService->analyzeTrends(
                auth()->id(),
                $validated
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error analyzing trends', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar análise'
            ], 500);
        }
    }

    /**
     * GET /api/trends/conversion
     * Get conversion rate details
     */
    public function conversion(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'year');

            // Get opportunities data
            $opportunities = \App\Models\Opportunity::where('customer_id', auth()->id());

            if ($period === 'month') {
                $opportunities = $opportunities->where('created_at', '>=', now()->subMonth());
            } elseif ($period === 'quarter') {
                $opportunities = $opportunities->where('created_at', '>=', now()->subQuarter());
            } else {
                $opportunities = $opportunities->where('created_at', '>=', now()->subYear());
            }

            $total = $opportunities->count();
            $won = $opportunities->where('status', 'Ganha')->count();

            return response()->json([
                'success' => true,
                'conversion_rate' => $total > 0 ? round(($won / $total) * 100, 2) : 0,
                'total_opportunities' => $total,
                'won_opportunities' => $won,
                'period' => $period
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar taxa de conversão'
            ], 500);
        }
    }

    /**
     * GET /api/trends/monthly-revenue
     * Get monthly revenue data for charts
     */
    public function monthlyRevenue(Request $request): JsonResponse
    {
        try {
            $months = $request->query('months', 12);
            $months = min($months, 24); // Max 24 months

            $data = \DB::table('opportunities')
                ->where('customer_id', auth()->id())
                ->where('status', 'Ganha')
                ->where('updated_at', '>=', now()->subMonths($months))
                ->selectRaw("
                    TO_CHAR(updated_at, 'YYYY-MM') as month,
                    COUNT(*) as count,
                    SUM(value) as revenue
                ")
                ->groupByRaw("TO_CHAR(updated_at, 'YYYY-MM')")
                ->orderBy('month')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data,
                'period_months' => $months
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados de receita'
            ], 500);
        }
    }
}
