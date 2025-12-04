<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Customer;
use App\Domain\Lead\Lead;
use App\Domain\Opportunity\Opportunity;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Get(
 *     path="/api/dashboard",
 *     summary="Retorna os dados do dashboard",
 *     operationId="dashboard_index",
 *     tags={"Dashboard"},
 *     @OA\Response(
 *         response=200,
 *         description="Dados do dashboard retornados com sucesso"
 *     )
 * )
 */
class DashboardController extends Controller
{
    // ...existing code...
    public function index(Request $request): JsonResponse
    {
        // Mocked dashboard response for local development and frontend integration tests
        $filters = [
            'periodo' => (int) $request->get('periodo', 30),
            'vendedor' => $request->get('vendedor', 'all'),
            'status' => $request->get('status', 'all')
        ];

        // Use optimized aggregate queries
        $resumo = $this->obterResumo($filters);
        $metricas = $this->obterMetricas($filters);
        $atividade = $this->obterAtividadeRecente($filters);
        $graficos = $this->obterDadosGraficos($filters);

        $data = [
            'resumo' => $resumo,
            'metricas' => $metricas,
            'atividade_recente' => $atividade,
            'dados_graficos' => $graficos,
            'performance_vendedores' => $this->gerarPerformanceVendedores($filters),
            'fontes_leads' => $this->gerarFontesLeads($filters),
            'filtros_aplicados' => $filters,
            'sistema' => [
                'nome' => 'WK CRM Laravel',
                'versao' => 'dev',
                'ultimo_update' => Carbon::now()->format('d/m/Y H:i:s')
            ]
        ];

        return response()->json($data);
    }

    private function obterResumo(array $filters = []): array
    {
        // Dados reais do banco (otimizados)
        $total_clientes = Customer::count();
        $novos_clientes_mes = Customer::where('created_at', '>=', now()->subMonth())->count();
        $leads_ativos = Lead::where('status', 'new')->count();

        $vendasAgg = Opportunity::selectRaw('COUNT(*) as cnt, COALESCE(SUM(value),0) as total')
            ->where('created_at', '>=', now()->subMonth())
            ->first();

        $vendas_mes = $vendasAgg->cnt ?? 0;
        $receita_mes = $vendasAgg->total ?? 0;
        $meta_mes = 250000; // valor fixo ou buscar de config
        $percentual_meta = $meta_mes > 0 ? round(($receita_mes / $meta_mes) * 100) : 0;

        return [
            'total_clientes' => $total_clientes,
            'novos_clientes_mes' => $novos_clientes_mes,
            'leads_ativos' => $leads_ativos,
            'vendas_mes' => $vendas_mes,
            'receita_mes' => 'R$ ' . number_format($receita_mes, 2, ',', '.'),
            'meta_mes' => 'R$ ' . number_format($meta_mes, 2, ',', '.'),
            'percentual_meta' => $percentual_meta
        ];
    }

    private function obterMetricas(array $filters = []): array
    {
        $total_leads = Lead::count();
        $convertidos = Lead::where('status', 'converted')->count();
        $em_andamento = Lead::where('status', 'new')->count();
        $taxa_conversao = $total_leads > 0 ? round(($convertidos / $total_leads) * 100, 2) : 0;

        $hoje = Opportunity::whereDate('created_at', now()->toDateString())->count();
        $semana = Opportunity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $mes = Opportunity::whereMonth('created_at', now()->month)->count();
        $trimestre = Opportunity::whereBetween('created_at', [now()->subMonths(3), now()])->count();

        $valor_ticket_medio = $mes > 0 ? Opportunity::whereMonth('created_at', now()->month)->sum('value') / $mes : 0;
        $variacao_ticket = '+0%'; // Implementar cálculo de variação se necessário

        return [
            'conversao_leads' => [
                'total_leads' => $total_leads,
                'convertidos' => $convertidos,
                'taxa_conversao' => $taxa_conversao,
                'em_andamento' => $em_andamento
            ],
            'vendas_periodo' => [
                'hoje' => $hoje,
                'semana' => $semana,
                'mes' => $mes,
                'trimestre' => $trimestre
            ],
            'tickets_medio' => [
                'valor' => 'R$ ' . number_format($valor_ticket_medio, 2, ',', '.'),
                'variacao' => $variacao_ticket
            ]
        ];
    }

