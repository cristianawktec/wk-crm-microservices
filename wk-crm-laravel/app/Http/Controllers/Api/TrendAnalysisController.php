<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrendAnalysisController extends Controller
{
    public function analyze(Request $request)
    {
        $period = $request->get('period', 'year');
        
        // Dados reais do banco
        $opportunities = DB::table('opportunities')
            ->select(
                DB::raw("DATE_TRUNC('month', created_at) as month"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(value) as total_value'),
                DB::raw('AVG(value) as avg_value')
            )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy(DB::raw("DATE_TRUNC('month', created_at)"))
            ->orderBy('month', 'desc')
            ->get();

        $statusDistribution = DB::table('opportunities')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $topSellers = DB::table('opportunities')
            ->join('users', 'opportunities.seller_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as count'), DB::raw('SUM(opportunities.value) as total_value'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_value')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'monthly_trends' => $opportunities->map(fn($item) => [
                    'month' => date('Y-m', strtotime($item->month)),
                    'count' => (int) $item->count,
                    'total' => (float) $item->total_value,
                    'average' => (float) $item->avg_value
                ]),
                'status_distribution' => $statusDistribution->map(fn($item) => [
                    'status' => $item->status,
                    'count' => (int) $item->count,
                    'percentage' => 0 // calculado no frontend
                ]),
                'top_sellers' => $topSellers->map(fn($item) => [
                    'name' => $item->name,
                    'count' => (int) $item->count,
                    'total' => (float) $item->total_value
                ]),
                'insights' => [
                    'total_opportunities' => DB::table('opportunities')->count(),
                    'total_value' => DB::table('opportunities')->sum('value'),
                    'avg_ticket' => DB::table('opportunities')->avg('value'),
                    'conversion_rate' => $this->calculateConversionRate()
                ]
            ]
        ]);
    }

    private function calculateConversionRate(): float
    {
        $total = DB::table('opportunities')->count();
        $won = DB::table('opportunities')->where('status', 'won')->count();
        
        return $total > 0 ? round(($won / $total) * 100, 2) : 0;
    }
}
