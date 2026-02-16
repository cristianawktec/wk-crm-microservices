<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Relatório de Acessos - WK CRM</title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background:#f7f7f7; padding:24px; }
    .card { max-width:900px; margin:0 auto; background:#fff; border:1px solid #eee; border-radius:8px; }
    .header { padding:20px 24px; border-bottom:2px solid #4f46e5; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; font-weight:bold; font-size:18px; }
    .content { padding:20px 24px; color:#333; font-size:14px; }
    .table { width:100%; border-collapse:collapse; margin:16px 0; font-size:12px; }
    .table th { background:#f3f4f6; padding:12px; text-align:left; font-weight:600; border:1px solid #e5e7eb; }
    .table td { padding:12px; border:1px solid #e5e7eb; }
    .table tr:nth-child(even) { background:#f9fafb; }
    .table tbody tr:hover { background:#f3f4f6; }
    .footer { padding:16px 24px; border-top:1px solid #eee; font-size:12px; color:#666; background:#f9fafb; }
    .info-box { background:#eff6ff; border-left:4px solid #3b82f6; padding:12px; margin:12px 0; border-radius:4px; }
    .info-box strong { color:#1e40af; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">📊 Relatório de Acessos ao Sistema WK CRM</div>
    
    <div class="content">
      <div class="info-box">
        <strong>Data/Hora do Relatório:</strong> {{ $timestamp }}<br>
        <strong>Solicitado por:</strong> {{ $triggered_by }}<br>
        <strong>Total de Registros:</strong> {{ count($audits) }}
      </div>

      <p>Abaixo estão os registros de acesso ao sistema:</p>

      <table class="table">
        <thead>
          <tr>
            <th>Data/Hora</th>
            <th>Usuário</th>
            <th>Email</th>
            <th>IP</th>
            <th>Navegador</th>
            <th>SO</th>
            <th>Dispositivo</th>
            <th>Rota</th>
          </tr>
        </thead>
        <tbody>
          @forelse($audits as $audit)
            <tr>
              <td>{{ $audit->logged_in_at ? \Carbon\Carbon::parse($audit->logged_in_at)->format('d/m/Y H:i:s') : '-' }}</td>
              <td>{{ $audit->user->name ?? $audit->user_id ?? '-' }}</td>
              <td>{{ $audit->user->email ?? '-' }}</td>
              <td><code>{{ $audit->ip_address ?? '-' }}</code></td>
              <td>{{ $audit->user_agent_browser ?? '-' }}</td>
              <td>{{ $audit->user_agent_os ?? '-' }}</td>
              <td>{{ $audit->device_type ?? '-' }}</td>
              <td><code>{{ $audit->route ?? '-' }}</code></td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="text-align:center; color:#999;">Nenhum registro encont no período</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      <div class="info-box">
        ⚠️ <strong>Aviso de Segurança:</strong> Este relatório contém informações sensíveis. Não compartilhe com pessoas não autorizadas.
      </div>
    </div>
    
    <div class="footer">
      WK CRM • Relatório Automático de Segurança • Este é um email automático, não responda.
    </div>
  </div>
</body>
</html>
