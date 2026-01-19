# Fix Login Issues - painel.consultoriawk.com + credentials

Write-Host "🔧 Corrigindo problemas de login..." -ForegroundColor Cyan

# 1. Criar configuração Nginx para painel.consultoriawk.com
Write-Host "`n1️⃣ Criando configuração Nginx para painel.consultoriawk.com..." -ForegroundColor Yellow

$nginxConfig = @"
server {
    listen 80;
    listen [::]:80;
    server_name painel.consultoriawk.com;
    return 301 https://`$server_name`$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name painel.consultoriawk.com;

    # Usar certificado do api.consultoriawk.com (mesmo domínio base)
    ssl_certificate /etc/letsencrypt/live/api.consultoriawk.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.consultoriawk.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Root directory para Angular Admin
    root /var/www/consultoriawk-crm/admin;
    index index.html;

    # Logs
    access_log /var/log/nginx/painel.consultoriawk.com.access.log;
    error_log /var/log/nginx/painel.consultoriawk.com.error.log;

    # Angular SPA routing
    location / {
        try_files `$uri `$uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/atom+xml image/svg+xml;
}
"@

# Criar arquivo temporário local
$nginxConfig | Out-File -Encoding utf8 -FilePath "$env:TEMP\painel.consultoriawk.com.nginx.conf"

Write-Host "Arquivo Nginx criado localmente: $env:TEMP\painel.consultoriawk.com.nginx.conf" -ForegroundColor Green

# 2. Comandos para executar na VPS
Write-Host "`n2️⃣ Comandos para executar na VPS:" -ForegroundColor Yellow

$commands = @"
# 1. Copiar arquivo para o servidor
scp -i `"`$env:USERPROFILE\.ssh\contabo_vps`" `"$env:TEMP\painel.consultoriawk.com.nginx.conf`" root@72.60.254.100:/tmp/

# 2. SSH e executar correções
ssh -i `"`$env:USERPROFILE\.ssh\contabo_vps`" root@72.60.254.100 << 'EOF'

# Mover configuração para Nginx
sudo mv /tmp/painel.consultoriawk.com.nginx.conf /etc/nginx/sites-available/
sudo ln -sf /etc/nginx/sites-available/painel.consultoriawk.com.nginx.conf /etc/nginx/sites-enabled/

# Testar configuração
sudo nginx -t

# Recarregar Nginx
sudo systemctl reload nginx

# Verificar usuários no banco
echo "=== Usuários no banco ==="
docker exec -it wk_postgres psql -U wk_user -d wk_main -c "SELECT id, name, email FROM users ORDER BY id;"

# Resetar senha do admin
echo "=== Resetando senha do admin ==="
docker exec -it wk_crm_laravel php -r "require 'vendor/autoload.php'; \`$app=require 'bootstrap/app.php'; \`$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); \`$u=App\\Models\\User::where('email','admin@consultoriawk.com')->first(); if(\`$u){ \`$u->password=bcrypt('Admin@123'); \`$u->save(); echo 'Senha atualizada para Admin@123\n'; } else { echo 'Usuário admin@consultoriawk.com não encontrado - verifique o email correto\n'; }"

# Limpar cache do Laravel
echo "=== Limpando cache ==="
docker exec -it wk_crm_laravel php artisan cache:clear
docker exec -it wk_crm_laravel php artisan config:clear
docker exec -it wk_crm_laravel php artisan route:clear

echo "=== Correções aplicadas ==="

EOF
"@

Write-Host $commands -ForegroundColor Cyan

Write-Host "`n3️⃣ Executando comandos..." -ForegroundColor Yellow
Invoke-Expression $commands

Write-Host "`n✅ Correções concluídas!" -ForegroundColor Green
Write-Host "`n📋 Próximos passos:" -ForegroundColor Yellow
Write-Host "  1. Limpar localStorage do navegador (F12 → Application → Local Storage → Limpar tudo)" -ForegroundColor White
Write-Host "  2. Hard refresh (Ctrl+F5)" -ForegroundColor White
Write-Host "  3. Tentar login:" -ForegroundColor White
Write-Host "     - Painel: https://painel.consultoriawk.com" -ForegroundColor Cyan
Write-Host "     - App: https://app.consultoriawk.com/login" -ForegroundColor Cyan
Write-Host "     - Email: admin@consultoriawk.com" -ForegroundColor Cyan
Write-Host "     - Senha: Admin@123" -ForegroundColor Cyan
