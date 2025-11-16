<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WK CRM - Leads</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.7/css/dataTables.bootstrap4.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="/admin" class="nav-link">Dashboard</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="/admin/leads" class="nav-link active">Leads</a>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="/admin" class="brand-link">
      <div class="brand-image img-circle elevation-3 d-flex align-items-center justify-content-center" style="width: 33px; height: 33px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid rgba(255,255,255,0.3);">
        <i class="fas fa-user-plus" style="color: #667eea; font-size: 18px;"></i>
      </div>
      <span class="brand-text font-weight-light">WK CRM Local</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item"><a href="/admin" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="/admin/clientes" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Clientes (CRUD)</p></a></li>
          <li class="nav-item"><a href="/admin/leads" class="nav-link active"><i class="nav-icon fas fa-user-plus"></i><p>Leads (CRUD)</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1 class="m-0">Leads</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
              <li class="breadcrumb-item active">Leads</li>
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
            <h3 class="card-title"><i class="fas fa-user-plus"></i> Lista de Leads</h3>
            <button class="btn btn-primary" id="btnNovo"><i class="fas fa-plus"></i> Novo Lead</button>
          </div>
          <div class="card-body">
            <table id="leadsTable" class="table table-striped table-bordered" style="width:100%">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nome</th>
                  <th>Email</th>
                  <th>Telefone</th>
                  <th>Status</th>
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

  <div class="modal fade" id="leadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lead</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="leadForm">
            <input type="hidden" id="leadId">
            <div class="form-group">
              <label for="leadNome">Nome</label>
              <input type="text" class="form-control" id="leadNome" required>
            </div>
            <div class="form-group">
              <label for="leadEmail">Email</label>
              <input type="email" class="form-control" id="leadEmail">
            </div>
            <div class="form-group">
              <label for="leadTelefone">Telefone</label>
              <input type="text" class="form-control" id="leadTelefone">
            </div>
            <div class="form-group">
              <label for="leadStatus">Status</label>
              <select id="leadStatus" class="form-control">
                <option value="new">Novo</option>
                <option value="contacted">Contactado</option>
                <option value="qualified">Qualificado</option>
                <option value="converted">Convertido</option>
                <option value="lost">Perdido</option>
              </select>
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
  const API_URL = '/api/leads';
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
    table = $('#leadsTable').DataTable({
      destroy: true,
      ajax: {
        url: API_URL,
        dataSrc: function(json){ return unwrapData(json) || []; },
        error: function(xhr, status, err){
          console.error('Falha ao carregar /api/leads:', status, err);
          let dica = 'Verifique se a API está respondendo em <a href="/api/health" target="_blank">/api/health</a>.';
          showAlert(`Erro ao carregar leads. ${dica}`, 'warning');
        },
        timeout: 10000
      },
      columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: 'phone' },
        { data: 'status' },
        { data: null, render: row => `
            <button class='btn btn-sm btn-info mr-1' onclick='editLead("${row.id}")'><i class="fas fa-edit"></i></button>
            <button class='btn btn-sm btn-danger' onclick='deleteLead("${row.id}")'><i class="fas fa-trash"></i></button>`
        }
      ]
    });
  }

  function reload(){ table.ajax.reload(null, false); }

  window.editLead = function(id){
    $.ajax({ url: `${API_URL}/${id}`, type: 'GET', timeout: 10000 })
      .done(function(res){
        const c = unwrapData(res) || res;
        $('#leadId').val(c.id || '');
        $('#leadNome').val(c.name || c.nome || '');
        $('#leadEmail').val(c.email || '');
        $('#leadTelefone').val(c.phone || c.telefone || '');
        $('#leadStatus').val(c.status || 'new');
        $('#leadModal').modal('show');
      })
      .fail(function(xhr, status, err){
        console.error('Erro ao buscar lead:', status, err);
        showAlert('Não foi possível carregar o lead para edição.');
      });
  }

  window.deleteLead = function(id){
    if(!confirm('Confirma exclusão?')) return;
    $.ajax({ url: `${API_URL}/${id}`, type: 'DELETE', timeout: 10000 })
      .done(() => reload())
      .fail(function(xhr, status, err){
        console.error('Erro ao excluir:', status, err);
        showAlert('Erro ao excluir lead.');
      });
  }

  $('#btnNovo').on('click', () => {
    $('#leadId').val('');
    $('#leadNome').val('');
    $('#leadEmail').val('');
    $('#leadTelefone').val('');
    $('#leadStatus').val('new');
    $('#leadModal').modal('show');
  });

  $('#btnSalvar').on('click', () => {
    const id = $('#leadId').val();
    const payload = { nome: $('#leadNome').val(), email: $('#leadEmail').val(), telefone: $('#leadTelefone').val(), status: $('#leadStatus').val() };
    if(id){
      $.ajax({
        url: `${API_URL}/${id}`,
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        timeout: 10000
      }).done(() => { $('#leadModal').modal('hide'); reload(); })
        .fail(function(xhr, status, err){
          console.error('Erro ao atualizar:', status, err);
          showAlert('Erro ao atualizar lead.');
        });
    } else {
      $.ajax({
        url: API_URL,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        timeout: 10000
      }).done(() => { $('#leadModal').modal('hide'); reload(); })
        .fail(function(xhr, status, err){
          console.error('Erro ao criar:', status, err);
          showAlert('Erro ao criar lead.');
        });
    }
  });

  $(function(){ loadTable(); });
</script>
</body>
</html>
