<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WK CRM - Clientes</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.7/css/dataTables.bootstrap4.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="/admin" class="nav-link">Dashboard</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="/admin/clientes" class="nav-link active">Clientes</a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="/admin" class="brand-link">
      <div class="brand-image img-circle elevation-3 d-flex align-items-center justify-content-center" style="width: 33px; height: 33px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid rgba(255,255,255,0.3);">
        <i class="fas fa-users" style="color: #667eea; font-size: 18px;"></i>
      </div>
      <span class="brand-text font-weight-light">WK CRM Local</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item"><a href="/admin" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="/admin/clientes" class="nav-link active"><i class="nav-icon fas fa-users"></i><p>Clientes (CRUD)</p></a></li>
          <li class="nav-item"><a href="/api/leads" target="_blank" class="nav-link"><i class="nav-icon fas fa-user-plus"></i><p>API Leads</p></a></li>
          <li class="nav-item"><a href="/api/oportunidades" target="_blank" class="nav-link"><i class="nav-icon fas fa-chart-line"></i><p>API Oportunidades</p></a></li>
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
            <h1 class="m-0">Clientes</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
              <li class="breadcrumb-item active">Clientes</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
          <div id="alertContainer"></div>
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-users"></i> Lista de Clientes</h3>
            <button class="btn btn-primary" id="btnNovo"><i class="fas fa-plus"></i> Novo Cliente</button>
          </div>
          <div class="card-body">
            <table id="clientesTable" class="table table-striped table-bordered" style="width:100%">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nome</th>
                  <th>Email</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="clienteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cliente</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="clienteForm">
            <input type="hidden" id="clienteId">
            <div class="form-group">
              <label for="clienteNome">Nome</label>
              <input type="text" class="form-control" id="clienteNome" required>
            </div>
            <div class="form-group">
              <label for="clienteEmail">Email</label>
              <input type="email" class="form-control" id="clienteEmail" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
          <button type="button" class="btn btn-primary" id="btnSalvar">Salvar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script>
  const API_URL = '/api/clientes';
  let table;

  function unwrapData(res){
    if(res && typeof res === 'object' && 'data' in res) return res.data;
    return res;
  }

  function showAlert(message, type='danger'){
    const html = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>`;
    $('#alertContainer').html(html);
  }

  function loadTable(){
    table = $('#clientesTable').DataTable({
      destroy: true,
      ajax: {
        url: API_URL,
        dataSrc: function(json){ return unwrapData(json) || []; },
        error: function(xhr, status, err){
          console.error('Falha ao carregar /api/clientes:', status, err);
          let dica = 'Verifique se a API está respondendo em <a href="/api/health" target="_blank">/api/health</a>.';
          dica += '<br>Se estiver usando Docker, confirme que o serviço Postgres está ativo e que o Laravel está apontando para DB_HOST=postgres.';
          dica += '<br>Se estiver rodando fora do Docker, ajuste o .env para DB_HOST=127.0.0.1 e a senha correta.';
          showAlert(`Erro ao carregar clientes. ${dica}`, 'warning');
        },
        timeout: 10000
      },
      columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: null, render: row => `
            <button class='btn btn-sm btn-info mr-1' onclick='editCliente("${row.id}")'><i class="fas fa-edit"></i></button>
            <button class='btn btn-sm btn-danger' onclick='deleteCliente("${row.id}")'><i class="fas fa-trash"></i></button>`
        }
      ]
    });
  }

  function reload(){ table.ajax.reload(null, false); }

  window.editCliente = function(id){
    $.ajax({ url: `${API_URL}/${id}`, type: 'GET', timeout: 10000 })
      .done(function(res){
        const c = unwrapData(res) || res;
        $('#clienteId').val(c.id || '');
        $('#clienteNome').val(c.name || c.nome || '');
        $('#clienteEmail').val(c.email || '');
        $('#clienteModal').modal('show');
      })
      .fail(function(xhr, status, err){
        console.error('Erro ao buscar cliente:', status, err);
        showAlert('Não foi possível carregar o cliente para edição.');
      });
  }

  window.deleteCliente = function(id){
    if(!confirm('Confirma exclusão?')) return;
    $.ajax({ url: `${API_URL}/${id}`, type: 'DELETE', timeout: 10000 })
      .done(() => reload())
      .fail(function(xhr, status, err){
        console.error('Erro ao excluir:', status, err);
        showAlert('Erro ao excluir cliente.');
      });
  }

  $('#btnNovo').on('click', () => {
    $('#clienteId').val('');
    $('#clienteNome').val('');
    $('#clienteEmail').val('');
    $('#clienteModal').modal('show');
  });

  $('#btnSalvar').on('click', () => {
    const id = $('#clienteId').val();
    const payload = { nome: $('#clienteNome').val(), email: $('#clienteEmail').val() };
    if(id){
      $.ajax({
        url: `${API_URL}/${id}`,
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        timeout: 10000
      }).done(() => { $('#clienteModal').modal('hide'); reload(); })
        .fail(function(xhr, status, err){
          console.error('Erro ao atualizar:', status, err);
          showAlert('Erro ao atualizar cliente. Verifique a conexão com a API/DB.');
        });
    } else {
      $.ajax({
        url: API_URL,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        timeout: 10000
      }).done(() => { $('#clienteModal').modal('hide'); reload(); })
        .fail(function(xhr, status, err){
          console.error('Erro ao criar:', status, err);
          showAlert('Erro ao criar cliente. Verifique a conexão com a API/DB.');
        });
    }
  });

  $(function(){ loadTable(); });
</script>
</body>
</html>
