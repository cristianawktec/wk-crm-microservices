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

  revenueChart: any = null;

  ngOnInit(): void {
    this.loadStats();
    this.loadFullDashboard();
  }

  loadStats(params: any = {}) {
    this.loading = true;
    this.api.getDashboardStats(params).subscribe({
      next: (response: any) => {
        if (response && response.success && response.data) {
          const data = response.data;
          
          // Map stats to component properties
          this.stats = {
            customers: data.customers || {},
            leads: data.leads || {},
            opportunities: data.opportunities || {},
          };
        } else {
          this.stats = {
            customers: { total: 0, active: 0 },
            leads: { total: 0, open: 0, closed: 0, converted: 0 },
            opportunities: { total: 0, won: 0, lost: 0, pending: 0, total_value: 0 },
          };
        }
        this.loading = false;
      },
      error: (err) => {
        this.loading = false;
        this.errorMessage = 'Falha ao carregar o dashboard. Verifique se o backend está em execução.';
        console.error('Dashboard load error', err);
        this.stats = {
          customers: { total: 0, active: 0 },
          leads: { total: 0, open: 0, closed: 0, converted: 0 },
          opportunities: { total: 0, won: 0, lost: 0, pending: 0, total_value: 0 },
        };
      }
    });
  }

  loadFullDashboard(): void {
    this.api.getDashboard().subscribe({
      next: (response) => {
        console.log('Dashboard completo recebido:', response);
        this.activityFeed = response.atividade_recente || [];
        console.log('Atividades:', this.activityFeed);
        
        if (response.dados_graficos?.vendas_diarias) {
          console.log('Dados de vendas:', response.dados_graficos.vendas_diarias);
          setTimeout(() => this.renderRevenueChart(response.dados_graficos.vendas_diarias), 100);
        } else {
          console.warn('Dados de gráfico não encontrados, usando mock:', response.dados_graficos);
          // Usar dados mock se não houver dados reais
          const mockData = this.generateMockChartData();
          setTimeout(() => this.renderRevenueChart(mockData), 100);
        }
      },
      error: (error) => {
        console.error('Erro ao carregar dashboard completo:', error);
        const mockData = this.generateMockChartData();
        setTimeout(() => this.renderRevenueChart(mockData), 100);
      }
    });
  }

  generateMockChartData(): any[] {
    const data = [];
    for (let i = 13; i >= 0; i--) {
      const date = new Date();
      date.setDate(date.getDate() - i);
      data.push({
        data: date.toISOString().split('T')[0],
        valor: Math.floor(Math.random() * 50000) + 20000,
        vendas: Math.floor(Math.random() * 5) + 1
      });
    }
    return data;
  }

  renderRevenueChart(vendasDiarias: any[]): void {
    console.log('Iniciando renderização do gráfico com', vendasDiarias.length, 'dias de dados');
    
    const canvas = document.getElementById('revenue-chart-canvas') as HTMLCanvasElement;
    if (!canvas) {
      console.error('Canvas não encontrado!');
      return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
      console.error('Contexto 2D não disponível!');
      return;
    }

    const labels = vendasDiarias.slice(-14).map(v => {
      const date = new Date(v.data);
      return date.getDate() + '/' + (date.getMonth() + 1);
    });
    const valores = vendasDiarias.slice(-14).map(v => v.valor);
    
    console.log('Labels:', labels);
    console.log('Valores:', valores);

    if (this.revenueChart) {
      this.revenueChart.destroy();
    }

    this.revenueChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Vendas (R$)',
          data: valores,
          borderColor: 'rgb(40, 167, 69)',
          backgroundColor: 'rgba(40, 167, 69, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true, position: 'top' },
          tooltip: {
            callbacks: {
              label: (context: any) => {
                return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value: any) => 'R$ ' + value.toLocaleString('pt-BR')
            }
          }
        }
      }
    });
  }

  applyFilters() {
    // Filtros desabilitados temporariamente
    this.loadStats();
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
