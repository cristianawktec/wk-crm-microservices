#!/bin/bash

# Script de Diagnóstico Avançado do Servidor VPS
# Para identificar e corrigir problemas específicos

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_header() {
    echo -e "${BLUE}============================================${NC}"
    echo -e "${BLUE} 🔍 DIAGNÓSTICO SERVIDOR WK CRM${NC}"
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

check_command() {
    if command -v $1 &> /dev/null; then
        print_success "$1 está instalado"
        return 0
    else
        print_error "$1 não está instalado"
        return 1
    fi
}

print_header

# 1. VERIFICAÇÃO DO SISTEMA
print_step "📋 Verificando informações do sistema..."
echo "OS: $(cat /etc/os-release | grep PRETTY_NAME | cut -d'=' -f2 | tr -d '\"')"
echo "Kernel: $(uname -r)"
echo "Uptime: $(uptime -p)"
echo "Memória livre: $(free -h | grep Mem | awk '{print $7}')"
echo "Espaço em disco: $(df -h / | tail -1 | awk '{print $4}') livre"
echo ""

# 2. VERIFICAÇÃO DE SERVIÇOS
print_step "🔍 Verificando status dos serviços..."

services=("nginx" "php8.2-fpm" "apache2")
for service in "${services[@]}"; do
    if systemctl is-active --quiet $service; then
        print_success "$service está ativo"
    else
        print_warning "$service não está ativo"
        echo "Tentando status detalhado:"
        systemctl status $service --no-pager -l
    fi
done
echo ""

# 3. VERIFICAÇÃO DE PACOTES
print_step "📦 Verificando pacotes instalados..."
packages=("nginx" "php8.2-fpm" "php8.2-cli" "php8.2-mysql" "php8.2-xml" "php8.2-mbstring" "php8.2-curl" "composer")
for package in "${packages[@]}"; do
    if dpkg -l | grep -q "^ii.*$package"; then
        print_success "$package está instalado"
    else
        print_error "$package NÃO está instalado"
    fi
done
echo ""

# 4. VERIFICAÇÃO DE PORTAS
print_step "🌐 Verificando portas em uso..."
netstat_output=$(netstat -tuln 2>/dev/null || ss -tuln)
echo "$netstat_output" | grep ":80\|:443\|:22\|:3306\|:9000"
echo ""

# 5. VERIFICAÇÃO DE ARQUIVOS DE CONFIGURAÇÃO
print_step "📄 Verificando arquivos de configuração..."

# Nginx
if [ -f "/etc/nginx/nginx.conf" ]; then
    print_success "nginx.conf encontrado"
    nginx -t 2>&1 | head -5
else
    print_error "nginx.conf não encontrado"
fi

# PHP-FPM
if [ -f "/etc/php/8.2/fpm/php-fpm.conf" ]; then
    print_success "php-fpm.conf encontrado"
else
    print_error "php-fpm.conf não encontrado"
fi

# Sites disponíveis
echo "Sites Nginx disponíveis:"
ls -la /etc/nginx/sites-available/ 2>/dev/null || print_warning "Diretório sites-available não encontrado"

echo "Sites Nginx habilitados:"
ls -la /etc/nginx/sites-enabled/ 2>/dev/null || print_warning "Diretório sites-enabled não encontrado"
echo ""

# 6. VERIFICAÇÃO DE DIRETÓRIOS WEB
print_step "📁 Verificando estrutura de diretórios web..."

directories=("/var/www" "/var/www/html" "/var/www/admin.consultoriawk.com" "/var/www/api.consultoriawk.com" "/opt/wk-crm")
for dir in "${directories[@]}"; do
    if [ -d "$dir" ]; then
        print_success "$dir existe"
        echo "  Proprietário: $(ls -ld $dir | awk '{print $3":"$4}')"
        echo "  Permissões: $(ls -ld $dir | awk '{print $1}')"
    else
        print_error "$dir não existe"
    fi
done
echo ""

# 7. VERIFICAÇÃO DE LOGS
print_step "📋 Verificando logs recentes..."

log_files=("/var/log/nginx/error.log" "/var/log/nginx/access.log" "/var/log/php8.2-fpm.log")
for log_file in "${log_files[@]}"; do
    if [ -f "$log_file" ]; then
        print_success "$log_file existe"
        echo "Últimas 3 linhas:"
        tail -3 "$log_file" 2>/dev/null | sed 's/^/  /'
    else
        print_warning "$log_file não encontrado"
    fi
    echo ""
done

# 8. VERIFICAÇÃO DE PROCESSOS
print_step "⚙️ Verificando processos ativos..."
echo "Processos Nginx:"
ps aux | grep nginx | grep -v grep || print_warning "Nenhum processo Nginx encontrado"

echo "Processos PHP-FPM:"
ps aux | grep php-fpm | grep -v grep || print_warning "Nenhum processo PHP-FPM encontrado"
echo ""

# 9. CORREÇÕES AUTOMÁTICAS
print_step "🔧 Aplicando correções automáticas..."

# Parar Apache se estiver rodando (conflito com Nginx)
if systemctl is-active --quiet apache2; then
    print_step "Parando Apache (conflito com Nginx)..."
    systemctl stop apache2
    systemctl disable apache2
fi

# Instalar pacotes faltantes
print_step "📦 Instalando pacotes necessários..."
apt update -qq
apt install -y nginx php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-sqlite3

# Instalar Composer se não existir
if ! command -v composer &> /dev/null; then
    print_step "📦 Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# 10. CONFIGURAÇÃO BÁSICA
print_step "⚙️ Configurando serviços básicos..."

# Criar estrutura de diretórios
mkdir -p /var/www/admin.consultoriawk.com/public_html
mkdir -p /var/www/api.consultoriawk.com/public_html
mkdir -p /opt/wk-crm

# Baixar projeto se não existir
if [ ! -d "/opt/wk-crm/.git" ]; then
    print_step "📥 Clonando projeto..."
    git clone https://github.com/cristianawktec/wk-crm-microservices.git /opt/wk-crm
fi

# Configuração mínima do Nginx
cat > /etc/nginx/sites-available/default << 'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    
    root /var/www/html;
    index index.html index.htm index.nginx-debian.html;
    
    server_name _;
    
    location / {
        try_files $uri $uri/ =404;
    }
}
EOF

