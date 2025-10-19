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
            $data = [
                'resumo' => $this->obterResumo(),
                'metricas' => $this->obterMetricas(),
                'atividade_recente' => $this->obterAtividadeRecente(),
                'dados_graficos' => $this->obterDadosGraficos(),
                'performance_vendedores' => $this->gerarPerformanceVendedores(),
                'fontes_leads' => $this->gerarFontesLeads(),
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

    private function obterResumo(): array
    {
        return [
            'total_clientes' => 1247,
            'novos_clientes_mes' => 89,
            'leads_ativos' => 234,
            'vendas_mes' => 156,
            'receita_mes' => 'R$ 234.567,89',
            'meta_mes' => 'R$ 250.000,00',
            'percentual_meta' => 94
        ];
    }

    private function obterMetricas(): array
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

    private function obterAtividadeRecente(): array
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

    private function obterDadosGraficos(): array
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

    private function gerarPerformanceVendedores(): array
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

    private function gerarFontesLeads(): array
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
}