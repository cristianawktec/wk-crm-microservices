import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, map, catchError, of, throwError } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface DashboardResumo {
  total_clientes: number;
  novos_clientes_mes: number;
  leads_ativos: number;
  vendas_mes: number;
  receita_mes: string;
  meta_mes: string;
  percentual_meta: number;
}

export interface DashboardMetricas {
  conversao_leads: {
    total_leads: number;
    convertidos: number;
    taxa_conversao: number;
    em_andamento: number;
  };
  vendas_periodo: {
    hoje: number;
    semana: number;
    mes: number;
    trimestre: number;
  };
  tickets_medio: {
    valor: string;
    variacao: string;
  };
}

export interface DashboardAtividade {
  id: string;
  descricao: string;
  tempo: string;
  tipo: 'cliente' | 'lead' | 'venda';
}

export interface DashboardGraficos {
  vendas_mes: Array<{ data: string; vendas: number; valor: number | string }>;
  leads_mes: Array<{ data: string; leads: number }>;
  pipeline: Array<{ etapa: string; quantidade: number; valor: string }>;
}

export interface DashboardVendedor {
  nome: string;
  vendas: number;
  leads: number;
  conversao: number;
  meta: number;
  atingimento: number;
}

export interface DashboardFonte {
  fonte: string;
  quantidade: number;
  percentual: number;
}

export interface DashboardResponse {
  resumo: DashboardResumo;
  metricas: DashboardMetricas;
  atividade_recente: DashboardAtividade[];
  dados_graficos: DashboardGraficos;
  performance_vendedores: DashboardVendedor[];
  fontes_leads: DashboardFonte[];
  filtros_aplicados: {
    periodo: string;
    vendedor: string;
    status: string;
  };
  sistema: {
    nome: string;
    versao: string;
    ultimo_update: string;
  };
}

@Injectable({
  providedIn: 'root'
})
export class DashboardService {
  private apiUrl = `${environment.apiUrl}/dashboard`;

  constructor(private http: HttpClient) {}

  getDashboard(periodo?: string, vendedor?: string, status?: string): Observable<DashboardResponse> {
    let params = new HttpParams();
    if (periodo) params = params.set('periodo', periodo);
    if (vendedor) params = params.set('vendedor', vendedor);
    if (status) params = params.set('status', status);
    return this.http.get<DashboardResponse>(this.apiUrl, { params }).pipe(
      catchError(err => {
        console.error('Falha ao carregar dashboard da API real:', err.status, err.message);
        if (!environment.production && environment.useMockDashboard) {
          console.warn('Usando dados mock de desenvolvimento (fallback).');
          const mock: DashboardResponse = {
            resumo: {
              total_clientes: 42,
              novos_clientes_mes: 5,
              leads_ativos: 18,
              vendas_mes: 12,
              receita_mes: 'R$ 18.500,00',
              meta_mes: 'R$ 25.000,00',
              percentual_meta: 74
            },
            metricas: {
              conversao_leads: {
                total_leads: 120,
                convertidos: 45,
                taxa_conversao: 37.5,
                em_andamento: 30
              },
              vendas_periodo: { hoje: 2, semana: 7, mes: 12, trimestre: 34 },
              tickets_medio: { valor: 'R$ 1.540,00', variacao: '+5%' }
            },
            atividade_recente: [
              { id: '1', descricao: 'Novo lead cadastrado: Empresa Alfa', tempo: 'há 5m', tipo: 'lead' },
              { id: '2', descricao: 'Venda concluída: Plano Premium', tempo: 'há 20m', tipo: 'venda' },
              { id: '3', descricao: 'Cliente atualizado: Beta Ltda', tempo: 'há 1h', tipo: 'cliente' }
            ],
            dados_graficos: {
              vendas_mes: Array.from({ length: 10 }).map((_, i) => ({ data: new Date(Date.now() - (9 - i) * 86400000).toISOString(), vendas: Math.round(Math.random()*5)+1, valor: 0 })),
              leads_mes: Array.from({ length: 10 }).map((_, i) => ({ data: new Date(Date.now() - (9 - i) * 86400000).toISOString(), leads: Math.round(Math.random()*10)+5 })),
              pipeline: [
                { etapa: 'Prospecção', quantidade: 15, valor: 'R$ 5.000,00' },
                { etapa: 'Qualificação', quantidade: 10, valor: 'R$ 4.500,00' },
                { etapa: 'Proposta', quantidade: 7, valor: 'R$ 3.800,00' },
                { etapa: 'Negociação', quantidade: 5, valor: 'R$ 3.200,00' },
                { etapa: 'Fechamento', quantidade: 3, valor: 'R$ 2.700,00' }
              ]
            },
            performance_vendedores: [
              { nome: 'Alice', vendas: 5, leads: 20, conversao: 25, meta: 10, atingimento: 50 },
              { nome: 'Bruno', vendas: 4, leads: 15, conversao: 26.6, meta: 10, atingimento: 40 },
              { nome: 'Carla', vendas: 3, leads: 12, conversao: 25, meta: 8, atingimento: 37.5 }
            ],
            fontes_leads: [
              { fonte: 'Orgânico', quantidade: 40, percentual: 33.3 },
              { fonte: 'Referência', quantidade: 30, percentual: 25 },
              { fonte: 'Ads', quantidade: 50, percentual: 41.7 }
            ],
            filtros_aplicados: {
              periodo: periodo || '30',
              vendedor: vendedor || 'all',
              status: status || 'all'
            },
            sistema: {
              nome: 'WK CRM Dev',
              versao: '0.1.0-mock',
              ultimo_update: new Date().toLocaleString('pt-BR')
            }
          };
          return of(mock);
        }
        return throwError(() => err);
      })
    );
  }

  getVendedores(): Observable<Array<{ id: number; nome: string }>> {
    return this.http.get<Array<{ id: number; nome: string }>>(`${this.apiUrl}/vendedores`);
  }

  // Conectar ao SSE para updates em tempo real
  getStreamUpdates(): EventSource {
    return new EventSource(`${environment.apiUrl}/dashboard/stream`);
  }
}
