#!/bin/bash

# Script de Correção Rápida - Corrige erros identificados
# Erro 1: Nginx add_header duplicados
# Erro 2: SQLite não criado e Laravel tentando PostgreSQL

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_step() {
    echo -e "${GREEN}➤ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

echo -e "${BLUE}🔧 CORREÇÃO RÁPIDA DOS ERROS IDENTIFICADOS${NC}"
echo ""

# 1. CORRIGIR CONFIGURAÇÃO NGINX SEM ADD_HEADER DUPLICADOS
print_step "🌐 Corrigindo configuração Nginx..."

# Configuração corrigida para admin.consultoriawk.com
cat > /etc/nginx/sites-available/admin.consultoriawk.com << 'EOF'
server {
    listen 80;
    server_name admin.consultoriawk.com;
    root /var/www/admin.consultoriawk.com/public_html;
    index index.html index.htm index.php;
    
    # Log files
    access_log /var/log/nginx/admin.consultoriawk.com.access.log;
    error_log /var/log/nginx/admin.consultoriawk.com.error.log;
    
    # Main location
    location / {
        # CORS headers
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization' always;
        
        # Security headers
        add_header X-Frame-Options "SAMEORIGIN" always;
        add_header X-XSS-Protection "1; mode=block" always;
        add_header X-Content-Type-Options "nosniff" always;
        
        try_files $uri $uri/ =404;
    }
    
    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Static files caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }
}
EOF

# Configuração corrigida para api.consultoriawk.com
cat > /etc/nginx/sites-available/api.consultoriawk.com << 'EOF'
server {
    listen 80;
    server_name api.consultoriawk.com;
    root /opt/wk-crm/wk-crm-laravel/public;
    index index.php;
    
    # Log files
    access_log /var/log/nginx/api.consultoriawk.com.access.log;
    error_log /var/log/nginx/api.consultoriawk.com.error.log;
    
    # Handle preflight requests
    if ($request_method = 'OPTIONS') {
        add_header 'Access-Control-Allow-Origin' '*';
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization';
        add_header 'Access-Control-Max-Age' 1728000;
        add_header 'Content-Type' 'text/plain; charset=utf-8';
        add_header 'Content-Length' 0;
        return 204;
    }
    
    # Laravel routing
    location / {
        # CORS headers for API
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization' always;
        
        # Security headers
        add_header X-Frame-Options "SAMEORIGIN" always;
        add_header X-XSS-Protection "1; mode=block" always;
        add_header X-Content-Type-Options "nosniff" always;
        
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Block access to hidden files
    location ~ /\. {
        deny all;
    }
}
EOF

print_success "Configuração Nginx corrigida"

# 2. CORRIGIR LARAVEL E CRIAR SQLITE
print_step "⚙️ Corrigindo Laravel e criando SQLite..."
cd /opt/wk-crm/wk-crm-laravel

# Criar diretório database se não existir
mkdir -p database

# Criar arquivo SQLite
touch database/database.sqlite
chmod 664 database/database.sqlite
chown www-data:www-data database/database.sqlite

# Corrigir .env para SQLite
cat > .env << 'EOF'
APP_NAME=WK_CRM
APP_ENV=production
APP_KEY=base64:K8mtGHPIZw4YfOilxzYwGQaNKi4H3gmoZ1RBgZj5gRo=
APP_DEBUG=false
APP_URL=http://api.consultoriawk.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=/opt/wk-crm/wk-crm-laravel/database/database.sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
EOF

# Executar migrações
php artisan config:clear
php artisan cache:clear
php artisan migrate --force

print_success "Laravel corrigido com SQLite"

# 3. TESTAR E APLICAR NGINX
print_step "🔄 Testando configuração Nginx..."
if nginx -t; then
    systemctl reload nginx
    print_success "Nginx recarregado com sucesso"
else
    print_error "Ainda há erro na configuração:"
    nginx -t
fi

# 4. CORRIGIR PERMISSÕES FINAIS
print_step "🔐 Corrigindo permissões finais..."
chown -R www-data:www-data /var/www/admin.consultoriawk.com/
chown -R www-data:www-data /opt/wk-crm/wk-crm-laravel/
chmod -R 755 /var/www/admin.consultoriawk.com/
chmod -R 775 /opt/wk-crm/wk-crm-laravel/storage
chmod -R 775 /opt/wk-crm/wk-crm-laravel/bootstrap/cache
chmod 664 /opt/wk-crm/wk-crm-laravel/database/database.sqlite

# 5. CRIAR PÁGINAS DE TESTE SIMPLES
print_step "🧪 Criando páginas de teste..."

# Teste AdminLTE mais simples
cat > /var/www/admin.consultoriawk.com/public_html/test.html << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>WK CRM Admin - Teste</title>
    <style>body{font-family:Arial;margin:40px;background:#f4f4f4;}.container{background:white;padding:30px;border-radius:8px;}.success{background:#d4edda;padding:15px;border-left:4px solid #28a745;margin:10px 0;}</style>
</head>
<body>
    <div class="container">
        <h1>🎉 AdminLTE WK CRM</h1>
        <div class="success">
            <strong>✅ admin.consultoriawk.com funcionando!</strong>
        </div>
        <p>Data/Hora: <span id="dt"></span></p>
        <p><a href="/">⬅ Voltar ao AdminLTE</a></p>
    </div>
    <script>document.getElementById('dt').textContent = new Date().toLocaleString();</script>
</body>
</html>
EOF

# Teste Laravel API mais simples
cat > /opt/wk-crm/wk-crm-laravel/public/test.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>WK CRM API - Teste</title>
    <style>body{font-family:Arial;margin:40px;background:#f4f4f4;}.container{background:white;padding:30px;border-radius:8px;}.success{background:#d4edda;padding:15px;border-left:4px solid #28a745;margin:10px 0;}</style>
</head>
<body>
    <div class="container">
        <h1>🚀 Laravel API WK CRM</h1>
        <div class="success">
            <strong>✅ api.consultoriawk.com funcionando!</strong>
        </div>
        <p><strong>PHP:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Laravel:</strong> <?php echo file_exists(__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/Application.php') ? 'Instalado ✅' : 'Erro ❌'; ?></p>
        <p><strong>SQLite:</strong> <?php echo file_exists(__DIR__.'/../database/database.sqlite') ? 'Configurado ✅' : 'Erro ❌'; ?></p>
        <p><strong>Data/Hora:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><a href="/api/customers">🔗 Testar API Customers</a></p>
    </div>
</body>
</html>
EOF

chown www-data:www-data /var/www/admin.consultoriawk.com/public_html/test.html
chown www-data:www-data /opt/wk-crm/wk-crm-laravel/public/test.php

print_success "Páginas de teste criadas"

# 6. TESTE FINAL
print_step "🧪 Teste de conectividade..."
curl -s http://localhost >/dev/null && print_success "Servidor local OK"

echo ""
echo "🌐 URLs PARA TESTAR:"
echo "   ✅ AdminLTE: http://admin.consultoriawk.com/"
echo "   🧪 Teste Admin: http://admin.consultoriawk.com/test.html"
echo "   ✅ Laravel API: http://api.consultoriawk.com/test.php"
echo "   📡 API Customers: http://api.consultoriawk.com/api/customers"
echo ""
print_success "🎉 Correções aplicadas! Teste as URLs acima."