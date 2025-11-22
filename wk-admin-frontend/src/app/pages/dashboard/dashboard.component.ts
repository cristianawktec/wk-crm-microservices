import { Component, OnInit, OnDestroy } from '@angular/core';
import { DashboardService, DashboardResponse } from '../../core/services/dashboard.service';
import { ThemeService } from '../../core/services/theme.service';
import { ChartConfiguration } from 'chart.js';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit, OnDestroy {
  dashboardData: DashboardResponse | null = null;
  loading = true;
  error: string | null = null;
  selectedPeriod = '30';
  selectedVendedor = 'all';
  selectedStatus = 'all';
  vendedores: Array<{ id: number; nome: string }> = [];
  showFilters = true;
  eventSource?: EventSource;

  periods = [
    { value: '7', label: 'Últimos 7 dias' },
    { value: '30', label: 'Últimos 30 dias' },
    { value: '90', label: 'Últimos 90 dias' },
    { value: '365', label: 'Último ano' }
  ];

  // Chart configurations
  vendasChartData: ChartConfiguration['data'] = { datasets: [], labels: [] };
  leadsChartData: ChartConfiguration['data'] = { datasets: [], labels: [] };
  pipelineChartData: ChartConfiguration['data'] = { datasets: [], labels: [] };

  chartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'bottom'
      }
    }
  };

  constructor(private dashboardService: DashboardService, private themeService: ThemeService) {}

  ngOnInit(): void {
    this.loadDashboard();
    // Recalcular cores dos gráficos ao trocar tema
    this.themeService.themeChanges.subscribe(() => {
      this.updateCharts();
    });
    // SSE desabilitado temporariamente
    // this.connectSSE();
  }

  ngOnDestroy(): void {
    if (this.eventSource) {
      this.eventSource.close();
    }
  }

  loadDashboard(): void {
    this.loading = true;
    this.error = null;
    console.log('Carregando dashboard com filtros:', {
      periodo: this.selectedPeriod,
      vendedor: this.selectedVendedor,
      status: this.selectedStatus
    });
    
    this.dashboardService.getDashboard(this.selectedPeriod, this.selectedVendedor, this.selectedStatus).subscribe({
      next: (data) => {
        console.log('Dashboard carregado com sucesso:', data);
        this.dashboardData = data;
        this.updateCharts();
        this.loading = false;
      },
      error: (error) => {
        console.error('Erro ao carregar dashboard:', error);
        console.error('Detalhes:', error.message, error.status, error.error);
        this.error = 'Falha ao carregar dados do dashboard.';
        this.loading = false;
      }
    });
  }

  toggleFilters(): void {
    this.showFilters = !this.showFilters;
  }

  onFilterChange(): void {
    this.loadDashboard();
  }

  connectSSE(): void {
    try {
      this.eventSource = this.dashboardService.getStreamUpdates();
      
      this.eventSource.onmessage = (event) => {
        const update = JSON.parse(event.data);
        console.log('Update SSE recebido:', update);
        // Atualizar dashboard com novos dados
        this.loadDashboard();
      };

      this.eventSource.onerror = (error) => {
        console.error('Erro no SSE:', error);
        this.eventSource?.close();
      };
    } catch (error) {
      console.error('Erro ao conectar SSE:', error);
    }
  }

  updateCharts(): void {
    if (!this.dashboardData) return;

    const graficos = this.dashboardData.dados_graficos;
    const styles = getComputedStyle(document.documentElement);
    const cPrimary = styles.getPropertyValue('--color-primary').trim() || '#3c8dbc';
    const cSuccess = styles.getPropertyValue('--color-success').trim() || '#28a745';
    const cWarning = styles.getPropertyValue('--color-warning').trim() || '#f39c12';
    const cDanger = styles.getPropertyValue('--color-danger').trim() || '#dc3545';
    const cAccent = styles.getPropertyValue('--color-accent').trim() || '#5c6bc0';

    // Gráfico de Vendas
    this.vendasChartData = {
      datasets: [
        {
          data: graficos.vendas_mes.map(d => d.vendas),
          label: 'Vendas',
          borderColor: cSuccess,
          backgroundColor: this.hexToRgba(cSuccess, 0.15),
          fill: true
        }
      ],
      labels: graficos.vendas_mes.map(d => new Date(d.data).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }))
    };

    // Gráfico de Leads
    this.leadsChartData = {
      datasets: [
        {
          data: graficos.leads_mes.map(d => d.leads),
          label: 'Leads',
          borderColor: cPrimary,
          backgroundColor: this.hexToRgba(cPrimary, 0.15),
          fill: true
        }
      ],
      labels: graficos.leads_mes.map(d => new Date(d.data).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }))
    };

    // Gráfico de Pipeline
    this.pipelineChartData = {
      datasets: [
        {
          data: graficos.pipeline.map(p => p.quantidade),
          label: 'Pipeline de Vendas',
          backgroundColor: [
            this.hexToRgba(cPrimary, 0.8),
            this.hexToRgba(cWarning, 0.8),
            this.hexToRgba(cSuccess, 0.8),
            this.hexToRgba(cDanger, 0.8),
            this.hexToRgba(cAccent, 0.8)
          ].slice(0, graficos.pipeline.length)
        }
      ],
      labels: graficos.pipeline.map(p => p.etapa)
    };
  }

  private hexToRgba(hex: string, alpha: number): string {
    const sanitized = hex.replace('#', '');
    if (sanitized.length === 3) {
      const r = parseInt(sanitized[0] + sanitized[0], 16);
      const g = parseInt(sanitized[1] + sanitized[1], 16);
      const b = parseInt(sanitized[2] + sanitized[2], 16);
      return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
    const bigint = parseInt(sanitized, 16);
    const r = (bigint >> 16) & 255;
    const g = (bigint >> 8) & 255;
    const b = bigint & 255;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  }

  parseReceita(valor: string): number {
    return parseFloat(valor.replace('R$', '').replace(/\./g, '').replace(',', '.').trim());
  }
}
