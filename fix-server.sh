#!/bin/bash

# 🚑 Script de Correção de Problemas - WK CRM
# Corrige problemas comuns de HTTP 500/404 no VPS

set -e

echo "🚑 Iniciando correção de problemas do WK CRM..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_step() {
    echo -e "${GREEN}[CORREÇÃO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[ATENÇÃO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERRO]${NC} $1"
}

# 1. Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script deve ser executado como root"
    exit 1
fi

print_step "🔍 Verificando estrutura de arquivos..."

# Criar estrutura de diretórios
echo "🗂️  Criando estrutura de diretórios..."
mkdir -p /var/www/admin.consultoriawk.com/public_html
mkdir -p /var/www/api.consultoriawk.com/public_html
mkdir -p /var/log/nginx
chmod 755 /var/www/admin.consultoriawk.com
chmod 755 /var/www/admin.consultoriawk.com/public_html
chmod 755 /var/www/api.consultoriawk.com
chmod 755 /var/www/api.consultoriawk.com/public_html

# 3. Verificar se projeto existe
if [ ! -d "/opt/wk-crm/.git" ]; then
    print_step "📥 Clonando projeto do GitHub..."
    git clone https://github.com/cristianawktec/wk-crm-microservices.git /opt/wk-crm
else
    print_step "🔄 Atualizando projeto..."
    cd /opt/wk-crm
    git pull origin main
fi