    private function obterAtividadeRecente(array $filters = []): array
    {
        $atividadesFormatadas = [];

        // Últimos clientes
        $clientes = Customer::orderByDesc('created_at')->take(2)->get();
        foreach ($clientes as $c) {
            $atividadesFormatadas[] = [
                'id' => $c->id,
                'descricao' => 'Novo cliente cadastrado: ' . $c->name,
                'tempo' => Carbon::parse($c->created_at)->diffForHumans(),
                'tipo' => 'cliente'
            ];
        }

        // Últimos leads
        $leads = \App\Domain\Lead\Lead::orderByDesc('created_at')->take(2)->get();
        foreach ($leads as $l) {
            $atividadesFormatadas[] = [
                'id' => $l->id,
                'descricao' => 'Novo lead: ' . $l->name . ' - ' . ($l->source ?? 'Fonte desconhecida'),
                'tempo' => Carbon::parse($l->created_at)->diffForHumans(),
                'tipo' => 'lead'
            ];
        }

        // Últimas oportunidades
        $opps = \App\Domain\Opportunity\Opportunity::orderByDesc('created_at')->take(1)->get();
        foreach ($opps as $o) {
            $atividadesFormatadas[] = [
                'id' => $o->id,
                'descricao' => 'Venda finalizada: R$ ' . number_format($o->value, 2, ',', '.') . ' - ' . $o->title,
                'tempo' => Carbon::parse($o->created_at)->diffForHumans(),
                'tipo' => 'venda'
            ];
        }

        return $atividadesFormatadas;
    }

    private function obterDadosGraficos(array $filters = []): array
    {
        // Optimized queries for last N days
        $days = $filters['periodo'] ?? 30;
        $start = Carbon::now()->subDays($days - 1)->startOfDay();
        $end = Carbon::now()->endOfDay();

        // Opportunities grouped by date (count and sum)
        $oppsByDate = Opportunity::selectRaw("DATE(created_at) as day, COUNT(*) as count, COALESCE(SUM(value),0) as total")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // Leads grouped by date
        $leadsByDate = Lead::selectRaw("DATE(created_at) as day, COUNT(*) as count")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $vendas_diarias = [];
        $leads_diarios = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $op = $oppsByDate->has($date) ? $oppsByDate->get($date) : null;
            $ld = $leadsByDate->has($date) ? $leadsByDate->get($date) : null;

            $vendas_diarias[] = [
                'data' => $date,
                'vendas' => $op ? (int) $op->count : 0,
                'valor' => (float) ($op ? $op->total : 0)
            ];

            $leads_diarios[] = [
                'data' => $date,
                'leads' => $ld ? (int) $ld->count : 0
            ];
        }

        // Pipeline aggregates
        $totalLeads = Lead::count();
        $totalOppValue = Opportunity::sum('value') ?? 0;
        $pipeline = [
            [
                'etapa' => 'Leads',
                'quantidade' => $totalLeads,
                'valor' => 'R$ ' . number_format($totalOppValue, 2, ',', '.')
            ],
            [
                'etapa' => 'Negociação',
                'quantidade' => Opportunity::where('status', 'open')->count(),
                'valor' => 'R$ ' . number_format(Opportunity::where('status', 'open')->sum('value'), 2, ',', '.')
            ],
            [
                'etapa' => 'Fechamento',
                'quantidade' => Opportunity::where('status', 'closed')->count(),
                'valor' => 'R$ ' . number_format(Opportunity::where('status', 'closed')->sum('value'), 2, ',', '.')
            ]
        ];

