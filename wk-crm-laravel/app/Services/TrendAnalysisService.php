<?php

namespace App\Services;

use App\Models\Opportunity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrendAnalysisService
{
    /**
     * Get comprehensive trend analysis for opportunities
     */
    public function analyzeTrends(int $userId, array $filters = []): array
    {
        try {
            $period = $filters['period'] ?? 'year'; // year, quarter, month
            $startDate = $this->getStartDate($period);

            Log::info('Analyzing trends', [
                'user_id' => $userId,
                'period' => $period,
                'start_date' => $startDate
            ]);

            return [
                'success' => true,
                'data' => [
                    'conversion_trends' => $this->getConversionTrends($userId, $startDate),
                    'sector_analysis' => $this->getSectorAnalysis($userId, $startDate),
                    'product_performance' => $this->getProductPerformance($userId, $startDate),
                    'sales_forecast' => $this->getSalesForecast($userId, $startDate),
                    'best_periods' => $this->getBestPeriods($userId, $startDate),
                    'cycle_analysis' => $this->getSalesCycleAnalysis($userId, $startDate),
                    'summary' => $this->generateSummary($userId, $startDate)
                ],
                'period' => $period,
                'generated_at' => now()->toIso8601String()
            ];
        } catch (\Exception $e) {
            Log::error('Error in trend analysis', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao gerar análise de tendências'
            ];
        }
    }

    /**
     * Get conversion trends over time
     */
    private function getConversionTrends(int $userId, Carbon $startDate): array
    {
        $opportunities = Opportunity::where('customer_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->get(['id', 'status', 'value', 'created_at', 'updated_at']);

        $totalOps = $opportunities->count();
        if ($totalOps === 0) {
            return [
                'total' => 0,
                'won' => 0,
                'lost' => 0,
                'in_progress' => 0,
                'conversion_rate' => 0,
                'message' => 'Sem oportunidades no período'
            ];
        }

        $won = $opportunities->where('status', 'Ganha')->count();
        $lost = $opportunities->where('status', 'Perdida')->count();
        $inProgress = $opportunities->where('status', 'Em Progresso')->count();

        return [
            'total' => $totalOps,
            'won' => $won,
            'lost' => $lost,
            'in_progress' => $inProgress,
            'conversion_rate' => round(($won / $totalOps) * 100, 2),
            'message' => 'Análise de conversão do período'
        ];
    }

    /**
     * Analyze performance by sector
     */
    private function getSectorAnalysis(int $userId, Carbon $startDate): array
    {
        $sectors = DB::table('opportunities')
            ->where('customer_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('sector')
            ->groupBy('sector')
            ->select(
                'sector',
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(CASE WHEN status = \'Ganha\' THEN 1 END) as won'),
                DB::raw('SUM(value) as total_value'),
                DB::raw('AVG(value) as avg_value'),
                DB::raw('ROUND(COUNT(CASE WHEN status = \'Ganha\' THEN 1 END)::float / COUNT(*) * 100, 2) as conversion_rate')
            )
            ->orderByDesc('total_value')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'sectors' => $sectors,
            'top_sector' => !empty($sectors) ? $sectors[0]->sector : null,
            'total_sectors' => count($sectors)
        ];
    }

    /**
     * Analyze product/service performance
     */
    private function getProductPerformance(int $userId, Carbon $startDate): array
    {
        $products = DB::table('opportunities')
            ->where('customer_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->where('status', 'Ganha')
            ->groupBy('title')
            ->select(
                'title',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(value) as total_value')
            )
            ->orderByDesc('total_value')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'top_products' => $products,
            'total_types' => count($products),
            'message' => 'Produtos/serviços mais vendidos'
        ];
    }

    /**
     * Generate sales forecast based on historical data
     */
    private function getSalesForecast(int $userId, Carbon $startDate): array
    {
        $thirtyDaysFromNow = now()->addDays(30);
        
        $upcomingOpps = Opportunity::where('customer_id', $userId)
            ->where('status', '!=', 'Ganha')
            ->where('status', '!=', 'Perdida')
            ->where('expected_close_date', '<=', $thirtyDaysFromNow)
            ->where('expected_close_date', '>=', now())
            ->sum('value');

        $historicalAvg = Opportunity::where('customer_id', $userId)
            ->where('status', 'Ganha')
            ->where('created_at', '>=', $startDate)
            ->avg('value');

        $monthlyAvg = Opportunity::where('customer_id', $userId)
            ->where('status', 'Ganha')
            ->where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(function($item) {
                return $item->created_at->format('Y-m');
            })
            ->map(function($items) {
                return $items->sum('value');
            })
            ->avg();

        return [
            'next_30_days' => round($upcomingOpps, 2),
            'historical_avg_deal_size' => round($historicalAvg ?? 0, 2),
            'monthly_average' => round($monthlyAvg ?? 0, 2),
            'forecast_confidence' => $historicalAvg ? 'Alta' : 'Baixa',
            'message' => 'Previsão baseada em dados históricos'
        ];
    }

    /**
     * Identify best periods for sales
     */
    private function getBestPeriods(int $userId, Carbon $startDate): array
    {
        $monthlyData = Opportunity::where('customer_id', $userId)
            ->where('status', 'Ganha')
            ->where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(function($item) {
                return $item->created_at->format('m-Y');
            })
            ->map(function($items) {
                return [
                    'count' => $items->count(),
                    'value' => $items->sum('value'),
                    'avg' => $items->avg('value')
                ];
            })
            ->sortByDesc('value')
            ->take(5);

        return [
            'best_months' => $monthlyData->values()->toArray(),
            'pattern' => $this->identifySeasonalPattern($monthlyData),
            'recommendation' => 'Aproveite estes períodos para intensificar prospecção'
        ];
    }

    /**
     * Analyze sales cycle duration
     */
    private function getSalesCycleAnalysis(int $userId, Carbon $startDate): array
    {
        $wonDeals = Opportunity::where('customer_id', $userId)
            ->where('status', 'Ganha')
            ->where('created_at', '>=', $startDate)
            ->get();

        if ($wonDeals->isEmpty()) {
            return [
                'avg_cycle_days' => 0,
                'median_cycle_days' => 0,
                'fastest_deal_days' => 0,
                'slowest_deal_days' => 0,
                'message' => 'Sem deals ganhos para análise'
            ];
        }

        $cycles = $wonDeals->map(function($deal) {
            return $deal->updated_at->diffInDays($deal->created_at);
        });

        return [
            'avg_cycle_days' => round($cycles->avg(), 1),
            'median_cycle_days' => round($cycles->median(), 1),
            'fastest_deal_days' => $cycles->min(),
            'slowest_deal_days' => $cycles->max(),
            'message' => 'Análise do ciclo de vendas'
        ];
    }

    /**
     * Generate summary insights
     */
    private function generateSummary(int $userId, Carbon $startDate): string
    {
        $trends = $this->getConversionTrends($userId, $startDate);
        $sectors = $this->getSectorAnalysis($userId, $startDate);
        $forecast = $this->getSalesForecast($userId, $startDate);

        if ($trends['total'] === 0) {
            return 'Comece criando oportunidades para ver análises de tendência.';
        }

        $summary = "Em {$trends['total']} oportunidades, você teve {$trends['conversion_rate']}% de taxa de conversão. ";
        
        if (!empty($sectors['sectors'])) {
            $topSector = $sectors['sectors'][0];
            $summary .= "O setor {$topSector->sector} é seu melhor performer com {$topSector->conversion_rate}% de conversão. ";
        }
        
        if ($forecast['next_30_days'] > 0) {
            $summary .= "Você tem R$ " . number_format($forecast['next_30_days'], 0, ',', '.') . 
                       " em oportunidades para fechar nos próximos 30 dias.";
        }

        return $summary;
    }

    /**
     * Helper: Get start date based on period
     */
    private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subYear()
        };
    }

    /**
     * Helper: Identify seasonal patterns
     */
    private function identifySeasonalPattern(array $monthlyData): string
    {
        if (empty($monthlyData)) {
            return 'Dados insuficientes';
        }

        $avg = collect($monthlyData)->avg(function($item) {
            return $item['value'];
        });

        $highCount = collect($monthlyData)->filter(fn($item) => $item['value'] > $avg * 1.5)->count();

        if ($highCount >= 3) {
            return 'Padrão cíclico detectado';
        } elseif ($highCount >= 1) {
            return 'Picos sazonais moderados';
        }

        return 'Vendas consistentes ao longo do ano';
    }
}
