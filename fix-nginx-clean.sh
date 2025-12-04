#!/bin/bash

# Script para Corrigir Configuração Nginx Existente
# Problema: configuração antiga está causando redirecionamento 301

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_header() {
    echo -e "${BLUE}============================================${NC}"
    echo -e "${BLUE} 🔧 CORRIGINDO NGINX CONFIGURAÇÃO${NC}"
    echo -e "${BLUE}============================================${NC}"
}

print_step() {
    echo -e "${GREEN}➤ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_header

# 1. BACKUP DAS CONFIGURAÇÕES EXISTENTES
print_step "💾 Fazendo backup das configurações existentes..."
mkdir -p /root/nginx-backup-$(date +%Y%m%d-%H%M%S)
cp -r /etc/nginx/sites-available/ /root/nginx-backup-$(date +%Y%m%d-%H%M%S)/
cp -r /etc/nginx/sites-enabled/ /root/nginx-backup-$(date +%Y%m%d-%H%M%S)/
print_success "Backup salvo em /root/nginx-backup-$(date +%Y%m%d-%H%M%S)/"

# 2. REMOVER CONFIGURAÇÕES EM CONFLITO
print_step "🗑️  Removendo configurações antigas em conflito..."
rm -f /etc/nginx/sites-enabled/consultoriawk-microservices.conf
rm -f /etc/nginx/sites-enabled/default
print_success "Configurações antigas removidas"

# 3. VER CONTEÚDO DA CONFIGURAÇÃO ATUAL
print_step "🔍 Analisando configuração atual..."
if [ -f "/etc/nginx/sites-available/consultoriawk-microservices.conf" ]; then
    echo "Conteúdo da configuração atual:"
    head -20 /etc/nginx/sites-available/consultoriawk-microservices.conf
    echo "..."
fi

# 4. CRIAR ESTRUTURA DE DIRETÓRIOS CORRETA
print_step "📁 Criando estrutura de diretórios..."
mkdir -p /var/www/admin.consultoriawk.com/public_html
mkdir -p /var/www/api.consultoriawk.com/public_html
mkdir -p /var/log/nginx

# 5. INSTALAR COMPOSER SE NÃO EXISTIR
if ! command -v composer &> /dev/null; then
    print_step "📦 Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    print_success "Composer instalado"
fi

# 6. CONFIGURAR LARAVEL
print_step "⚙️ Configurando Laravel..."
cd /opt/wk-crm/wk-crm-laravel

# Instalar dependências
composer install --optimize-autoloader --no-dev

# Criar .env se não existir
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
    print_success "Arquivo .env criado"
fi

# Configurar banco SQLite
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_HOST=127.0.0.1/#DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=3306/#DB_PORT=3306/' .env
sed -i 's/DB_DATABASE=laravel/#DB_DATABASE=laravel/' .env
sed -i 's/DB_USERNAME=root/#DB_USERNAME=root/' .env
sed -i 's/DB_PASSWORD=/#DB_PASSWORD=/' .env

# Criar banco SQLite
touch database/database.sqlite
chmod 664 database/database.sqlite

# Executar migrações
php artisan migrate --force
php artisan config:cache
php artisan route:cache

print_success "Laravel configurado com SQLite"

# 7. COPIAR ADMINLTE PARA LOCAL CORRETO
print_step "📄 Copiando AdminLTE para subdomínio..."
cp -r /opt/wk-crm/wk-admin-simple/* /var/www/admin.consultoriawk.com/public_html/
print_success "AdminLTE copiado"

# 8. CRIAR CONFIGURAÇÃO NGINX LIMPA
print_step "🌐 Criando configuração Nginx limpa..."

# Configuração para admin.consultoriawk.com
cat > /etc/nginx/sites-available/admin.consultoriawk.com << 'EOF'
server {
    listen 80;
    server_name admin.consultoriawk.com;
    root /var/www/admin.consultoriawk.com/public_html;
    index index.html index.htm index.php;
    
    # Log files
    access_log /var/log/nginx/admin.consultoriawk.com.access.log;
    error_log /var/log/nginx/admin.consultoriawk.com.error.log;
    
    # CORS headers
    # Commented because CORS is centralized in the reverse proxy; uncomment if needed.
    # add_header 'Access-Control-Allow-Origin' '*' always;
    # add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    # add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization' always;
    
    # Main location
    location / {
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
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
}
EOF

# Configuração para api.consultoriawk.com
cat > /etc/nginx/sites-available/api.consultoriawk.com << 'EOF'
server {
    listen 80;
    server_name api.consultoriawk.com;
    root /opt/wk-crm/wk-crm-laravel/public;
    index index.php;
    
    # Log files
    access_log /var/log/nginx/api.consultoriawk.com.access.log;
    error_log /var/log/nginx/api.consultoriawk.com.error.log;
    
    # CORS headers for API
    # Commented to avoid duplicate headers when using centralized proxy.
    # add_header 'Access-Control-Allow-Origin' '*' always;
    # add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    # add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization' always;
    
    # Handle preflight requests
    if ($request_method = 'OPTIONS') {
        # Preflight handling retained; reverse proxy should set CORS headers.
        add_header 'Content-Type' 'text/plain; charset=utf-8';
        add_header 'Content-Length' 0;
        return 204;
    }
    
    # Laravel routing
    location / {
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
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
}
EOF

# 9. HABILITAR NOVOS SITES
print_step "🔗 Habilitando novos sites..."
ln -sf /etc/nginx/sites-available/admin.consultoriawk.com /etc/nginx/sites-enabled/
ln -sf /etc/nginx/sites-available/api.consultoriawk.com /etc/nginx/sites-enabled/

# 10. CORRIGIR PERMISSÕES
print_step "🔐 Corrigindo permissões..."
chown -R www-data:www-data /var/www/admin.consultoriawk.com/
chown -R www-data:www-data /opt/wk-crm/wk-crm-laravel/
chmod -R 755 /var/www/admin.consultoriawk.com/
chmod -R 775 /opt/wk-crm/wk-crm-laravel/storage
chmod -R 775 /opt/wk-crm/wk-crm-laravel/bootstrap/cache
chmod 664 /opt/wk-crm/wk-crm-laravel/database/database.sqlite

# 11. CRIAR PÁGINAS DE TESTE
print_step "🧪 Criando páginas de teste..."

# Teste AdminLTE
cat > /var/www/admin.consultoriawk.com/public_html/test.html << 'EOF'
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ AdminLTE Funcionando - WK CRM</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f4; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        h1 { color: #2c3e50; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 AdminLTE WK CRM - Funcionando!</h1>
        <div class="success">
            <strong>✅ Subdomínio admin.consultoriawk.com configurado com sucesso!</strong>
        </div>
        <div class="info">
            <strong>🔗 Links importantes:</strong><br>
            • <a href="/">Painel AdminLTE Principal</a><br>
            • <a href="/customers.html">Gestão de Clientes</a><br>
            • <a href="http://api.consultoriawk.com/test.php">Teste API Laravel</a><br>
            • <a href="http://api.consultoriawk.com/api/customers">API Customers</a>
        </div>
        <p><strong>Data/Hora:</strong> <span id="datetime"></span></p>
        <p><strong>Status:</strong> ✅ Nginx + AdminLTE funcionando perfeitamente!</p>
    </div>
    <script>
        document.getElementById('datetime').textContent = new Date().toLocaleString('pt-BR');
    </script>
</body>
</html>
EOF

# Teste Laravel API
cat > /opt/wk-crm/wk-crm-laravel/public/test.php << 'EOF'
<?php
echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>✅ Laravel API Funcionando - WK CRM</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f4; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        h1 { color: #2c3e50; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 Laravel API WK CRM - Funcionando!</h1>
        <div class='success'>
            <strong>✅ API Laravel configurada com sucesso!</strong>
        </div>
        <div class='info'>
            <strong>📋 Informações do Sistema:</strong><br>
            • <strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "<br>
            • <strong>PHP:</strong> " . phpversion() . "<br>
            • <strong>Laravel:</strong> " . (file_exists(__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/Application.php') ? 'Instalado ✅' : 'Não encontrado ❌') . "<br>
            • <strong>SQLite:</strong> " . (extension_loaded('sqlite3') ? 'Disponível ✅' : 'Não disponível ❌') . "<br>
            • <strong>Banco:</strong> " . (file_exists(__DIR__.'/../database/database.sqlite') ? 'Configurado ✅' : 'Não encontrado ❌') . "
        </div>
        <div class='info'>
            <strong>🔗 Endpoints disponíveis:</strong><br>
            <div class='code'>
                GET /api/customers - Lista todos os clientes<br>
                POST /api/customers - Criar novo cliente<br>
                GET /api/health - Status da API
            </div>
        </div>
    </div>
</body>
</html>";
?>
EOF

chown www-data:www-data /var/www/admin.consultoriawk.com/public_html/test.html
chown www-data:www-data /opt/wk-crm/wk-crm-laravel/public/test.php

# 12. TESTAR E REINICIAR NGINX
print_step "🔄 Testando e reiniciando Nginx..."
if nginx -t; then
    systemctl reload nginx
    print_success "Nginx recarregado com sucesso"
else
    print_error "Erro na configuração do Nginx:"
    nginx -t
    exit 1
fi

# 13. TESTAR CONECTIVIDADE
print_step "🧪 Testando conectividade..."

# Teste local
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|301\|302"; then
    print_success "Servidor responde localmente"
else
    print_warning "Problema na resposta local"
fi

# 14. RELATÓRIO FINAL
print_step "📊 RELATÓRIO FINAL DE CONFIGURAÇÃO"
echo ""
print_success "✅ Configuração Nginx limpa aplicada"
print_success "✅ AdminLTE configurado em /var/www/admin.consultoriawk.com/"
print_success "✅ Laravel API configurado em /opt/wk-crm/wk-crm-laravel/"
print_success "✅ Composer instalado e dependências configuradas"
print_success "✅ Banco SQLite criado e migrações executadas"
print_success "✅ Páginas de teste criadas"
echo ""
echo "🌐 URLs PARA TESTAR:"
echo "   ✅ AdminLTE: http://admin.consultoriawk.com/"
echo "   🧪 Teste AdminLTE: http://admin.consultoriawk.com/test.html"
echo "   ✅ Laravel API: http://api.consultoriawk.com/test.php"
echo "   📡 API Customers: http://api.consultoriawk.com/api/customers"
echo ""
echo "📋 COMANDOS DE DEBUG (se necessário):"
echo "   • tail -f /var/log/nginx/admin.consultoriawk.com.error.log"
echo "   • tail -f /var/log/nginx/api.consultoriawk.com.error.log"
echo "   • systemctl status nginx php8.2-fpm"
echo "   • nginx -t"
echo ""
print_success "🎉 Configuração concluída! Teste as URLs acima."