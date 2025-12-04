#!/bin/bash

# 🔧 Script de Correção API Routing VPS
# Corrige problema de 404 na API Laravel na VPS

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_step() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_title() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}================================${NC}"
}

print_title "🔧 Correção API Routing - WK CRM VPS"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script deve ser executado como root"
    exit 1
fi

PROJECT_DIR="/opt/wk-crm/wk-crm-laravel"

print_step "📊 Diagnóstico inicial..."

# 1. Verificar se o diretório existe
if [ ! -d "$PROJECT_DIR" ]; then
    print_error "Diretório Laravel não encontrado: $PROJECT_DIR"
    exit 1
fi

print_step "✅ Diretório Laravel encontrado"

# 2. Verificar se index.php existe
if [ ! -f "$PROJECT_DIR/public/index.php" ]; then
    print_error "Arquivo index.php não encontrado em $PROJECT_DIR/public/"
    exit 1
fi

print_step "✅ Arquivo index.php encontrado"

# 3. Verificar permissões
print_step "🔧 Corrigindo permissões..."
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR/storage"
chmod -R 755 "$PROJECT_DIR/bootstrap/cache"

# 4. Limpar caches do Laravel
print_step "🧹 Limpando caches do Laravel..."
cd "$PROJECT_DIR"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 5. Recriar caches otimizados
print_step "⚡ Recriando caches otimizados..."
php artisan config:cache
php artisan route:cache

# 6. Verificar configuração do Nginx
print_step "🌐 Verificando configuração do Nginx..."

# Backup da configuração atual
NGINX_CONFIG="/etc/nginx/sites-available/api.consultoriawk.com"
if [ -f "$NGINX_CONFIG" ]; then
    cp "$NGINX_CONFIG" "${NGINX_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"
    print_step "✅ Backup da configuração Nginx criado"
fi

# Aplicar configuração corrigida
print_step "📝 Aplicando configuração Nginx corrigida..."

cat > "$NGINX_CONFIG" << 'EOF'
server {
    listen 80;
    listen 443 ssl http2;
    server_name api.consultoriawk.com;

    # SSL configuration
    ssl_certificate /etc/letsencrypt/live/api.consultoriawk.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.consultoriawk.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Redirect HTTP to HTTPS
    if ($scheme != "https") {
        return 301 https://$host$request_uri;
    }

    # Document root
    root /opt/wk-crm/wk-crm-laravel/public;
    index index.php index.html;

    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Additional FastCGI settings
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        fastcgi_param HTTP_PROXY "";
        
        # CORS headers
        # NOTE: In this repo CORS is centralized at the reverse proxy (infrastructure/nginx/nginx.conf).
        # If running this site standalone, uncomment/adapt the lines below to enable CORS at site level:
        # add_header Access-Control-Allow-Origin * always;
        # add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
        # add_header Access-Control-Allow-Headers "Authorization, Content-Type, Accept, X-Requested-With" always;
    }

    # Handle OPTIONS requests for CORS
    if ($request_method = OPTIONS) {
        # Preflight handling retained; centralized proxy will provide CORS headers.
        add_header Content-Length 0;
        add_header Content-Type text/plain;
        return 200;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    # Log files
    access_log /var/log/nginx/api.consultoriawk.com.access.log;
    error_log /var/log/nginx/api.consultoriawk.com.error.log;

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to sensitive files
    location ~* \.(log|sql|conf)$ {
        deny all;
    }
}
EOF

# 7. Testar configuração do Nginx
print_step "🧪 Testando configuração do Nginx..."
if nginx -t; then
    print_step "✅ Configuração do Nginx válida"
else
    print_error "❌ Configuração do Nginx inválida!"
    print_step "🔄 Restaurando backup..."
    mv "${NGINX_CONFIG}.backup."* "$NGINX_CONFIG"
    exit 1
fi

# 8. Reiniciar serviços
print_step "🔄 Reiniciando serviços..."
systemctl restart php8.2-fpm
systemctl reload nginx

# 9. Verificar se serviços estão rodando
print_step "🔍 Verificando status dos serviços..."
if systemctl is-active --quiet nginx; then
    print_step "✅ Nginx está ativo"
else
    print_error "❌ Nginx não está ativo"
fi

if systemctl is-active --quiet php8.2-fpm; then
    print_step "✅ PHP-FPM está ativo"
else
    print_error "❌ PHP-FPM não está ativo"
fi

# 10. Testes de funcionamento
print_step "🧪 Executando testes de funcionamento..."

# Teste 1: Index.php direto
print_step "Teste 1: Acessando index.php diretamente..."
if curl -s -o /dev/null -w "%{http_code}" "http://localhost/index.php" | grep -q "200"; then
    print_step "✅ Index.php acessível"
else
    print_warning "⚠️ Index.php pode não estar acessível"
fi

# Teste 2: API Health
print_step "Teste 2: Testando API Health..."
HEALTH_RESPONSE=$(curl -s "http://localhost/api/health" | head -c 100)
if [[ $HEALTH_RESPONSE == *"OK"* ]]; then
    print_step "✅ API Health funcionando: $HEALTH_RESPONSE"
else
    print_warning "⚠️ API Health com problemas: $HEALTH_RESPONSE"
fi

# Teste 3: Teste externo (se possível)
print_step "Teste 3: Testando acesso externo..."
EXTERNAL_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "https://api.consultoriawk.com/api/health" || echo "000")
if [ "$EXTERNAL_RESPONSE" = "200" ]; then
    print_step "✅ API externa funcionando (HTTP $EXTERNAL_RESPONSE)"
else
    print_warning "⚠️ API externa retorna HTTP $EXTERNAL_RESPONSE"
fi

print_title "📊 RESUMO DA CORREÇÃO"

print_step "✅ Permissões corrigidas"
print_step "✅ Caches do Laravel limpos e recriados"
print_step "✅ Configuração Nginx atualizada"
print_step "✅ Serviços reiniciados"

echo ""
print_step "🌐 URLs para testar:"
echo "   - https://api.consultoriawk.com/api/health"
echo "   - https://api.consultoriawk.com/api/dashboard"
echo "   - https://api.consultoriawk.com/"

echo ""
print_step "📝 Para monitorar logs:"
echo "   - tail -f /var/log/nginx/api.consultoriawk.com.error.log"
echo "   - tail -f $PROJECT_DIR/storage/logs/laravel.log"

echo ""
print_step "🎉 Correção de API Routing concluída!"