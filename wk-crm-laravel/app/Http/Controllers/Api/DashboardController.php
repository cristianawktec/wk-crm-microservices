<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
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
{
    // ...existing code...
    public function index(Request $request): JsonResponse
    {
        try {
            // Extrair filtros da request
            $filters = [
                'periodo' => $request->get('periodo', '30'),
                'vendedor' => $request->get('vendedor', 'all'),
                'status' => $request->get('status', 'all')
            ];
            
            $data = [
                'resumo' => $this->obterResumo($filters),
                'metricas' => $this->obterMetricas($filters),
                'atividade_recente' => $this->obterAtividadeRecente($filters),
                'dados_graficos' => $this->obterDadosGraficos($filters),
                'performance_vendedores' => $this->gerarPerformanceVendedores($filters),
                'fontes_leads' => $this->gerarFontesLeads($filters),
                'filtros_aplicados' => $filters,
                'sistema' => [
                    'nome' => 'WK CRM Laravel Enhanced',
                    'versao' => '2.0.0',  
                    'ultimo_update' => Carbon::now()->format('d/m/Y H:i:s')
                ]
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Dashboard temporariamente indisponível',
                'sistema' => [
                    'nome' => 'WK CRM Laravel Enhanced',
                    'versao' => '2.0.0',  
                    'status' => 'error'
                ]
            ], 500);
        }
    }

    private function obterResumo(array $filters = []): array
    {
        // Dados reais do banco
        $total_clientes = \App\Domain\Customer\Customer::count();
        $novos_clientes_mes = \App\Domain\Customer\Customer::where('created_at', '>=', now()->subMonth())->count();
        $leads_ativos = \App\Domain\Lead\Lead::where('status', 'new')->count();
        $vendas_mes = \App\Domain\Opportunity\Opportunity::where('created_at', '>=', now()->subMonth())->count();
        $receita_mes = \App\Domain\Opportunity\Opportunity::where('created_at', '>=', now()->subMonth())->sum('value');
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
        $total_leads = \App\Domain\Lead\Lead::count();
        $convertidos = \App\Domain\Lead\Lead::where('status', 'converted')->count();
        $em_andamento = \App\Domain\Lead\Lead::where('status', 'new')->count();
        $taxa_conversao = $total_leads > 0 ? round(($convertidos / $total_leads) * 100, 2) : 0;

        $hoje = \App\Domain\Opportunity\Opportunity::whereDate('created_at', now()->toDateString())->count();
        $semana = \App\Domain\Opportunity\Opportunity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $mes = \App\Domain\Opportunity\Opportunity::whereMonth('created_at', now()->month)->count();
        $trimestre = \App\Domain\Opportunity\Opportunity::whereBetween('created_at', [now()->subMonths(3), now()])->count();

        $valor_ticket_medio = $mes > 0 ? \App\Domain\Opportunity\Opportunity::whereMonth('created_at', now()->month)->sum('value') / $mes : 0;
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
        $clientes = \App\Domain\Customer\Customer::orderByDesc('created_at')->take(2)->get();
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
        // Gráficos reais dos últimos 30 dias
        $vendas_diarias = [];
        $leads_diarios = [];
        for ($i = 29; $i >= 0; $i--) {
            $data = Carbon::now()->subDays($i)->format('Y-m-d');
            $vendas_count = \App\Domain\Opportunity\Opportunity::whereDate('created_at', $data)->count();
            $vendas_valor = \App\Domain\Opportunity\Opportunity::whereDate('created_at', $data)->sum('value');
            $leads_count = \App\Domain\Lead\Lead::whereDate('created_at', $data)->count();
            $vendas_diarias[] = [
                'data' => $data,
                'vendas' => $vendas_count,
                'valor' => $vendas_valor
            ];
            $leads_diarios[] = [
                'data' => $data,
                'leads' => $leads_count
            ];
        }

        // Pipeline simplificado
        $pipeline = [
            [
                'etapa' => 'Leads',
                'quantidade' => \App\Domain\Lead\Lead::count(),
                'valor' => 'R$ ' . number_format(\App\Domain\Opportunity\Opportunity::sum('value'), 2, ',', '.')
            ],
            [
                'etapa' => 'Negociação',
                'quantidade' => \App\Domain\Opportunity\Opportunity::where('status', 'open')->count(),
                'valor' => 'R$ ' . number_format(\App\Domain\Opportunity\Opportunity::where('status', 'open')->sum('value'), 2, ',', '.')
            ],
            [
                'etapa' => 'Fechamento',
                'quantidade' => \App\Domain\Opportunity\Opportunity::where('status', 'closed')->count(),
                'valor' => 'R$ ' . number_format(\App\Domain\Opportunity\Opportunity::where('status', 'closed')->sum('value'), 2, ',', '.')
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
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Cache-Control'
        ]);
    }
}