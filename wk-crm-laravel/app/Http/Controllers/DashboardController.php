<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Estatísticas e relatórios do dashboard"
 * )
 */
class DashboardController extends Controller
{
    /**
     * Estatísticas gerais do CRM
     * 
     * @OA\Get(
     *     path="/api/dashboard/stats",
     *     tags={"Dashboard"},
     *     summary="Obter estatísticas gerais",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período (today, week, month, year, custom)",
     *         @OA\Schema(type="string", enum={"today", "week", "month", "year", "custom"})
     *     ),
     *     @OA\Parameter(name="start_date", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="end_date", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Estatísticas do dashboard",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_customers", type="integer"),
     *             @OA\Property(property="total_leads", type="integer"),
     *             @OA\Property(property="total_opportunities", type="integer"),
     *             @OA\Property(property="conversion_rate", type="number"),
     *             @OA\Property(property="total_revenue", type="number")
     *         )
     *     )
     * )
     */
    public function stats(Request $request): JsonResponse
    {
        $period = $this->getPeriodDates($request);

        // Totais gerais
        $totalCustomers = Customer::when($period, function($query) use ($period) {
            return $query->whereBetween('created_at', [$period['start'], $period['end']]);
        })->count();

        $totalLeads = Lead::when($period, function($query) use ($period) {
            return $query->whereBetween('created_at', [$period['start'], $period['end']]);
        })->count();

        $totalOpportunities = Opportunity::when($period, function($query) use ($period) {
            return $query->whereBetween('created_at', [$period['start'], $period['end']]);
        })->count();

        // Leads por status
        $leadsByStatus = Lead::when($period, function($query) use ($period) {
            return $query->whereBetween('created_at', [$period['start'], $period['end']]);
        })
        ->select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status');

        // Oportunidades por status
        $opportunitiesByStatus = Opportunity::when($period, function($query) use ($period) {
            return $query->whereBetween('created_at', [$period['start'], $period['end']]);
        })
        ->select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status');

        // Receita total (oportunidades ganhas)
        $totalRevenue = Opportunity::when($period, function($query) use ($period) {
            return $query->whereBetween('created_at', [$period['start'], $period['end']]);
        })
        ->where('status', 'won')
        ->sum('amount');

        // Taxa de conversão (leads convertidos / total leads)
        $convertedLeads = Lead::when($period, function($query) use ($period) {
            return $query->whereBetween('created_at', [$period['start'], $period['end']]);
        })->where('status', 'converted')->count();
        
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;

        // Win rate (oportunidades ganhas / total oportunidades)
        $wonOpportunities = $opportunitiesByStatus->get('won', 0);
        $winRate = $totalOpportunities > 0 ? round(($wonOpportunities / $totalOpportunities) * 100, 2) : 0;

        return response()->json([
            'period' => [
                'start' => $period ? $period['start']->toDateString() : null,
                'end' => $period ? $period['end']->toDateString() : null,
                'label' => $request->input('period', 'all_time')
            ],
            'totals' => [
                'customers' => $totalCustomers,
                'leads' => $totalLeads,
                'opportunities' => $totalOpportunities,
                'revenue' => (float) $totalRevenue
            ],
            'leads' => [
                'by_status' => $leadsByStatus,
                'converted' => $convertedLeads,
                'conversion_rate' => $conversionRate
            ],
            'opportunities' => [
                'by_status' => $opportunitiesByStatus,
                'won' => $wonOpportunities,
                'win_rate' => $winRate
            ]
        ]);
    }

    /**
     * Dados para gráficos do dashboard
     * 
     * @OA\Get(
     *     path="/api/dashboard/charts",
     *     tags={"Dashboard"},
     *     summary="Obter dados para gráficos",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Período (week, month, year)",
     *         @OA\Schema(type="string", enum={"week", "month", "year"})
     *     ),
     *     @OA\Response(response=200, description="Dados dos gráficos")
     * )
     */
    public function charts(Request $request): JsonResponse
    {
        $period = $request->input('period', 'month');
        
        $data = [
            'leads_timeline' => $this->getLeadsTimeline($period),
            'opportunities_timeline' => $this->getOpportunitiesTimeline($period),
            'revenue_timeline' => $this->getRevenueTimeline($period),
            'sales_funnel' => $this->getSalesFunnel(),
            'top_customers' => $this->getTopCustomers(5)
        ];

        return response()->json($data);
    }

