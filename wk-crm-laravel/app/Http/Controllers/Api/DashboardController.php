<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
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
        // Simular filtros aplicados nos dados
        $multiplier = 1.0;
        if ($filters['periodo'] == '7') $multiplier = 0.25;
        elseif ($filters['periodo'] == '90') $multiplier = 3.0;
        elseif ($filters['periodo'] == '365') $multiplier = 12.0;
        
        return [
            'total_clientes' => (int)(1247 * $multiplier),
            'novos_clientes_mes' => (int)(89 * $multiplier),
            'leads_ativos' => (int)(234 * $multiplier),
            'vendas_mes' => (int)(156 * $multiplier),
            'receita_mes' => 'R$ 234.567,89',
            'meta_mes' => 'R$ 250.000,00',
            'percentual_meta' => 94
        ];
    }

    private function obterMetricas(array $filters = []): array
    {
        return [
            'conversao_leads' => [
                'total_leads' => 456,
                'convertidos' => 123,
                'taxa_conversao' => 27.0,
                'em_andamento' => 234
            ],
            'vendas_periodo' => [
                'hoje' => 12,
                'semana' => 89,
                'mes' => 156,
                'trimestre' => 489
            ],
            'tickets_medio' => [
                'valor' => 'R$ 1.504,54',
                'variacao' => '+12.5%'
            ]
        ];
    }

    private function obterAtividadeRecente(array $filters = []): array
    {
        $atividades = [
            'Novo cliente cadastrado: João Silva',
            'Venda finalizada: R$ 2.340,00 - Maria Santos',  
            'Lead qualificado: Pedro Oliveira - Interesse em consultoria',
            'Reunião agendada: Ana Costa - 15/10 às 14h',
            'Proposta enviada: Carlos Lima - R$ 5.670,00',
            'Cliente reativado: Fernanda Rocha',
            'Novo lead: Ricardo Pereira - Site corporativo'
        ];

        $atividadesFormatadas = [];
        foreach (array_slice($atividades, 0, 5) as $index => $atividade) {
            $atividadesFormatadas[] = [
                'id' => $index + 1,
                'descricao' => $atividade,
                'tempo' => Carbon::now()->subMinutes(rand(10, 300))->diffForHumans(),
                'tipo' => ['cliente', 'venda', 'lead', 'reuniao', 'proposta'][rand(0, 4)]
            ];
        }

        return $atividadesFormatadas;
    }

    private function obterDadosGraficos(array $filters = []): array
    {
        // Dados para gráficos dos últimos 30 dias
        $vendas_diarias = [];
        $leads_diarios = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $data = Carbon::now()->subDays($i)->format('Y-m-d');
            $vendas_diarias[] = [
                'data' => $data,
                'vendas' => rand(2, 15),
                'valor' => rand(1500, 8500)
            ];
            $leads_diarios[] = [
                'data' => $data,
                'leads' => rand(5, 25)
            ];
        }

        return [
            'vendas_mes' => $vendas_diarias,
            'leads_mes' => $leads_diarios,
            'pipeline' => [
                ['etapa' => 'Leads', 'quantidade' => 234, 'valor' => 'R$ 456.789'],
                ['etapa' => 'Qualificados', 'quantidade' => 123, 'valor' => 'R$ 234.567'], 
                ['etapa' => 'Propostas', 'quantidade' => 89, 'valor' => 'R$ 178.234'],
                ['etapa' => 'Negociação', 'quantidade' => 45, 'valor' => 'R$ 89.123'],
                ['etapa' => 'Fechamento', 'quantidade' => 23, 'valor' => 'R$ 67.890']
            ]
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