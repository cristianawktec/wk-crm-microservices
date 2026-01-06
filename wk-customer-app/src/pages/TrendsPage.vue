<template>
  <div class="trends-container">
    <header class="trends-header">
      <h1>📊 Análise de Tendências</h1>
      <p>Insights detalhados sobre seu desempenho de vendas</p>
    </header>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Gerando análise de tendências...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="loadTrends" class="btn-retry">Tentar Novamente</button>
    </div>

    <div v-else class="trends-content">
      <!-- Period Selector -->
      <div class="period-selector">
        <label>Período:</label>
        <select v-model="selectedPeriod" @change="loadTrends">
          <option value="month">Último Mês</option>
          <option value="quarter">Último Trimestre</option>
          <option value="year">Último Ano</option>
        </select>
      </div>

      <!-- Summary Card -->
      <div class="summary-card">
        <h3>Resumo Geral</h3>
        <p v-if="trends.summary">{{ trends.summary }}</p>
      </div>

      <!-- KPI Grid -->
      <div class="kpi-grid">
        <!-- Conversion Rate -->
        <div class="kpi-card">
          <div class="kpi-icon">📈</div>
          <div class="kpi-content">
            <h4>Taxa de Conversão</h4>
            <p class="kpi-value">{{ trends.conversion_trends?.conversion_rate || 0 }}%</p>
            <small>{{ trends.conversion_trends?.total || 0 }} oportunidades</small>
          </div>
        </div>

        <!-- Win Rate -->
        <div class="kpi-card">
          <div class="kpi-icon">🎯</div>
          <div class="kpi-content">
            <h4>Deals Ganhos</h4>
            <p class="kpi-value">{{ trends.conversion_trends?.won || 0 }}</p>
            <small>{{ trends.conversion_trends?.lost || 0 }} perdidos</small>
          </div>
        </div>

        <!-- Average Deal Size -->
        <div class="kpi-card">
          <div class="kpi-icon">💰</div>
          <div class="kpi-content">
            <h4>Ticket Médio</h4>
            <p class="kpi-value">{{ formatCurrency(trends.sales_forecast?.historical_avg_deal_size) }}</p>
            <small>Tamanho histórico</small>
          </div>
        </div>

        <!-- Sales Cycle -->
        <div class="kpi-card">
          <div class="kpi-icon">⏱️</div>
          <div class="kpi-content">
            <h4>Ciclo de Vendas</h4>
            <p class="kpi-value">{{ trends.cycle_analysis?.avg_cycle_days || 0 }}d</p>
            <small>Média de dias</small>
          </div>
        </div>
      </div>

      <!-- Sector Analysis -->
      <div class="section">
        <h3>Desempenho por Setor 🏢</h3>
        <div v-if="trends.sector_analysis?.sectors?.length > 0" class="sector-table">
          <table>
            <thead>
              <tr>
                <th>Setor</th>
                <th>Oportunidades</th>
                <th>Ganhas</th>
                <th>Taxa de Conversão</th>
                <th>Valor Total</th>
                <th>Ticket Médio</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="sector in trends.sector_analysis.sectors" :key="sector.sector">
                <td><strong>{{ sector.sector }}</strong></td>
                <td>{{ sector.total }}</td>
                <td>{{ sector.won }}</td>
                <td>
                  <span class="badge" :class="getBadgeClass(sector.conversion_rate)">
                    {{ sector.conversion_rate }}%
                  </span>
                </td>
                <td>{{ formatCurrency(sector.total_value) }}</td>
                <td>{{ formatCurrency(sector.avg_value) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="no-data">Sem dados de setor disponíveis</p>
      </div>

      <!-- Top Products -->
      <div class="section">
        <h3>Produtos/Serviços Mais Vendidos 🏆</h3>
        <div v-if="trends.product_performance?.top_products?.length > 0" class="products-list">
          <div v-for="(product, idx) in trends.product_performance.top_products" :key="idx" class="product-item">
            <div class="product-rank">{{ (idx as number) + 1 }}</div>
            <div class="product-info">
              <h4>{{ product.title }}</h4>
              <small>{{ product.count }} venda(s)</small>
            </div>
            <div class="product-value">{{ formatCurrency(product.total_value) }}</div>
          </div>
        </div>
        <p v-else class="no-data">Sem produtos vendidos no período</p>
      </div>

      <!-- Sales Forecast -->
      <div class="section">
        <h3>Previsão de Vendas 🔮</h3>
        <div class="forecast-grid">
          <div class="forecast-card">
            <h4>Próximos 30 Dias</h4>
            <p class="forecast-value">{{ formatCurrency(trends.sales_forecast?.next_30_days) }}</p>
            <small>Em oportunidades ativas</small>
          </div>
          <div class="forecast-card">
            <h4>Média Mensal</h4>
            <p class="forecast-value">{{ formatCurrency(trends.sales_forecast?.monthly_average) }}</p>
            <small>Baseado em histórico</small>
          </div>
          <div class="forecast-card">
            <h4>Confiança da Previsão</h4>
            <p class="forecast-value">{{ trends.sales_forecast?.forecast_confidence }}</p>
            <small>Nível de certeza</small>
          </div>
        </div>
      </div>

      <!-- Best Periods -->
      <div class="section">
        <h3>Melhores Períodos de Venda 📅</h3>
        <div v-if="trends.best_periods?.best_months?.length > 0" class="periods-list">
          <div v-for="(period, idx) in trends.best_periods.best_months" :key="idx" class="period-item">
            <div class="period-rank">🥇</div>
            <div class="period-stats">
              <h4>Mês {{ (idx as number) + 1 }}</h4>
              <p>{{ period.count }} oportunidade(s) ganhas</p>
              <small>Valor: {{ formatCurrency(period.value) }}</small>
            </div>
          </div>
        </div>
        <p v-if="trends.best_periods?.pattern" class="pattern-info">
          <strong>Padrão identificado:</strong> {{ trends.best_periods.pattern }}
        </p>
        <p v-else class="no-data">Sem dados de período disponíveis</p>
      </div>

      <!-- Sales Cycle Details -->
      <div class="section">
        <h3>Análise do Ciclo de Vendas ⏱️</h3>
        <div class="cycle-grid">
          <div class="cycle-card">
            <h4>Ciclo Médio</h4>
            <p class="cycle-value">{{ trends.cycle_analysis?.avg_cycle_days || 0 }}d</p>
            <small>Dias até fechamento</small>
          </div>
          <div class="cycle-card">
            <h4>Ciclo Mais Rápido</h4>
            <p class="cycle-value">{{ trends.cycle_analysis?.fastest_deal_days || 0 }}d</p>
            <small>Melhor tempo</small>
          </div>
          <div class="cycle-card">
            <h4>Ciclo Mais Longo</h4>
            <p class="cycle-value">{{ trends.cycle_analysis?.slowest_deal_days || 0 }}d</p>
            <small>Maior duração</small>
          </div>
          <div class="cycle-card">
            <h4>Mediana</h4>
            <p class="cycle-value">{{ trends.cycle_analysis?.median_cycle_days || 0 }}d</p>
            <small>Valor central</small>
          </div>
        </div>
      </div>

      <!-- Export Options -->
      <div class="section export-section">
        <h3>Exportar Relatório</h3>
        <div class="export-buttons">
          <button @click="exportPDF" class="btn-export">📄 PDF</button>
          <button @click="exportCSV" class="btn-export">📊 CSV</button>
          <button @click="exportJSON" class="btn-export">📋 JSON</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { api } from '../services/api'

interface Trends {
  conversion_trends?: any
  sector_analysis?: any
  product_performance?: any
  sales_forecast?: any
  best_periods?: any
  cycle_analysis?: any
  summary?: string
}

const toast = useToast()
const loading = ref(false)
const error = ref('')
const selectedPeriod = ref('year')
const trends = ref<Trends>({})

const loadTrends = async () => {
  loading.value = true
  error.value = ''

  try {
    trends.value = await api.getTrends(selectedPeriod.value)
  } catch (err: any) {
    if (err.response?.status === 401) {
      error.value = 'Sessão expirada. Por favor, faça login novamente.'
      toast.error('Sua sessão expirou. Por favor, faça login novamente')
    } else if (err.response?.status === 404) {
      error.value = 'Serviço de análise temporariamente indisponível'
      console.error('Trends endpoint not found (404)')
    } else {
      error.value = 'Erro ao conectar ao servidor'
    }
    console.error('Trend analysis error:', err.response?.status, err.message)
  } finally {
    loading.value = false
  }
}

const formatCurrency = (value: number | undefined): string => {
  if (!value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const getBadgeClass = (rate: number): string => {
  if (rate >= 70) return 'badge-success'
  if (rate >= 50) return 'badge-warning'
  return 'badge-danger'
}

const exportPDF = () => {
  toast.info('PDF export em desenvolvimento')
}

const exportCSV = () => {
  toast.info('CSV export em desenvolvimento')
}

const exportJSON = () => {
  const jsonString = JSON.stringify(trends.value, null, 2)
  const blob = new Blob([jsonString], { type: 'application/json' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `trends-${new Date().toISOString().split('T')[0]}.json`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
  toast.success('Relatório exportado com sucesso!')
}

onMounted(() => {
  loadTrends()
})
</script>

<style scoped lang="css">
.trends-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  background: #f8f9fa;
  min-height: 100vh;
}

.trends-header {
  text-align: center;
  margin-bottom: 40px;
  color: #333;
}

.trends-header h1 {
  font-size: 32px;
  margin-bottom: 8px;
}

.trends-header p {
  font-size: 16px;
  color: #666;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.spinner {
  width: 40px;
  height: 40px;
  margin: 0 auto 20px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state p {
  color: #ef4444;
  margin-bottom: 20px;
  font-size: 16px;
}

.btn-retry {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
}

.btn-retry:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.period-selector {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 30px;
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.period-selector label {
  font-weight: 600;
  color: #333;
}

.period-selector select {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  background: white;
  transition: border-color 0.2s;
}

.period-selector select:focus {
  outline: none;
  border-color: #667eea;
}

.summary-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 30px;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.summary-card h3 {
  margin: 0 0 10px 0;
  font-size: 18px;
}

.summary-card p {
  margin: 0;
  font-size: 16px;
  line-height: 1.6;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.kpi-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  gap: 15px;
  align-items: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.kpi-icon {
  font-size: 32px;
  min-width: 50px;
  text-align: center;
}

.kpi-content h4 {
  margin: 0 0 8px 0;
  color: #666;
  font-size: 14px;
  font-weight: 600;
}

.kpi-value {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #333;
}

.kpi-content small {
  display: block;
  color: #999;
  font-size: 12px;
  margin-top: 4px;
}

.section {
  background: white;
  padding: 25px;
  border-radius: 12px;
  margin-bottom: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section h3 {
  margin: 0 0 20px 0;
  font-size: 20px;
  color: #333;
  border-bottom: 2px solid #667eea;
  padding-bottom: 10px;
}

.no-data {
  text-align: center;
  color: #999;
  padding: 40px 20px;
}

.sector-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

table thead {
  background: #f8f9fa;
}

table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #333;
  border-bottom: 2px solid #e5e7eb;
}

table td {
  padding: 12px;
  border-bottom: 1px solid #e5e7eb;
  color: #666;
}

table tbody tr:hover {
  background: #f8f9fa;
}

.badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 12px;
}

.badge-success {
  background: #d1fae5;
  color: #065f46;
}

.badge-warning {
  background: #fed7aa;
  color: #92400e;
}

.badge-danger {
  background: #fee2e2;
  color: #991b1b;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  transition: background 0.2s;
}

.product-item:hover {
  background: #f0f0f0;
}

.product-rank {
  font-size: 24px;
  font-weight: 700;
  color: #667eea;
  min-width: 40px;
  text-align: center;
}

.product-info {
  flex: 1;
}

.product-info h4 {
  margin: 0 0 4px 0;
  color: #333;
  font-size: 14px;
  font-weight: 600;
}

.product-info small {
  color: #999;
  display: block;
}

.product-value {
  font-weight: 700;
  color: #667eea;
  font-size: 16px;
  min-width: 120px;
  text-align: right;
}

.forecast-grid,
.cycle-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 15px;
}

.forecast-card,
.cycle-card {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  border-left: 4px solid #667eea;
  text-align: center;
}

.forecast-card h4,
.cycle-card h4 {
  margin: 0 0 10px 0;
  color: #666;
  font-size: 13px;
  font-weight: 600;
}

.forecast-value,
.cycle-value {
  font-size: 24px;
  font-weight: 700;
  color: #333;
  margin: 0 0 8px 0;
}

.forecast-card small,
.cycle-card small {
  color: #999;
  font-size: 12px;
}

.periods-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 15px;
}

.period-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.period-rank {
  font-size: 20px;
  min-width: 30px;
  text-align: center;
}

.period-stats {
  flex: 1;
}

.period-stats h4 {
  margin: 0 0 4px 0;
  color: #333;
  font-size: 14px;
  font-weight: 600;
}

.period-stats p {
  margin: 0;
  color: #666;
  font-size: 13px;
}

.period-stats small {
  color: #999;
  display: block;
  margin-top: 4px;
}

.pattern-info {
  background: #fef3c7;
  color: #92400e;
  padding: 12px;
  border-radius: 6px;
  margin-top: 15px;
  font-size: 14px;
  margin-bottom: 0;
}

.export-section {
  text-align: center;
}

.export-buttons {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-export {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-export:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

@media (max-width: 768px) {
  .trends-container {
    padding: 15px;
  }

  .trends-header h1 {
    font-size: 24px;
  }

  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .forecast-grid,
  .cycle-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  table {
    font-size: 12px;
  }

  table th,
  table td {
    padding: 8px;
  }
}
</style>
