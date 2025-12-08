<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @group Reports
 * Endpoints para geração de relatórios com suporte a filtros e exportação
 */
class ReportController extends Controller
{
    /**
     * Get sales report
     *
     * Retorna sumário de vendas (opportunities) com filtros e opção de exportação
     *
     * @authenticated
     * @queryParam date_from string Data inicial (YYYY-MM-DD). Opcional
     * @queryParam date_to string Data final (YYYY-MM-DD). Opcional
     * @queryParam seller_id string ID do seller. Opcional (admin pode filtrar, seller vê apenas seus dados)
     * @queryParam status string Status: pending, won, lost. Opcional
     * @queryParam format string Formato: json (default), csv, pdf. Opcional
     * @queryParam group_by string Agrupar por: seller, status, period (default: seller). Opcional
     *
     * @response 200 {
     *   "success": true,
     *   "report": "sales",
     *   "period": {
     *     "from": "2025-01-01",
     *     "to": "2025-12-31"
     *   },
     *   "summary": {
     *     "total_opportunities": 50,
     *     "total_value": 500000.00,
     *     "won_count": 15,
     *     "won_value": 250000.00,
     *     "conversion_rate": 30,
     *     "average_value": 10000.00
     *   },
     *   "data": [
     *     {
     *       "seller": "John Doe",
     *       "opportunities": 5,
     *       "value": 50000,
     *       "won": 2,
     *       "lost": 1,
     *       "pending": 2
     *     }
     *   ]
     * }
     */
    public function salesReport(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $user = $request->user();
            $dateFrom = $request->query('date_from', now()->subMonths(12)->format('Y-m-d'));
            $dateTo = $request->query('date_to', now()->format('Y-m-d'));
            $sellerId = $request->query('seller_id');
            $status = $request->query('status');
            $format = $request->query('format', 'json');
            $groupBy = $request->query('group_by', 'seller');

            // Build query
            $query = Opportunity::whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay(),
            ]);

            // Apply user filter (seller can only see their opportunities)
            if (!$user->hasRole('admin') && $user->hasRole('seller')) {
                $query->where('seller_id', $user->id);
            } elseif ($sellerId && $user->hasRole('admin')) {
                $query->where('seller_id', $sellerId);
            }

            // Apply status filter
            if ($status && in_array($status, ['pending', 'won', 'lost'])) {
                $query->where('status', $status);
            }

            // Calculate summary
            $summary = [
                'total_opportunities' => $query->count(),
                'total_value' => (float) ($query->sum('value') ?? 0),
                'won_count' => (clone $query)->where('status', 'won')->count(),
                'won_value' => (float) ((clone $query)->where('status', 'won')->sum('value') ?? 0),
                'conversion_rate' => $query->count() > 0 ? round(((clone $query)->where('status', 'won')->count() / $query->count()) * 100, 2) : 0,
                'average_value' => $query->count() > 0 ? round((float) ($query->avg('value') ?? 0), 2) : 0,
            ];

            // Group data
            if ($groupBy === 'seller') {
                $data = $this->groupSalesBysSeller($query);
            } elseif ($groupBy === 'status') {
                $data = $this->groupSalesByStatus($query);
            } else {
                $data = $this->groupSalesByPeriod($query);
            }

            // Handle export
            if ($format === 'csv') {
                return $this->exportSalesReportCSV($summary, $data, $dateFrom, $dateTo);
            } elseif ($format === 'pdf') {
                return $this->exportSalesReportPDF($summary, $data, $dateFrom, $dateTo);
            }

            // Return JSON
            return response()->json([
                'success' => true,
                'report' => 'sales',
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
                'summary' => $summary,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating sales report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get leads report
     *
     * Retorna sumário de leads com filtros e opção de exportação
     *
     * @authenticated
     * @queryParam date_from string Data inicial (YYYY-MM-DD). Opcional
     * @queryParam date_to string Data final (YYYY-MM-DD). Opcional
     * @queryParam seller_id string ID do seller. Opcional
     * @queryParam source string Fonte do lead. Opcional
     * @queryParam format string Formato: json (default), csv, pdf. Opcional
     * @queryParam group_by string Agrupar por: seller, source, status (default: source). Opcional
     *
     * @response 200 {
     *   "success": true,
     *   "report": "leads",
     *   "summary": {
     *     "total_leads": 150,
     *     "converted": 30,
     *     "conversion_rate": 20,
     *     "average_days_to_convert": 15.5
     *   },
     *   "data": [
     *     {
     *       "source": "Website",
     *       "count": 50,
     *       "converted": 10,
     *       "conversion_rate": 20
     *     }
     *   ]
     * }
     */
    public function leadsReport(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $user = $request->user();
            $dateFrom = $request->query('date_from', now()->subMonths(12)->format('Y-m-d'));
            $dateTo = $request->query('date_to', now()->format('Y-m-d'));
            $sellerId = $request->query('seller_id');
            $source = $request->query('source');
            $format = $request->query('format', 'json');
            $groupBy = $request->query('group_by', 'source');

            // Build query
            $query = Lead::whereBetween('created_at', [
                Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay(),
            ]);

            // Apply user filter
            if (!$user->hasRole('admin') && $user->hasRole('seller')) {
                $query->where('seller_id', $user->id);
            } elseif ($sellerId && $user->hasRole('admin')) {
                $query->where('seller_id', $sellerId);
            }

            // Apply source filter
            if ($source) {
                $query->where('source', $source);
            }

            // Calculate summary
            $totalLeads = $query->count();
            $convertedLeads = (clone $query)->where('status', 'converted')->count();

            $summary = [
                'total_leads' => $totalLeads,
                'converted' => $convertedLeads,
                'conversion_rate' => $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0,
                'average_days_to_convert' => $this->calculateAverageDaysToConvert($query),
            ];

            // Group data
            if ($groupBy === 'seller') {
                $data = $this->groupLeadsBySeller($query);
            } elseif ($groupBy === 'status') {
                $data = $this->groupLeadsByStatus($query);
            } else {
                $data = $this->groupLeadsBySource($query);
            }

            // Handle export
            if ($format === 'csv') {
                return $this->exportLeadsReportCSV($summary, $data, $dateFrom, $dateTo);
            } elseif ($format === 'pdf') {
                return $this->exportLeadsReportPDF($summary, $data, $dateFrom, $dateTo);
            }

            // Return JSON
            return response()->json([
                'success' => true,
                'report' => 'leads',
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
                'summary' => $summary,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating leads report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Group sales by seller
     */
    private function groupSalesBysSeller($query)
    {
        return $query
            ->select('seller_id', DB::raw('COUNT(*) as opportunities'), DB::raw('SUM(value) as value'))
            ->addSelect(DB::raw("COUNT(CASE WHEN status = 'won' THEN 1 END) as won"))
            ->addSelect(DB::raw("COUNT(CASE WHEN status = 'lost' THEN 1 END) as lost"))
            ->addSelect(DB::raw("COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending"))
            ->groupBy('seller_id')
            ->with('seller:id,name')
            ->get()
            ->map(fn($item) => [
                'seller' => $item->seller?->name ?? 'Unknown',
                'opportunities' => $item->opportunities,
                'value' => (float) ($item->value ?? 0),
                'won' => $item->won,
                'lost' => $item->lost,
                'pending' => $item->pending,
            ])
            ->values();
    }

    /**
     * Group sales by status
     */
    private function groupSalesByStatus($query)
    {
        return $query
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(value) as total_value'))
            ->groupBy('status')
            ->get()
            ->map(fn($item) => [
                'status' => $item->status,
                'count' => $item->count,
                'total_value' => (float) ($item->total_value ?? 0),
            ])
            ->values();
    }

    /**
     * Group sales by period (monthly)
     */
    private function groupSalesByPeriod($query)
    {
        return $query
            ->select(DB::raw("DATE_TRUNC('month', created_at) as month"), DB::raw('COUNT(*) as count'), DB::raw('SUM(value) as total_value'))
            ->groupBy(DB::raw("DATE_TRUNC('month', created_at)"))
            ->orderBy(DB::raw("DATE_TRUNC('month', created_at)"))
            ->get()
            ->map(fn($item) => [
                'period' => $item->month,
                'count' => $item->count,
                'total_value' => (float) ($item->total_value ?? 0),
            ])
            ->values();
    }

    /**
     * Group leads by source
     */
    private function groupLeadsBySource($query)
    {
        $total = $query->count();

        return $query
            ->select('source', DB::raw('COUNT(*) as count'))
            ->addSelect(DB::raw("COUNT(CASE WHEN status = 'converted' THEN 1 END) as converted"))
            ->groupBy('source')
            ->get()
            ->map(fn($item) => [
                'source' => $item->source,
                'count' => $item->count,
                'converted' => $item->converted,
                'conversion_rate' => $total > 0 ? round(($item->converted / $item->count) * 100, 2) : 0,
            ])
            ->values();
    }

    /**
     * Group leads by seller
     */
    private function groupLeadsBySeller($query)
    {
        return $query
            ->select('seller_id', DB::raw('COUNT(*) as count'))
            ->addSelect(DB::raw("COUNT(CASE WHEN status = 'converted' THEN 1 END) as converted"))
            ->groupBy('seller_id')
            ->with('seller:id,name')
            ->get()
            ->map(fn($item) => [
                'seller' => $item->seller?->name ?? 'Unknown',
                'count' => $item->count,
                'converted' => $item->converted,
                'conversion_rate' => $item->count > 0 ? round(($item->converted / $item->count) * 100, 2) : 0,
            ])
            ->values();
    }

    /**
     * Group leads by status
     */
    private function groupLeadsByStatus($query)
    {
        return $query
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn($item) => [
                'status' => $item->status,
                'count' => $item->count,
            ])
            ->values();
    }

    /**
     * Calculate average days to convert
     */
    private function calculateAverageDaysToConvert($query)
    {
        $convertedLeads = (clone $query)
            ->where('status', 'converted')
            ->get(['created_at', 'updated_at']);

        if ($convertedLeads->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        foreach ($convertedLeads as $lead) {
            $days = $lead->updated_at->diffInDays($lead->created_at);
            $totalDays += $days;
        }

        return round($totalDays / $convertedLeads->count(), 2);
    }

    /**
     * Export sales report as CSV
     */
    private function exportSalesReportCSV($summary, $data, $dateFrom, $dateTo)
    {
        $filename = "sales_report_{$dateFrom}_{$dateTo}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($summary, $data) {
            $file = fopen('php://output', 'w');
            
            // Write summary
            fputcsv($file, ['SALES REPORT SUMMARY']);
            fputcsv($file, ['Total Opportunities', $summary['total_opportunities']]);
            fputcsv($file, ['Total Value', $summary['total_value']]);
            fputcsv($file, ['Won Count', $summary['won_count']]);
            fputcsv($file, ['Conversion Rate (%)', $summary['conversion_rate']]);
            fputcsv($file, ['Average Value', $summary['average_value']]);
            fputcsv($file, []); // Empty line

            // Write data
            fputcsv($file, array_keys((array) $data[0]));
            foreach ($data as $row) {
                fputcsv($file, (array) $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sales report as PDF (placeholder - requires barryvdh/laravel-dompdf)
     */
    private function exportSalesReportPDF($summary, $data, $dateFrom, $dateTo)
    {
        // TODO: Implement PDF export using barryvdh/laravel-dompdf
        return response()->json([
            'success' => false,
            'message' => 'PDF export not yet implemented. Please install barryvdh/laravel-dompdf',
        ], 501);
    }

    /**
     * Export leads report as CSV
     */
    private function exportLeadsReportCSV($summary, $data, $dateFrom, $dateTo)
    {
        $filename = "leads_report_{$dateFrom}_{$dateTo}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($summary, $data) {
            $file = fopen('php://output', 'w');
            
            // Write summary
            fputcsv($file, ['LEADS REPORT SUMMARY']);
            fputcsv($file, ['Total Leads', $summary['total_leads']]);
            fputcsv($file, ['Converted', $summary['converted']]);
            fputcsv($file, ['Conversion Rate (%)', $summary['conversion_rate']]);
            fputcsv($file, ['Average Days to Convert', $summary['average_days_to_convert']]);
            fputcsv($file, []); // Empty line

            // Write data
            fputcsv($file, array_keys((array) $data[0]));
            foreach ($data as $row) {
                fputcsv($file, (array) $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export leads report as PDF (placeholder)
     */
    private function exportLeadsReportPDF($summary, $data, $dateFrom, $dateTo)
    {
        // TODO: Implement PDF export using barryvdh/laravel-dompdf
        return response()->json([
            'success' => false,
            'message' => 'PDF export not yet implemented. Please install barryvdh/laravel-dompdf',
        ], 501);
    }
}