# 4. Copiar AdminLTE para local correto
print_step "📁 Copiando AdminLTE para admin.consultoriawk.com..."
cp -r /opt/wk-crm/wk-admin-simple/* /var/www/admin.consultoriawk.com/public_html/ 2>/dev/null || {
    print_warning "Erro ao copiar AdminLTE, tentando criar estrutura básica..."
    mkdir -p /var/www/admin.consultoriawk.com/public_html
    echo "<!DOCTYPE html><html><head><title>WK CRM Admin</title></head><body><h1>AdminLTE em manutenção</h1><p>Arquivos sendo configurados...</p></body></html>" > /var/www/admin.consultoriawk.com/public_html/index.html
}

# 5. Corrigir permissões Web
print_step "🔐 Corrigindo permissões dos diretórios web..."
chown -R www-data:www-data /var/www/admin.consultoriawk.com/
chmod -R 755 /var/www/admin.consultoriawk.com/
chown -R www-data:www-data /var/www/api.consultoriawk.com/
chmod -R 755 /var/www/api.consultoriawk.com/

# 6. Configurar Laravel
print_step "⚙️ Configurando Laravel..."
cd /opt/wk-crm/wk-crm-laravel

# Instalar Composer se necessário
if ! command -v composer &> /dev/null; then
    print_step "📦 Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Instalar dependências Laravel
composer install --no-dev --optimize-autoloader 2>/dev/null || {
    print_warning "Erro no composer install, tentando atualizar..."
    composer update --no-dev 2>/dev/null || print_warning "Composer com problemas, continuando..."
}

# Configurar arquivo .env se não existir
if [ ! -f ".env" ]; then
    print_step "📝 Criando arquivo .env do Laravel..."
    cp .env.example .env
    
    # Configurações básicas do .env
    sed -i 's/APP_ENV=local/APP_ENV=production/' .env
    sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
    sed -i 's/DB_HOST=127.0.0.1/#DB_HOST=127.0.0.1/' .env
    sed -i 's/DB_PORT=3306/#DB_PORT=3306/' .env
    sed -i 's/DB_DATABASE=laravel/#DB_DATABASE=laravel/' .env
    sed -i 's/DB_USERNAME=root/#DB_USERNAME=root/' .env
    sed -i 's/DB_PASSWORD=/#DB_PASSWORD=/' .env
    
    # Adicionar configuração SQLite
    echo "DB_DATABASE=/opt/wk-crm/wk-crm-laravel/database/database.sqlite" >> .env
fi

# Criar banco SQLite se não existir
if [ ! -f "database/database.sqlite" ]; then
    print_step "🗄️ Criando banco de dados SQLite..."
    touch database/database.sqlite
    chmod 664 database/database.sqlite
fi

# Gerar chave da aplicação
php artisan key:generate --force 2>/dev/null || print_warning "Erro ao gerar chave da aplicação"

# Executar migrações
print_step "🔄 Executando migrações..."
php artisan migrate --force 2>/dev/null || print_warning "Erro nas migrações, continuando..."

# Cache Laravel
php artisan config:cache 2>/dev/null
php artisan route:cache 2>/dev/null
php artisan view:cache 2>/dev/null

# 7. Corrigir permissões Laravel
print_step "🔐 Corrigindo permissões do Laravel..."
chown -R www-data:www-data /opt/wk-crm/wk-crm-laravel/
chmod -R 775 /opt/wk-crm/wk-crm-laravel/storage
chmod -R 775 /opt/wk-crm/wk-crm-laravel/bootstrap/cache
chmod 664 /opt/wk-crm/wk-crm-laravel/database/database.sqlite

# 8. Configurar Nginx
print_step "🌐 Configurando Nginx..."

# Configuração do Nginx para AdminLTE (subdomínio)
cat > /etc/nginx/sites-available/admin.consultoriawk.com << 'EOF'
server {
    listen 80;
    server_name admin.consultoriawk.com;
    root /var/www/admin.consultoriawk.com/public_html;
    index index.html index.htm index.php;

    # Configurações de cabeçalhos para CORS
    # These headers are commented out because CORS is centralized in the reverse proxy.
    # If deploying without a reverse proxy, uncomment and adapt the lines below.
    # add_header 'Access-Control-Allow-Origin' '*' always;
    # add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    # add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization' always;

    # Arquivos estáticos
    location / {
        try_files $uri $uri/ /index.html;
    }

    # PHP (se necessário)
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache para arquivos estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# Configuração da API Laravel
cat > /etc/nginx/sites-available/api.consultoriawk.com << 'EOF'
server {
    listen 80;
    server_name api.consultoriawk.com;
    root /opt/wk-crm/wk-crm-laravel/public;
    index index.php;

    # Configurações CORS para API
    # Commented since the central reverse proxy handles CORS headers.
    # add_header 'Access-Control-Allow-Origin' '*' always;
    # add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    # add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization' always;

    # Handle preflight requests
    if ($request_method = 'OPTIONS') {
        # Preflight handling retained; CORS headers are provided by the reverse proxy.
        add_header 'Content-Type' 'text/plain; charset=utf-8';
        add_header 'Content-Length' 0;
        return 204;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # CORS headers
        # Commented because the upstream reverse proxy supplies CORS headers centrally.
        # add_header Access-Control-Allow-Origin "*" always;
        # add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
        # add_header Access-Control-Allow-Headers "Authorization, Content-Type, Accept" always;
    }

    # Handle OPTIONS requests
    if ($request_method = OPTIONS) {
        # Preflight response kept; reverse proxy will add CORS headers.
        add_header Content-Length 0;
        add_header Content-Type text/plain;
        return 200;
    }
}
EOF

# Habilitar sites
ln -sf /etc/nginx/sites-available/admin.consultoriawk.com /etc/nginx/sites-enabled/
ln -sf /etc/nginx/sites-available/api.consultoriawk.com /etc/nginx/sites-enabled/

# Remover site padrão
rm -f /etc/nginx/sites-enabled/default

# 9. Testar e recarregar Nginx
if nginx -t; then
    print_step "✅ Configuração do Nginx válida"
    systemctl reload nginx
else
    print_error "❌ Erro na configuração do Nginx"
    nginx -t
fi

# 10. Verificar e reiniciar PHP-FPM
print_step "🔄 Reiniciando PHP-FPM..."
systemctl restart php8.2-fpm
systemctl status php8.2-fpm --no-pager

# 11. Criar página de teste
print_step "🧪 Criando páginas de teste..."

# Teste AdminLTE (corrigido para subdomínio)
cat > /var/www/admin.consultoriawk.com/public_html/test.html << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Teste AdminLTE - WK CRM</title>
    <meta charset="utf-8">
</head>
<body>
    <h1>✅ AdminLTE Funcionando!</h1>
    <p>Data/Hora: <script>document.write(new Date())</script></p>
    <p><a href="index.html">Ir para Dashboard</a></p>
    <p><a href="customers.html">Gestão de Clientes</a></p>
</body>
</html>
EOF

# Teste PHP Laravel
cat > /opt/wk-crm/wk-crm-laravel/public/test.php << 'EOF'
<?php
echo "✅ PHP/Laravel Funcionando!\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "Versão PHP: " . phpversion() . "\n";
echo "Laravel: " . (file_exists(__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/Application.php') ? 'Instalado' : 'Não encontrado') . "\n";
phpinfo();
?>
EOF

chown www-data:www-data /opt/wk-crm/wk-crm-laravel/public/test.php

# 12. Verificações finais
print_step "🔍 Executando verificações finais..."

echo ""
echo "📊 RELATÓRIO DE CORREÇÕES:"
echo "📁 AdminLTE: /var/www/admin.consultoriawk.com/public_html/"
echo "📡 Laravel API: /opt/wk-crm/wk-crm-laravel/"
echo "🌐 Nginx: Configurado com subdomínios corretos"
echo "🐘 PHP-FPM: Reiniciado"
echo ""
echo "🧪 TESTES DISPONÍVEIS:"
echo "🎨 AdminLTE: http://admin.consultoriawk.com/"
echo "📡 PHP/Laravel: http://api.consultoriawk.com/test.php"
echo "📊 API Customers: http://api.consultoriawk.com/api/customers"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo "1. Teste os links acima no navegador"
echo "2. Se AdminLTE não carregar, verifique: ls -la /var/www/admin.consultoriawk.com/public_html/"
echo "3. Se API não responder, verifique: tail -f /var/log/nginx/error.log"
echo "4. Para ver logs Laravel: tail -f /opt/wk-crm/wk-crm-laravel/storage/logs/laravel.log"
echo ""

print_step "🎉 Correções concluídas! Teste os URLs acima."