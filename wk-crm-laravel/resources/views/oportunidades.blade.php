<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oportunidades - WK CRM</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="/admin">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="/admin" class="brand-link">
            <span class="brand-text font-weight-light">WK CRM</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="/admin/clientes" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Clientes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/leads" class="nav-link">
                            <i class="nav-icon fas fa-user-plus"></i>
                            <p>Leads</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/oportunidades" class="nav-link active">
                            <i class="nav-icon fas fa-briefcase"></i>
                            <p>Oportunidades</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Oportunidades</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                            <li class="breadcrumb-item active">Oportunidades</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list"></i> Lista de Oportunidades</h3>
                        <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#opportunityModal" onclick="resetForm()">
                            <i class="fas fa-plus"></i> Nova Oportunidade
                        </button>
                    </div>
                    <div class="card-body">
                        <table id="opportunitiesTable" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Valor</th>
                                <th>Data Prevista</th>
                                <th>Status</th>
                                <th>Lead/Cliente</th>
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
</div>

<!-- Modal -->
<div class="modal fade" id="opportunityModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nova Oportunidade</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="opportunityForm">
                <div class="modal-body">
                    <input type="hidden" id="opportunityId">
                    
                    <div class="form-group">
                        <label for="titulo">Título *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valor">Valor (R$) *</label>
                                <input type="number" class="form-control" id="valor" name="valor" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_prevista_fechamento">Data Prevista de Fechamento</label>
                                <input type="date" class="form-control" id="data_prevista_fechamento" name="data_prevista_fechamento">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="open">Aberta</option>
                                    <option value="negotiation">Em Negociação</option>
                                    <option value="won">Ganha</option>
                                    <option value="lost">Perdida</option>
                                    <option value="cancelled">Cancelada</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lead_id">Lead</label>
                                <select class="form-control" id="lead_id" name="lead_id">
                                    <option value="">Selecione um Lead (opcional)</option>
                                </select>
                                <small class="form-text text-muted">Informe Lead OU Cliente</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cliente_id">Cliente</label>
                        <select class="form-control" id="cliente_id" name="cliente_id">
                            <option value="">Selecione um Cliente (opcional)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    const API_URL = '/api/oportunidades';
    const API_LEADS = '/api/leads';
    const API_CLIENTES = '/api/clientes';
    let table;

    // Inicializar DataTable
    table = $('#opportunitiesTable').DataTable({
        ajax: {
            url: API_URL,
            dataSrc: function(json) {
                return unwrapData(json);
            }
        },
        columns: [
            { 
                data: 'id',
                render: function(data) {
                    return data.substring(0, 8) + '...';
                }
            },
            { data: 'title' },
            { 
                data: 'amount',
                render: function(data) {
                    return 'R$ ' + parseFloat(data).toLocaleString('pt-BR', {minimumFractionDigits: 2});
                }
            },
            { 
                data: 'expected_close_date',
                render: function(data) {
                    return data ? new Date(data).toLocaleDateString('pt-BR') : '-';
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    const badges = {
                        'open': '<span class="badge badge-info">Aberta</span>',
                        'negotiation': '<span class="badge badge-warning">Negociação</span>',
                        'won': '<span class="badge badge-success">Ganha</span>',
                        'lost': '<span class="badge badge-danger">Perdida</span>',
                        'cancelled': '<span class="badge badge-secondary">Cancelada</span>'
                    };
                    return badges[data] || data;
                }
            },
            {
                data: null,
                render: function(data) {
                    if (data.lead) {
                        return '<small><i class="fas fa-user-plus"></i> ' + data.lead.name + '</small>';
                    }
                    if (data.cliente) {
                        return '<small><i class="fas fa-user"></i> ' + data.cliente.name + '</small>';
                    }
                    return '-';
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-info" onclick="editOpportunity('${data.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteOpportunity('${data.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
        }
    });

    // Unwrap Laravel pagination
    function unwrapData(response) {
        if (response.data && Array.isArray(response.data)) {
            return response.data;
        }
        return response;
    }

    // Submit form
    $('#opportunityForm').on('submit', function(e) {
        e.preventDefault();
        
        const id = $('#opportunityId').val();
        const data = {
            titulo: $('#titulo').val(),
            descricao: $('#descricao').val(),
            valor: parseFloat($('#valor').val()),
            data_prevista_fechamento: $('#data_prevista_fechamento').val() || null,
            status: $('#status').val(),
            lead_id: $('#lead_id').val() || null,
            cliente_id: $('#cliente_id').val() || null
        };

        const method = id ? 'PUT' : 'POST';
        const url = id ? `${API_URL}/${id}` : API_URL;

        $.ajax({
            url: url,
            method: method,
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                $('#opportunityModal').modal('hide');
                table.ajax.reload();
                alert('Oportunidade salva com sucesso!');
            },
            error: function(xhr) {
                let message = 'Erro ao salvar oportunidade';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    message += '\n' + Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                alert(message);
            }
        });
    });

    // Edit opportunity
    window.editOpportunity = function(id) {
        $.get(`${API_URL}/${id}`, function(response) {
            const data = response.data || response;
            
            $('#modalTitle').text('Editar Oportunidade');
            $('#opportunityId').val(data.id);
            $('#titulo').val(data.title);
            $('#descricao').val(data.description);
            $('#valor').val(data.amount);
            $('#data_prevista_fechamento').val(data.expected_close_date);
            $('#status').val(data.status);
            $('#lead_id').val(data.lead_id || '');
            $('#cliente_id').val(data.cliente_id || '');
            
            $('#opportunityModal').modal('show');
        });
    };

    // Delete opportunity
    window.deleteOpportunity = function(id) {
        if (confirm('Tem certeza que deseja excluir esta oportunidade?')) {
            $.ajax({
                url: `${API_URL}/${id}`,
                method: 'DELETE',
                success: function() {
                    table.ajax.reload();
                    alert('Oportunidade excluída com sucesso!');
                },
                error: function() {
                    alert('Erro ao excluir oportunidade');
                }
            });
        }
    };

    // Reset form
    window.resetForm = function() {
        $('#modalTitle').text('Nova Oportunidade');
        $('#opportunityForm')[0].reset();
        $('#opportunityId').val('');
        $('#status').val('open');
        // Recarregar combos ao abrir modal
        loadCombos();
    };

    // Carregar opções de Leads e Clientes
    function loadCombos() {
        // Leads (usa paginação da API, pega primeiro page 1)
        $.get(API_LEADS, function(resp) {
            const items = resp.data || resp || [];
            const $lead = $('#lead_id');
            const current = $lead.val();
            $lead.empty().append('<option value="">Selecione um Lead (opcional)</option>');
            items.forEach(function(x) {
                $lead.append(`<option value="${x.id}">${x.name} (${x.email || ''})</option>`);
            });
            if (current) $lead.val(current);
        });

        // Clientes (sem paginação customizada aqui; se api for paginada, ajuste conforme retorno)
        $.get(API_CLIENTES, function(resp) {
            const items = resp.data || resp || [];
            const $cli = $('#cliente_id');
            const current = $cli.val();
            $cli.empty().append('<option value="">Selecione um Cliente (opcional)</option>');
            items.forEach(function(x) {
                // suporta tanto name quanto nome
                const nome = x.name || x.nome || 'Cliente';
                $cli.append(`<option value="${x.id}">${nome} (${x.email || ''})</option>`);
            });
            if (current) $cli.val(current);
        });
    }

    // Prepara os combos já ao carregar a página
    loadCombos();
});
</script>
</body>
</html>
