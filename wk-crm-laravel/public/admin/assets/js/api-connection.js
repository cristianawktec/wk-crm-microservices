/* Migrated API connection (kept same behavior as static site) */
// Use relative path so the dev-server proxy can forward requests to the Laravel backend.
const API_BASE_URL = '/api';

class WKCrmAPI {
  constructor() { this.baseURL = API_BASE_URL; }
  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const config = { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', ...(options.headers||{}) }, ...options };
    try {
      const response = await fetch(url, config);
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      return await response.json();
    } catch (error) { console.error('API Error:', error); throw error; }
  }

  async getCustomers() {
    try { return await this.request('/customers'); }
    catch (error) {
      console.warn('API não disponível, usando dados simulados:', error.message);
      return { data: [ { id:1, name:'João Silva Santos', email:'joao@example.com', phone:'(11) 99999-1111', created_at:'2024-10-15T10:30:00Z' } ], meta: { total:1 } };
    }
  }

  async getDashboardStats() {
    try { const dashboardData = await this.request('/dashboard'); return { resumo: dashboardData.resumo, metricas: dashboardData.metricas, atividade_recente: dashboardData.atividade_recente, dados_graficos: dashboardData.dados_graficos, performance_vendedores: dashboardData.performance_vendedores } }
    catch (error) { console.warn('Dashboard API não disponível, usando simulado.'); const customers = await this.getCustomers(); return { totalCustomers: customers.data.length, salesThisMonth: 0, activeOpportunities: 0, supportTickets: 0 }; }
  }
}

const wkAPI = new WKCrmAPI();

function showError(message, container=null){ const html = `<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-triangle"></i> ${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`; (container?container:document.getElementById('alert-container')||document.body).insertAdjacentHTML('afterbegin', html); }

export { wkAPI, showError };
