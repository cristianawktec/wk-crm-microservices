import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { ToastService } from '../../services/toast.service';

@Component({
  selector: 'app-reports',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './reports.component.html',
  styleUrls: ['./reports.component.css']
})
export class ReportsComponent implements OnInit {
  loading = false;
  selectedYear = new Date().getFullYear();
  selectedMonth = new Date().getMonth() + 1;
  selectedPeriod = 'month';

  // Data
  kpis: any[] = [];
  monthlySales: any[] = [];
  statusDistribution: any[] = [];
  topSellers: any[] = [];
  salesFunnel: any[] = [];

  // Chart data (for charting library)
  salesChartData: any = null;
  statusChartData: any = null;
  funnelChartData: any = null;
  sellersChartData: any = null;

  // Years for filter
  years: number[] = [];

  constructor(private api: ApiService, private toast: ToastService) {
    // Generate years array (last 5 years)
    const currentYear = new Date().getFullYear();
    for (let i = 0; i < 5; i++) {
      this.years.push(currentYear - i);
    }
  }

  ngOnInit(): void {
    this.loadAnalytics();
  }

  loadAnalytics(): void {
    this.loading = true;
    const params = {
      year: this.selectedYear,
      month: this.selectedMonth,
      period: this.selectedPeriod
    };

    Promise.all([
      this.loadKpis(params),
      this.loadMonthlySales(params),
      this.loadStatusDistribution(params),
      this.loadTopSellers(params),
      this.loadSalesFunnel(params)
    ]).then(() => {
      this.loading = false;
      this.toast.success('Sucesso!', 'Relatórios carregados com sucesso!');
    }).catch(() => {
      this.loading = false;
      this.toast.error('Erro', 'Erro ao carregar relatórios');
    });
  }

  private loadKpis(params: any): Promise<void> {
    return new Promise((resolve) => {
      this.api.getAnalyticsKpis(params).subscribe({
        next: (response) => {
          if (response.success) {
            this.kpis = response.kpis || [];
          }
          resolve();
        },
        error: () => resolve()
      });
    });
  }

  private loadMonthlySales(params: any): Promise<void> {
    return new Promise((resolve) => {
      this.api.getMonthlySalesTrend(params).subscribe({
        next: (response) => {
          if (response.success) {
            this.monthlySales = response.data || [];
            this.prepareSalesChart();
          }
          resolve();
        },
        error: () => resolve()
      });
    });
  }

  private loadStatusDistribution(params: any): Promise<void> {
    return new Promise((resolve) => {
      this.api.getStatusDistribution(params).subscribe({
        next: (response) => {
          if (response.success) {
            this.statusDistribution = response.data || [];
            this.prepareStatusChart();
          }
          resolve();
        },
        error: () => resolve()
      });
    });
  }

  private loadTopSellers(params: any): Promise<void> {
    return new Promise((resolve) => {
      this.api.getTopSellers(params).subscribe({
        next: (response) => {
          if (response.success) {
            this.topSellers = response.data || [];
            this.prepareSellersChart();
          }
          resolve();
        },
        error: () => resolve()
      });
    });
  }

  private loadSalesFunnel(params: any): Promise<void> {
    return new Promise((resolve) => {
      this.api.getSalesFunnel(params).subscribe({
        next: (response) => {
          if (response.success) {
            this.salesFunnel = response.funnel || [];
            this.prepareFunnelChart();
          }
          resolve();
        },
        error: () => resolve()
      });
    });
  }

  private prepareSalesChart(): void {
    // Prepare data for Chart.js or your charting library
    // This is a simple structure that can be used with various charting libraries
    this.salesChartData = {
      labels: this.monthlySales.map(m => m.month),
      datasets: [{
        label: 'Vendas (R$)',
        data: this.monthlySales.map(m => m.value),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }]
    };
  }

  private prepareStatusChart(): void {
    this.statusChartData = {
      labels: this.statusDistribution.map(s => s.status),
      datasets: [{
        label: 'Oportunidades por Status',
        data: this.statusDistribution.map(s => s.count),
        backgroundColor: this.statusDistribution.map(s => s.color),
        borderColor: this.statusDistribution.map(s => s.color)
      }]
    };
  }

  private prepareFunnelChart(): void {
    this.funnelChartData = {
      labels: this.salesFunnel.map(s => s.status),
      datasets: [{
        label: 'Funil de Vendas',
        data: this.salesFunnel.map(s => s.count),
        backgroundColor: this.salesFunnel.map(s => s.color),
        borderColor: this.salesFunnel.map(s => s.color)
      }]
    };
  }

  private prepareSellersChart(): void {
    this.sellersChartData = {
      labels: this.topSellers.map(s => s.seller_name),
      datasets: [{
        label: 'Top Vendedores (R$)',
        data: this.topSellers.map(s => s.total_value),
        backgroundColor: '#3b82f6',
        borderColor: '#1e40af'
      }]
    };
  }

  onYearChange(): void {
    this.loadAnalytics();
  }

  onMonthChange(): void {
    this.loadAnalytics();
  }

  exportPDF(): void {
    this.toast.info('Em Desenvolvimento', 'Exportação em PDF em desenvolvimento...');
  }

  exportExcel(): void {
    this.toast.info('Em Desenvolvimento', 'Exportação em Excel em desenvolvimento...');
  }

  // Helper methods for display
  getKpiClass(index: number): string {
    const colors = ['border-indigo-500', 'border-green-500', 'border-blue-500', 'border-purple-500'];
    return colors[index] || colors[0];
  }

  formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value || 0);
  }
}