        return [
            'vendas_mes' => $vendas_diarias,
            'leads_mes' => $leads_diarios,
            'pipeline' => $pipeline
        ];
    }

    private function gerarPerformanceVendedores(array $filters = []): array
    {
        $vendedores = [
            ['nome' => 'Carlos Silva', 'vendas' => 23, 'leads' => 45, 'conversao' => 51.1, 'meta' => 25, 'atingimento' => 92.0],
            ['nome' => 'Ana Santos', 'vendas' => 19, 'leads' => 38, 'conversao' => 50.0, 'meta' => 20, 'atingimento' => 95.0],
            ['nome' => 'Pedro Costa', 'vendas' => 17, 'leads' => 42, 'conversao' => 40.5, 'meta' => 18, 'atingimento' => 94.4],
            ['nome' => 'Maria Lima', 'vendas' => 15, 'leads' => 35, 'conversao' => 42.9, 'meta' => 15, 'atingimento' => 100.0],
            ['nome' => 'João Pereira', 'vendas' => 12, 'leads' => 28, 'conversao' => 42.8, 'meta' => 15, 'atingimento' => 80.0]
        ];

        return $vendedores;
    }

    private function gerarFontesLeads(array $filters = []): array
    {
        $fontes = [
            ['fonte' => 'Website', 'quantidade' => 89, 'percentual' => 35.6],
            ['fonte' => 'Google Ads', 'quantidade' => 67, 'percentual' => 26.8],
            ['fonte' => 'Facebook', 'quantidade' => 45, 'percentual' => 18.0],
            ['fonte' => 'Indicação', 'quantidade' => 28, 'percentual' => 11.2],
            ['fonte' => 'LinkedIn', 'quantidade' => 21, 'percentual' => 8.4]
        ];

        return $fontes;
    }

    public function vendedores(): JsonResponse
    {
        try {
            // Use database-backed sellers when available
            if (class_exists(\App\Models\Seller::class)) {
                $sellers = \App\Models\Seller::select(['id', 'name'])->orderBy('name')->get();
                return response()->json($sellers);
            }

            // Fallback to static list for compatibility
            $vendedores = [
                ['id' => 1, 'nome' => 'João Silva'],
                ['id' => 2, 'nome' => 'Maria Santos'],
                ['id' => 3, 'nome' => 'Pedro Costa'],
                ['id' => 4, 'nome' => 'Ana Oliveira'],
                ['id' => 5, 'nome' => 'Carlos Ferreira']
            ];

            return response()->json($vendedores);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao carregar vendedores',
                'vendedores' => []
            ], 500);
        }
    }

    public function simulateUpdate(Request $request): JsonResponse
    {
        try {
            // Simular novo cliente, venda ou lead
            $tipos = ['novo_cliente', 'nova_venda', 'lead_qualificado', 'meta_atingida'];
            $tipo = $request->get('tipo', $tipos[array_rand($tipos)]);
            
            $simulatedData = $this->gerarDadosSimulados($tipo);
            
            // Disparar evento WebSocket
            broadcast(new \App\Events\DashboardUpdated(
                $simulatedData,
                $tipo,
                $simulatedData['mensagem']
            ));

            return response()->json([
                'status' => 'success',
                'message' => 'Evento WebSocket disparado com sucesso',
                'data' => $simulatedData,
                'tipo' => $tipo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao disparar evento',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function gerarDadosSimulados(string $tipo): array
    {
        switch ($tipo) {
            case 'novo_cliente':
                return [
                    'mensagem' => '🎉 Novo cliente cadastrado: ' . fake()->name,
                    'valor' => null,
                    'incremento' => ['total_clientes' => 1],
                    'icone' => 'fas fa-user-plus',
                    'cor' => 'success'
                ];
            
            case 'nova_venda':
                $valor = fake()->numberBetween(1000, 10000);
                return [
                    'mensagem' => '💰 Nova venda: R$ ' . number_format($valor, 2, ',', '.'),
                    'valor' => $valor,
                    'incremento' => ['vendas_mes' => 1, 'receita_mes' => $valor],
                    'icone' => 'fas fa-money-bill-wave',
                    'cor' => 'primary'
                ];
            
            case 'lead_qualificado':
                return [
                    'mensagem' => '🎯 Lead qualificado: ' . fake()->company,
                    'valor' => null,
                    'incremento' => ['leads_ativos' => 1],
                    'icone' => 'fas fa-bullseye',
                    'cor' => 'warning'
                ];
            
            case 'meta_atingida':
                return [
                    'mensagem' => '🏆 Meta mensal atingida! Parabéns ao time!',
                    'valor' => null,
                    'incremento' => ['percentual_meta' => 100],
                    'icone' => 'fas fa-trophy',
                    'cor' => 'danger'
                ];
            
            default:
                return [
                    'mensagem' => '📊 Dados atualizados',
                    'valor' => null,
                    'incremento' => [],
                    'icone' => 'fas fa-chart-line',
                    'cor' => 'info'
                ];
        }
    }

    public function streamUpdates(Request $request)
    {
        return response()->stream(function () {
            while (true) {
                // Simular dados em tempo real
                $update = [
                    'timestamp' => now()->toISOString(),
                    'data' => [
                        'clientes_online' => rand(15, 45),
                        'vendas_hora' => rand(0, 8),
                        'leads_novos' => rand(0, 12),
                        'conversao_tempo_real' => rand(20, 35) . '%'
                    ],
                    'evento' => 'dados_tempo_real'
                ];

                echo "data: " . json_encode($update) . "\n\n";
                
                if (connection_aborted()) {
                    break;
                }
                
                sleep(5); // Atualizar a cada 5 segundos
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive'
        ]);
    }
}