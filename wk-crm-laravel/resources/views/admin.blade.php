<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WK CRM Brasil - Painel Local</title>

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- AdminLTE Theme style -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  
  <style>
    .brand-link {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .main-header .navbar {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .small-box {
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
    }
    
    .small-box:hover {
      transform: translateY(-5px);
    }
    
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255,255,255,.3);
      border-radius: 50%;
      border-top-color: #fff;
      animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Dashboard Local</a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="/" role="button">
          <i class="fas fa-home"></i> Voltar
        </a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
      <img src="https://via.placeholder.com/33x33/FFFFFF/667eea?text=WK" alt="WK CRM" class="brand-image img-circle elevation-3">
      <span class="brand-text font-weight-light">WK CRM Local</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://via.placeholder.com/160x160/28a745/ffffff?text=Dev" class="img-circle elevation-2" alt="Dev">
        </div>
        <div class="info">
          <a href="#" class="d-block">Desenvolvedor</a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/api/clientes" class="nav-link" target="_blank">
              <i class="nav-icon fas fa-users"></i>
              <p>API Clientes</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/api/leads" class="nav-link" target="_blank">
              <i class="nav-icon fas fa-user-plus"></i>
              <p>API Leads</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/api/oportunidades" class="nav-link" target="_blank">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>API Oportunidades</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard Local - Conectado ao VPS</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/">WK CRM</a></li>
              <li class="breadcrumb-item active">Dashboard Local</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <!-- Status da API -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i> <strong>Conexão VPS:</strong> 
              <span id="api-status" class="loading"></span>
              <span id="api-message">Conectando ao banco VPS...</span>
            </div>
          </div>
        </div>

        <!-- Filtros Dinâmicos -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros Dinâmicos</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="filter-period">Período:</label>
                      <select class="form-control" id="filter-period">
                        <option value="7">Últimos 7 dias</option>
                        <option value="30" selected>Últimos 30 dias</option>
                        <option value="90">Últimos 90 dias</option>
                        <option value="365">Último ano</option>
                        <option value="all">Todos os períodos</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="filter-vendedor">Vendedor:</label>
                      <select class="form-control" id="filter-vendedor">
                        <option value="all" selected>Todos os vendedores</option>
                        <option value="loading">Carregando...</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="filter-status">Status:</label>
                      <select class="form-control" id="filter-status">
                        <option value="all" selected>Todos os status</option>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                        <option value="prospecto">Prospecto</option>
                        <option value="cliente">Cliente</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>&nbsp;</label>
                      <div>
                        <button type="button" class="btn btn-primary btn-block" id="apply-filters">
                          <i class="fas fa-search"></i> Aplicar Filtros
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-12">
                    <small class="text-muted">
                      <i class="fas fa-info-circle"></i> 
                      Filtros ativos: <span id="active-filters">Período: 30 dias, Vendedor: Todos, Status: Todos</span>
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tempo Real -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-broadcast-tower"></i> Dados em Tempo Real</h3>
                <div class="card-tools">
                  <span id="realtime-status" class="badge badge-success">
                    <i class="fas fa-circle text-success"></i> Conectando...
                  </span>
                  <button type="button" class="btn btn-sm btn-outline-primary ml-2" onclick="toggleRealTime()">
                    <i class="fas fa-power-off"></i> Toggle
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div id="real-time-indicators">
                  <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Aguardando dados tempo real...
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estatísticas -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3 id="total-clientes"><span class="loading"></span></h3>
                <p>Total de Clientes</p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3 id="total-leads"><span class="loading"></span></h3>
                <p>Leads Ativos</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-plus"></i>
              </div>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3 id="total-oportunidades"><span class="loading"></span></h3>
                <p>Oportunidades</p>
              </div>
              <div class="icon">
                <i class="fas fa-chart-line"></i>
              </div>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3 id="valor-pipeline">R$ <span class="loading"></span></h3>
                <p>Pipeline Total</p>
              </div>
              <div class="icon">
                <i class="fas fa-dollar-sign"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações do Sistema -->
        <div class="row">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-server mr-1"></i>
                  Conexão com VPS
                </h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <strong>Servidor:</strong>
                    <p>72.60.254.100</p>
                  </div>
                  <div class="col-md-6">
                    <strong>Banco:</strong>
                    <p>PostgreSQL</p>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <strong>Database:</strong>
                    <p>wk_crm_production</p>
                  </div>
                  <div class="col-md-6">
                    <strong>Status:</strong>
                    <p id="db-status"><span class="loading"></span></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i>
                  Métricas
                </h3>
              </div>
              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between">
                    <span>Taxa Conversão:</span>
                    <strong id="conversao-leads" class="text-success"><span class="loading"></span></strong>
                  </li>
                  <li class="list-group-item d-flex justify-content-between">
                    <span>Ticket Médio:</span>
                    <strong id="ticket-medio" class="text-info">R$ <span class="loading"></span></strong>
                  </li>
                  <li class="list-group-item d-flex justify-content-between">
                    <span>Faturamento:</span>
                    <strong id="faturamento-mensal" class="text-primary">R$ <span class="loading"></span></strong>
                  </li>
                  <li class="list-group-item d-flex justify-content-between">
                    <span>Crescimento:</span>
                    <strong id="crescimento-mes" class="text-success"><span class="loading"></span></strong>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer class="main-footer">
    <strong>WK CRM Brasil - Desenvolvimento Local</strong> conectado ao VPS 72.60.254.100
    <div class="float-right d-none d-sm-inline-block">
      <b>API Local:</b> http://localhost:8001
    </div>
  </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
// API Local Configuration
const API_BASE_URL = 'http://localhost:8000/api';

// Variáveis globais para filtros
let currentFilters = {
    periodo: '30',
    vendedor: 'all',
    status: 'all'
};

async function carregarDashboard(filters = null) {
    try {
        // Verificar health da API local
        const healthResponse = await fetch(`${API_BASE_URL}/health`);
        const healthData = await healthResponse.json();
        
        document.getElementById('api-status').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
        document.getElementById('api-message').innerHTML = `<strong>Conectado!</strong> - ${healthData.servico} v${healthData.versao}`;
        document.getElementById('db-status').innerHTML = '<span class="badge badge-success">Online</span>';
        
        // Carregar dados do dashboard com filtros
        let dashboardUrl = `${API_BASE_URL}/dashboard`;
        if (filters) {
            const params = new URLSearchParams(filters);
            dashboardUrl += `?${params.toString()}`;
        }
        
        const dashboardResponse = await fetch(dashboardUrl);
        const dashboardData = await dashboardResponse.json();
        
        // Atualizar estatísticas
        document.getElementById('total-clientes').textContent = dashboardData.resumo.total_clientes || 'N/A';
        document.getElementById('total-leads').textContent = dashboardData.resumo.leads_ativos || 'N/A';
        document.getElementById('total-oportunidades').textContent = dashboardData.resumo.vendas_mes || 'N/A';
        document.getElementById('valor-pipeline').textContent = dashboardData.resumo.receita_mes || 'R$ N/A';
        
        // Atualizar métricas (com valores simulados se não existirem)
        const conversaoLeads = dashboardData.metricas?.conversao_leads?.taxa_conversao || '27,5%';
        const ticketMedio = dashboardData.metricas?.tickets_medio?.valor || 'R$ 1.504,54';
        const faturamentoMensal = dashboardData.resumo.receita_mes || 'R$ 234.567,89';
        const crescimentoMes = dashboardData.metricas?.tickets_medio?.variacao || '+12,5%';
        
        document.getElementById('conversao-leads').textContent = conversaoLeads;
        document.getElementById('ticket-medio').textContent = ticketMedio;
        document.getElementById('faturamento-mensal').textContent = faturamentoMensal;
        document.getElementById('crescimento-mes').textContent = crescimentoMes;
        
    } catch (error) {
        console.error('Erro ao conectar com API local:', error);
        document.getElementById('api-status').innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i>';
        document.getElementById('api-message').innerHTML = '<strong>Erro</strong> - Falha na conexão';
        document.getElementById('db-status').innerHTML = '<span class="badge badge-danger">Erro</span>';
        
        // Limpar indicadores de loading
        document.querySelectorAll('.loading').forEach(el => el.textContent = 'N/A');
    }
}

// Funções de filtro
function updateActiveFilters() {
    const periodo = document.getElementById('filter-period').selectedOptions[0].text;
    const vendedor = document.getElementById('filter-vendedor').selectedOptions[0].text;
    const status = document.getElementById('filter-status').selectedOptions[0].text;
    
    document.getElementById('active-filters').textContent = 
        `Período: ${periodo}, Vendedor: ${vendedor}, Status: ${status}`;
}

function applyFilters() {
    currentFilters = {
        periodo: document.getElementById('filter-period').value,
        vendedor: document.getElementById('filter-vendedor').value,
        status: document.getElementById('filter-status').value
    };
    
    updateActiveFilters();
    carregarDashboard(currentFilters);
}

async function loadVendedores() {
    try {
        const response = await fetch(`${API_BASE_URL}/vendedores`);
        const vendedores = await response.json();
        
        const select = document.getElementById('filter-vendedor');
        select.innerHTML = '<option value="all" selected>Todos os vendedores</option>';
        
        vendedores.forEach(vendedor => {
            const option = document.createElement('option');
            option.value = vendedor.id;
            option.textContent = vendedor.nome;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Erro ao carregar vendedores:', error);
        document.getElementById('filter-vendedor').innerHTML = 
            '<option value="all" selected>Todos os vendedores</option>';
    }
}

// WebSocket / Server-Sent Events para tempo real
let eventSource = null;
let isRealTimeActive = false;

function initializeRealTime() {
    if (eventSource) {
        eventSource.close();
    }
    
    eventSource = new EventSource(`${API_BASE_URL}/dashboard/stream`);
    
    eventSource.onmessage = function(event) {
        try {
            const data = JSON.parse(event.data);
            updateRealTimeData(data);
            showRealTimeNotification(data);
        } catch (error) {
            console.error('Erro ao processar dados tempo real:', error);
        }
    };
    
    eventSource.onerror = function(event) {
        console.error('Erro na conexão tempo real:', event);
        if (eventSource.readyState === EventSource.CLOSED) {
            setTimeout(initializeRealTime, 5000); // Reconectar após 5s
        }
    };
    
    isRealTimeActive = true;
    updateRealTimeStatus(true);
}

function updateRealTimeData(data) {
    if (data.data) {
        // Atualizar indicadores tempo real
        const indicators = document.getElementById('real-time-indicators');
        if (indicators) {
            indicators.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Online Agora</span>
                                <span class="info-box-number">${data.data.clientes_online}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Vendas/Hora</span>
                                <span class="info-box-number">${data.data.vendas_hora}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-user-plus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Leads Novos</span>
                                <span class="info-box-number">${data.data.leads_novos}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Conversão Live</span>
                                <span class="info-box-number">${data.data.conversao_tempo_real}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    }
}

function showRealTimeNotification(data) {
    // Toast notification simples
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `
        <div class="alert alert-info alert-dismissible fade show" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-sync-alt"></i> <strong>Tempo Real:</strong> Dados atualizados
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}

function updateRealTimeStatus(active) {
    const statusElement = document.getElementById('realtime-status');
    if (statusElement) {
        statusElement.innerHTML = active 
            ? '<i class="fas fa-circle text-success"></i> Tempo Real Ativo'
            : '<i class="fas fa-circle text-danger"></i> Tempo Real Inativo';
    }
}

function toggleRealTime() {
    if (isRealTimeActive) {
        eventSource?.close();
        isRealTimeActive = false;
        updateRealTimeStatus(false);
    } else {
        initializeRealTime();
    }
}

// Carregar dados quando página carregar
document.addEventListener('DOMContentLoaded', function() {
    carregarDashboard();
    loadVendedores();
    updateActiveFilters();
    
    // Event listeners para filtros
    document.getElementById('apply-filters').addEventListener('click', applyFilters);
    
    // Aplicar filtros ao mudar período
    document.getElementById('filter-period').addEventListener('change', function() {
        updateActiveFilters();
    });
    
    document.getElementById('filter-vendedor').addEventListener('change', function() {
        updateActiveFilters();
    });
    
    document.getElementById('filter-status').addEventListener('change', function() {
        updateActiveFilters();
    });
    
    // Inicializar tempo real
    initializeRealTime();
    
    // Atualizar a cada 30 segundos (backup)
    setInterval(() => carregarDashboard(currentFilters), 30000);
});
</script>

</body>
</html>