    /**
     * Timeline de leads criados
     */
    private function getLeadsTimeline(string $period): array
    {
        $dateFormat = match($period) {
            'week' => 'YYYY-MM-DD',
            'month' => 'YYYY-MM-DD',
            'year' => 'YYYY-MM',
            default => 'YYYY-MM'
        };

        $daysBack = match($period) {
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 30
        };

        return Lead::where('created_at', '>=', Carbon::now()->subDays($daysBack))
            ->select(
                DB::raw("TO_CHAR(created_at, '{$dateFormat}') as date"),
                DB::raw('count(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'total' => (int) $item->total
            ])
            ->toArray();
    }

    /**
     * Timeline de oportunidades criadas
     */
    private function getOpportunitiesTimeline(string $period): array
    {
        $dateFormat = match($period) {
            'week' => 'YYYY-MM-DD',
            'month' => 'YYYY-MM-DD',
            'year' => 'YYYY-MM',
            default => 'YYYY-MM'
        };

        $daysBack = match($period) {
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 30
        };

        return Opportunity::where('created_at', '>=', Carbon::now()->subDays($daysBack))
            ->select(
                DB::raw("TO_CHAR(created_at, '{$dateFormat}') as date"),
                DB::raw('count(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'total' => (int) $item->total
            ])
            ->toArray();
    }

    /**
     * Timeline de receita
     */
    private function getRevenueTimeline(string $period): array
    {
        $dateFormat = match($period) {
            'week' => 'YYYY-MM-DD',
            'month' => 'YYYY-MM-DD',
            'year' => 'YYYY-MM',
            default => 'YYYY-MM'
        };

        $daysBack = match($period) {
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 30
        };

        return Opportunity::where('created_at', '>=', Carbon::now()->subDays($daysBack))
            ->where('status', 'won')
            ->select(
                DB::raw("TO_CHAR(created_at, '{$dateFormat}') as date"),
                DB::raw('sum(amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'revenue' => (float) $item->revenue
            ])
            ->toArray();
    }

    /**
     * Funil de vendas
     */
    private function getSalesFunnel(): array
    {
        $totalLeads = Lead::count();
        $qualifiedLeads = Lead::where('status', 'qualified')->count();
        $opportunities = Opportunity::whereIn('status', ['open', 'negotiation'])->count();
        $wonDeals = Opportunity::where('status', 'won')->count();

        return [
            [
                'stage' => 'Leads',
                'count' => $totalLeads,
                'percentage' => 100
            ],
            [
                'stage' => 'Qualificados',
                'count' => $qualifiedLeads,
                'percentage' => $totalLeads > 0 ? round(($qualifiedLeads / $totalLeads) * 100, 1) : 0
            ],
            [
                'stage' => 'Oportunidades',
                'count' => $opportunities,
                'percentage' => $totalLeads > 0 ? round(($opportunities / $totalLeads) * 100, 1) : 0
            ],
            [
                'stage' => 'Fechados',
                'count' => $wonDeals,
                'percentage' => $totalLeads > 0 ? round(($wonDeals / $totalLeads) * 100, 1) : 0
            ]
        ];
    }

    /**
     * Top clientes por receita
     */
    private function getTopCustomers(int $limit = 5): array
    {
        return Customer::select('customers.*')
            ->leftJoin('opportunities', 'customers.id', '=', 'opportunities.cliente_id')
            ->where('opportunities.status', 'won')
            ->selectRaw('customers.*, sum(opportunities.amount) as total_revenue')
            ->groupBy('customers.id')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(fn($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'total_revenue' => (float) $customer->total_revenue
            ])
            ->toArray();
    }

    /**
     * Calcula período de datas baseado no request
     */
    private function getPeriodDates(Request $request): ?array
    {
        $period = $request->input('period');

        if ($period === 'custom') {
            $start = Carbon::parse($request->input('start_date'));
            $end = Carbon::parse($request->input('end_date'));
            return ['start' => $start, 'end' => $end];
        }

        return match($period) {
            'today' => ['start' => Carbon::today(), 'end' => Carbon::now()],
            'week' => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()],
            'year' => ['start' => Carbon::now()->startOfYear(), 'end' => Carbon::now()],
            default => null
        };
    }
}