# Página de teste básica
cat > /var/www/html/index.html << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Servidor WK CRM - Funcionando!</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f4f4f4; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Servidor WK CRM</h1>
        <div class="status success">
            <strong>✅ Nginx está funcionando!</strong>
        </div>
        <div class="status info">
            <strong>📋 Próximos passos:</strong><br>
            1. Configurar subdomínios admin.consultoriawk.com e api.consultoriawk.com<br>
            2. Executar script fix-server.sh<br>
            3. Testar AdminLTE e Laravel API
        </div>
        <p><strong>Data/Hora:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><strong>Servidor:</strong> <?php echo $_SERVER['SERVER_NAME'] ?? 'localhost'; ?></p>
    </div>
</body>
</html>
EOF

# Corrigir permissões
chown -R www-data:www-data /var/www/
chmod -R 755 /var/www/

# Habilitar site padrão
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Testar e reiniciar serviços
print_step "🔄 Reiniciando serviços..."
if nginx -t; then
    systemctl enable nginx
    systemctl restart nginx
    print_success "Nginx reiniciado com sucesso"
else
    print_error "Erro na configuração do Nginx"
    nginx -t
fi

systemctl enable php8.2-fpm
systemctl restart php8.2-fpm
print_success "PHP-FPM reiniciado"

# 11. TESTE FINAL
print_step "🧪 Executando testes finais..."

# Teste de conectividade local
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200"; then
    print_success "Servidor responde na porta 80"
else
    print_warning "Servidor não responde na porta 80"
fi

# Verificar se PHP funciona
echo "<?php phpinfo(); ?>" > /var/www/html/info.php
chown www-data:www-data /var/www/html/info.php

print_step "📊 RELATÓRIO FINAL"
echo ""
echo "🌐 URLs para testar:"
echo "  - http://$(hostname -I | awk '{print $1}')/ (IP direto)"
echo "  - http://admin.consultoriawk.com/ (se DNS configurado)"
echo "  - http://api.consultoriawk.com/ (se DNS configurado)"
echo ""
echo "🔧 Para continuar a configuração:"
echo "  curl -fsSL https://raw.githubusercontent.com/cristianawktec/wk-crm-microservices/main/fix-server.sh | bash"
echo ""
echo "📋 Logs importantes:"
echo "  - tail -f /var/log/nginx/error.log"
echo "  - tail -f /var/log/nginx/access.log"
echo "  - systemctl status nginx php8.2-fpm"

print_success "Diagnóstico concluído! Servidor básico configurado."