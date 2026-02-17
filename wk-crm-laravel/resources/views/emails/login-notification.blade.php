<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Novo Login - WK CRM</title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background:#f7f7f7; padding:24px; }
    .card { max-width:600px; margin:0 auto; background:#fff; border:1px solid #eee; border-radius:8px; }
    .header { padding:20px 24px; border-bottom:2px solid #4f46e5; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; font-weight:bold; font-size:18px; }
    .content { padding:20px 24px; color:#333; font-size:14px; }
    .info-box { background:#f3f4f6; border-left:4px solid #3b82f6; padding:12px; margin:12px 0; border-radius:4px; }
    .info-box strong { color:#1e40af; display:block; margin-bottom:4px; }
    .info-box p { margin:4px 0; }
    .footer { padding:16px 24px; border-top:1px solid #eee; font-size:12px; color:#666; background:#f9fafb; }
    .alert { background:#fef3c7; border-left:4px solid #f59e0b; padding:12px; margin:16px 0; border-radius:4px; }
    .alert-title { color:#d97706; font-weight:bold; margin-bottom:4px; }
    .label { display:inline-block; background:#e5e7eb; padding:2px 6px; margin:2px 0; border-radius:3px; font-size:11px; font-weight:600; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">🔐 Novo Login Detectado</div>
    
    <div class="content">
      <p>Olá,</p>
      <p>Um novo login foi detectado em sua conta WK CRM:</p>

      <div class="info-box">
        <strong>👤 Usuário:</strong>
        <p>{{ $user_name }} ({{ $user_email }})</p>
        
        <strong>📅 Data/Hora:</strong>
        <p>{{ $login_time }}</p>
        
        <strong>🌐 Endereço IP:</strong>
        <p><code>{{ $ip_address }}</code></p>
        
        <strong>🖥️ Detalhes do Dispositivo:</strong>
        <p>
          <span class="label">{{ $browser }}</span>
          <span class="label">{{ $platform }}</span>
          <span class="label">{{ $device }}</span>
        </p>
      </div>

      <div class="alert">
        <div class="alert-title">⚠️ Verificação de Segurança</div>
        <p>Se você não realizou este login, por favor, altere sua senha imediatamente acessando: <strong>{{ config('app.url') }}/profile</strong></p>
      </div>

      <p style="margin-top:16px; font-size:12px; color:#666;">
        Este é um email automático de notificação de segurança do WK CRM. Não responda a este email.
      </p>
    </div>
    
    <div class="footer">
      WK CRM • Notificação de Segurança • {{ $timestamp }}
    </div>
  </div>
</body>
</html>
