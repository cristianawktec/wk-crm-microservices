import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';
import Chart from 'chart.js/auto';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
  loading = true;
  stats: any = {};
  errorMessage: string | null = null;
  activityFeed: any[] = [];
  performanceData: any[] = [];
  // filters and metadata
  filters: any = { period: '30', seller: '', status: '' };
  sellers: any[] = [];

  constructor(private api: ApiService) {}

  ngOnInit(): void {
    // load sellers for filter select
    this.api.getSellers().subscribe({ next: (res: any) => {
      if (Array.isArray(res)) {
        this.sellers = res;
      } else {
        this.sellers = res?.data || res || [];
      }
    }, error: () => this.sellers = [] });
    this.loadStats();
  }

  loadStats(params: any = {}) {
    this.loading = true;
    this.api.getDashboard(params).subscribe({
      next: (v) => {
        if (v && v.resumo) {
          this.stats = v.resumo;
        } else if (v) {
          // legacy / fallback format
          this.stats = {
            total_customers: v.totalCustomers || 0,
            receita_mes: v.salesThisMonth || 0,
            leads_ativos: v.activeOpportunities || 0,
            tickets_abertos: v.supportTickets || 0
          };
        } else {
          this.stats = { total_customers: 0, receita_mes: 0, leads_ativos: 0, tickets_abertos: 0 };
        }
        // If enhanced grafics available, render charts and feeds
        if (v && v.dados_graficos) {
          this.updateDashboardCharts(v.dados_graficos);
        }

        if (v && v.atividade_recente) {
          this.activityFeed = v.atividade_recente || [];
        } else {
          this.activityFeed = [];
        }

        if (v && v.performance_vendedores) {
          this.performanceData = v.performance_vendedores || [];
        } else {
          this.performanceData = [];
        }

        this.loading = false;
      },
      error: (err) => { this.loading = false; this.errorMessage = 'Falha ao carregar o dashboard. Verifique se o backend está em execução.'; console.error('Dashboard load error', err); }
    });
  }

  applyFilters() {
    const params: any = {};
    if (this.filters.period) params.period = this.filters.period;
    if (this.filters.seller) params.seller = this.filters.seller;
    if (this.filters.status) params.status = this.filters.status;
    this.loadStats(params);
  }

  resetFilters() {
    this.filters = { period: '30', seller: '', status: '' };
    this.loadStats();
  }

  updateDashboardCharts(dadosGraficos: any) {
    try {
      const vendas = (dadosGraficos.vendas_mes || []).slice(-30);

      const revenueCtx = (document.getElementById('revenue-chart-canvas') as HTMLCanvasElement)?.getContext('2d');
      if (revenueCtx) {
        // destroy existing chart instance if present
        if ((window as any).revenueChart instanceof Chart) (window as any).revenueChart.destroy();
        (window as any).revenueChart = new Chart(revenueCtx, {
          type: 'line',
          data: {
            labels: vendas.map((v: any) => new Date(v.data).toLocaleDateString('pt-BR', { month: 'short', day: 'numeric' })),
            datasets: [{ label: 'Vendas (R$)', data: vendas.map((v: any) => v.valor || 0), borderColor: 'rgb(75,192,192)', backgroundColor: 'rgba(75,192,192,0.2)', tension: 0.1 }]
          },
          options: { responsive: true, maintainAspectRatio: false }
        });
      }

      const salesCtx = (document.getElementById('sales-chart-canvas') as HTMLCanvasElement)?.getContext('2d');
      if (salesCtx) {
        if ((window as any).salesChart instanceof Chart) (window as any).salesChart.destroy();
        (window as any).salesChart = new Chart(salesCtx, {
          type: 'bar',
          data: {
            labels: vendas.map((v: any) => new Date(v.data).toLocaleDateString('pt-BR', { month: 'short', day: 'numeric' })),
            datasets: [{ label: 'Vendas', data: vendas.map((v: any) => v.vendas || 0), backgroundColor: 'rgba(54,162,235,0.6)' }]
          },
          options: { responsive: true, maintainAspectRatio: false }
        });
      }
    } catch (err) {
      console.warn('Erro ao renderizar charts', err);
    }
  }

  // The activity feed and performance table are rendered by Angular templates.
  // Keep these helpers in case we need to massaged data before binding.
  updateActivityFeed(atividadeRecente: any[]) {
    this.activityFeed = atividadeRecente || [];
  }

  updatePerformanceTable(performanceVendedores: any[]) {
    this.performanceData = performanceVendedores || [];
  }
}
