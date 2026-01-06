<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrendsController extends Controller
{
    /**
     * Analyze sales trends for the given period
     */
    public function analyze(Request $request): JsonResponse
    {
        try {
            $period = $request->query('period', 'year');
            $userId = auth()->id();

            // Calculate date range
            $now = Carbon::now();
            $startDate = match ($period) {
                'month' => $now->copy()->subMonth()->startOfMonth(),
                'quarter' => $now->copy()->subQuarter()->startOfQuarter(),
                'year' => $now->copy()->subYear()->startOfYear(),
                default => $now->copy()->subYear()->startOfYear(),
            };

            // Get user's opportunities (or all if admin)
            $query = Opportunity::query();
            if (auth()->check() && auth()->user()->email !== 'admin@consultoriawk.com') {
                $query->where('customer_id', $userId);
            }

            $opportunities = $query->whereBetween('created_at', [$startDate, $now])->get();

            // Conversion Trends
            $total = $opportunities->count();
            $won = $opportunities->where('status', 'won')->count();
            $lost = $opportunities->where('status', 'lost')->count();
            $conversionRate = $total > 0 ? round(($won / $total) * 100, 2) : 0;

            // Sector Analysis
            $sectorAnalysis = $this->analyzeSectors($opportunities);

            // Product Performance
            $productPerformance = $this->analyzeProducts($opportunities);

            // Sales Forecast
            $salesForecast = $this->analyzeForecast($opportunities);

            // Best Periods
            $bestPeriods = $this->analyzeBestPeriods($opportunities);

            // Cycle Analysis
            $cycleAnalysis = $this->analyzeCycle($opportunities);

            // Generate Summary
            $summary = $this->generateSummary($total, $won, $lost, $conversionRate, $period);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'conversion_trends' => [
                        'total' => $total,
                        'won' => $won,
                        'lost' => $lost,
                        'conversion_rate' => $conversionRate,
                    ],
                    'sector_analysis' => $sectorAnalysis,
                    'product_performance' => $productPerformance,
                    'sales_forecast' => $salesForecast,
                    'best_periods' => $bestPeriods,
                    'cycle_analysis' => $cycleAnalysis,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Trends analyze error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao analisar tendências: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function analyzeSectors($opportunities): array
    {
        $sectors = $opportunities->groupBy('sector')
            ->map(function ($group) {
                $total = $group->count();
                $won = $group->where('status', 'won')->count();
                $totalValue = $group->sum('value');
                $conversionRate = $total > 0 ? round(($won / $total) * 100, 2) : 0;

                return [
                    'sector' => $group->first()->sector ?? 'Não especificado',
                    'total' => $total,
                    'won' => $won,
                    'conversion_rate' => $conversionRate,
                    'total_value' => $totalValue,
                    'avg_value' => $total > 0 ? round($totalValue / $total, 2) : 0,
                ];
            })
            ->sortByDesc('total_value')
            ->values()
            ->toArray();

        return [
            'sectors' => $sectors,
            'total_sectors' => count($sectors),
        ];
    }

    private function analyzeProducts($opportunities): array
    {
        $products = $opportunities
            ->where('status', 'won')
            ->groupBy('title')
            ->map(function ($group) {
                return [
                    'title' => $group->first()->title ?? 'Sem título',
                    'count' => $group->count(),
                    'total_value' => $group->sum('value'),
                ];
            })
            ->sortByDesc('total_value')
            ->take(10)
            ->values()
            ->toArray();

        return [
            'top_products' => $products,
            'total_products' => count($products),
        ];
    }

    private function analyzeForecast($opportunities): array
    {
        $totalValue = $opportunities->sum('value');
        $avgValue = $opportunities->count() > 0 ? $totalValue / $opportunities->count() : 0;

        // Active opportunities (not won/lost)
        $activeOpportunities = $opportunities
            ->whereNotIn('status', ['won', 'lost'])
            ->sum('value');

        // Calculate monthly average
        $months = $opportunities->groupBy(function ($item) {
            return $item->created_at->format('Y-m');
        })->count();
        $monthlyAverage = $months > 0 ? round($totalValue / $months, 2) : 0;

        return [
            'historical_avg_deal_size' => round($avgValue, 2),
            'next_30_days' => round($activeOpportunities, 2),
            'monthly_average' => $monthlyAverage,
            'forecast_confidence' => $this->calculateConfidence($opportunities),
        ];
    }

    private function analyzeBestPeriods($opportunities): array
    {
        $bestMonths = $opportunities
            ->where('status', 'won')
            ->groupBy(function ($item) {
                return $item->closed_at?->format('m') ?? 'unknown';
            })
            ->map(function ($group, $month) {
                return [
                    'month' => $month,
                    'count' => $group->count(),
                    'value' => $group->sum('value'),
                ];
            })
            ->sortByDesc('value')
            ->take(3)
            ->values()
            ->toArray();

        // Identify pattern
        $pattern = 'Dados insuficientes para padrão';
        if (count($bestMonths) > 0) {
            $topMonth = $bestMonths[0]['month'];
            $pattern = "Melhor desempenho em mês " . $topMonth;
        }

        return [
            'best_months' => $bestMonths,
            'pattern' => $pattern,
        ];
    }

    private function analyzeCycle($opportunities): array
    {
        $closedDeals = $opportunities
            ->whereNotNull('closed_at')
            ->map(function ($opp) {
                $days = $opp->closed_at->diffInDays($opp->created_at);
                return max(0, $days);
            })
            ->toArray();

        if (empty($closedDeals)) {
            return [
                'avg_cycle_days' => 0,
                'fastest_deal_days' => 0,
                'slowest_deal_days' => 0,
                'median_cycle_days' => 0,
            ];
        }

        sort($closedDeals);
        $avgDays = round(array_sum($closedDeals) / count($closedDeals), 1);
        $medianDays = $closedDeals[floor((count($closedDeals) - 1) / 2)];

        return [
            'avg_cycle_days' => $avgDays,
            'fastest_deal_days' => min($closedDeals),
            'slowest_deal_days' => max($closedDeals),
            'median_cycle_days' => $medianDays,
        ];
    }

    private function calculateConfidence($opportunities): string
    {
        $count = $opportunities->count();
        $wonRate = $count > 0 ? ($opportunities->where('status', 'won')->count() / $count) : 0;

        if ($count >= 20 && $wonRate >= 0.5) {
            return 'Alta (90%)';
        } elseif ($count >= 10 && $wonRate >= 0.3) {
            return 'Média (70%)';
        } elseif ($count >= 5) {
            return 'Baixa (50%)';
        }

        return 'Muito Baixa (30%)';
    }

    private function generateSummary(int $total, int $won, int $lost, float $conversionRate, string $period): string
    {
        $periodLabel = match ($period) {
            'month' => 'neste mês',
            'quarter' => 'neste trimestre',
            'year' => 'neste ano',
            default => 'neste período',
        };

        if ($total === 0) {
            return "Sem oportunidades {$periodLabel}. Comece criando novas oportunidades para gerar insights.";
        }

        $trend = $conversionRate >= 50 ? 'excelente' : ($conversionRate >= 30 ? 'bom' : 'precisa melhorar');

        return "Você teve $total oportunidade(s) {$periodLabel}, ganhou $won deal(s) com taxa de conversão de {$conversionRate}% ({$trend}). "
            . ($lost > 0 ? "Perdeu $lost negócio(s)." : "");
    }
}
