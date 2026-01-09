<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Notificação WK CRM</title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background:#f7f7f7; padding:24px; }
    .card { max-width:640px; margin:0 auto; background:#fff; border:1px solid #eee; border-radius:8px; }
    .header { padding:20px 24px; border-bottom:1px solid #eee; font-weight:bold; font-size:18px; }
    .content { padding:20px 24px; color:#333; font-size:14px; }
    .footer { padding:16px 24px; border-top:1px solid #eee; font-size:12px; color:#666; }
    .btn { display:inline-block; padding:10px 16px; background:#4f46e5; color:#fff; text-decoration:none; border-radius:6px; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">{{ $title }}</div>
    <div class="content">
      <p style="margin:0 0 12px">{{ $body }}</p>
      @if(!empty($action_url))
        <p>
          <a class="btn" href="{{ $action_url }}" target="_blank">Abrir no WK CRM</a>
        </p>
      @endif
      <p style="margin-top:16px; color:#777">Gerado em {{ $created_at }}</p>
    </div>
    <div class="footer">
      WK CRM • Este é um email automático, não responda.
    </div>
  </div>
</body>
</html>
