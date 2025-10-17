#!/bin/bash

# 🚀 Script de Deploy Automático - WK CRM
# Hostinger VPS Deployment Script

set -e  # Exit on error

echo "🚀 Iniciando deploy do WK CRM..."

# Configurações
PROJECT_DIR="/opt/wk-crm"
WEB_DIR="/var/www/html"
ADMIN_DIR="$WEB_DIR/admin"
REPO_URL="https://github.com/cristianawktec/wk-crm-microservices.git"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
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

# 1. Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script deve ser executado como root"
    exit 1
fi

# 2. Backup dos arquivos atuais (se existir)
if [ -d "$PROJECT_DIR" ]; then
    print_step "Fazendo backup dos arquivos atuais..."
    cp -r "$PROJECT_DIR" "${PROJECT_DIR}_backup_$(date +%Y%m%d_%H%M%S)" 2>/dev/null || true
fi

# 3. Clonar ou atualizar repositório
if [ -d "$PROJECT_DIR/.git" ]; then
    print_step "Atualizando repositório..."
    cd "$PROJECT_DIR"
    git fetch origin
    git reset --hard origin/main
    git pull origin main
else
    print_step "Clonando repositório..."
    rm -rf "$PROJECT_DIR"
    git clone "$REPO_URL" "$PROJECT_DIR"
    cd "$PROJECT_DIR"
fi

# 4. Configurar AdminLTE Frontend
print_step "Configurando AdminLTE Frontend..."

# Criar diretório admin se não existir
mkdir -p "$ADMIN_DIR"

# Copiar arquivos AdminLTE
cp -r "$PROJECT_DIR/wk-admin-simple/"* "$ADMIN_DIR/"

# Ajustar permissões
chown -R www-data:www-data "$ADMIN_DIR"
chmod -R 755 "$ADMIN_DIR"

print_step "✅ AdminLTE configurado em: $ADMIN_DIR"

# 5. Configurar Laravel API
print_step "Configurando Laravel API..."

cd "$PROJECT_DIR/wk-crm-laravel"

# Instalar dependências do Composer
if command -v composer &> /dev/null; then
    composer install --optimize-autoloader --no-dev
else
    print_warning "Composer não encontrado. Instalando..."
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --optimize-autoloader --no-dev
fi

# Configurar ambiente Laravel
if [ ! -f ".env" ]; then
    cp .env.example .env
    print_step "Arquivo .env criado. CONFIGURE AS VARIÁVEIS DE AMBIENTE!"
fi

# Gerar chave da aplicação
php artisan key:generate --force

# Executar migrações (se database estiver configurado)
print_step "Executando migrações do banco de dados..."
if php artisan migrate:status &>/dev/null; then
    php artisan migrate --force
    print_step "✅ Migrações executadas com sucesso"
else
    print_warning "⚠️  Migrações não puderam ser executadas. Verifique configuração do banco de dados no .env"
fi

# Cache do Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissões Laravel
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

print_step "✅ Laravel API configurada"

# 6. Configurar Nginx
print_step "Configurando Nginx..."

# Copiar configuração do nginx
cp "$PROJECT_DIR/laravel_nginx.conf" "/etc/nginx/sites-available/api.consultoriawk.com"

# Habilitar site
ln -sf "/etc/nginx/sites-available/api.consultoriawk.com" "/etc/nginx/sites-enabled/"

# Testar configuração do nginx
if nginx -t; then
    print_step "✅ Configuração do Nginx válida"
    systemctl reload nginx
    print_step "✅ Nginx recarregado"
else
    print_error "❌ Erro na configuração do Nginx"
    exit 1
fi

# 7. Verificar e reiniciar PHP-FPM
print_step "Verificando PHP-FPM..."
if systemctl is-active --quiet php8.2-fpm; then
    systemctl restart php8.2-fpm
    print_step "✅ PHP-FPM reiniciado"
else
    print_warning "PHP-FPM não está ativo. Tentando iniciar..."
    systemctl start php8.2-fpm
fi

# 8. Resumo do Deploy
print_step "🎉 Deploy concluído com sucesso!"
echo ""
echo "📊 RESUMO:"
echo "🎨 AdminLTE: https://consultoriawk.com/admin/"
echo "📡 API Laravel: https://api.consultoriawk.com/api/"
echo "📁 Projeto: $PROJECT_DIR"
echo "🌐 Admin Dir: $ADMIN_DIR"
echo ""
echo "🔧 PRÓXIMOS PASSOS:"
echo "1. Configure o arquivo .env do Laravel: $PROJECT_DIR/wk-crm-laravel/.env"
echo "2. Configure SSL se necessário: certbot --nginx -d api.consultoriawk.com"
echo "3. Teste a API: curl -X GET https://api.consultoriawk.com/api/customers"
echo "4. Acesse o AdminLTE: https://consultoriawk.com/admin/"
echo ""

# 9. Testes básicos
print_step "Executando testes básicos..."

# Teste AdminLTE
if curl -s -o /dev/null -w "%{http_code}" "http://localhost/admin/" | grep -q "200"; then
    print_step "✅ AdminLTE acessível"
else
    print_warning "⚠️  AdminLTE pode não estar acessível"
fi

# Teste API Laravel (básico)
if curl -s -o /dev/null -w "%{http_code}" "http://localhost:80" | grep -q "200\|302"; then
    print_step "✅ Servidor web respondendo"
else
    print_warning "⚠️  Servidor web pode ter problemas"
fi

echo ""
print_step "🚀 Deploy finalizado! Verifique os logs em caso de problemas:"
echo "   - Nginx: tail -f /var/log/nginx/error.log"
echo "   - Laravel: tail -f $PROJECT_DIR/wk-crm-laravel/storage/logs/laravel.log"
echo "   - PHP-FPM: tail -f /var/log/php8.2-fpm.log"