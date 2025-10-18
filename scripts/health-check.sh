#!/bin/bash

# 🏥 System Health Check
# Verifica saúde geral do sistema após deploy

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

# Contador de erros
ERROR_COUNT=0

add_error() {
    ERROR_COUNT=$((ERROR_COUNT + 1))
    print_error "$1"
}

print_title "🏥 WK CRM - System Health Check"

echo "📊 Data/Hora: $(date '+%Y-%m-%d %H:%M:%S')"
echo "🖥️  Servidor: $(hostname)"
echo "📍 IP: $(curl -s ifconfig.me 2>/dev/null || echo 'N/A')"
echo ""

# 1. Verificar serviços essenciais
print_step "🔧 Verificando serviços essenciais..."

SERVICES=("nginx" "php8.2-fpm" "postgresql")
for service in "${SERVICES[@]}"; do
    if systemctl is-active --quiet $service; then
        print_step "✅ $service: ATIVO"
    else
        add_error "❌ $service: INATIVO"
    fi
done

echo ""

# 2. Verificar portas essenciais
print_step "🌐 Verificando portas essenciais..."

PORTS=("80:HTTP" "443:HTTPS" "5432:PostgreSQL")
for port_desc in "${PORTS[@]}"; do
    port=$(echo $port_desc | cut -d: -f1)
    desc=$(echo $port_desc | cut -d: -f2)
    
    if netstat -tuln | grep -q ":$port "; then
        print_step "✅ Porta $port ($desc): ABERTA"
    else
        add_error "❌ Porta $port ($desc): FECHADA"
    fi
done

echo ""

# 3. Verificar configuração do Nginx
print_step "⚙️  Verificando configuração do Nginx..."

if nginx -t >/dev/null 2>&1; then
    print_step "✅ Configuração do Nginx: VÁLIDA"
else
    add_error "❌ Configuração do Nginx: INVÁLIDA"
fi

echo ""

# 4. Verificar certificados SSL
print_step "🔒 Verificando certificados SSL..."

DOMAINS=("api.consultoriawk.com" "admin.consultoriawk.com")
for domain in "${DOMAINS[@]}"; do
    if [ -f "/etc/letsencrypt/live/$domain/fullchain.pem" ]; then
        # Verificar data de expiração
        EXPIRY_DATE=$(openssl x509 -enddate -noout -in "/etc/letsencrypt/live/$domain/fullchain.pem" | cut -d= -f2)
        EXPIRY_EPOCH=$(date -d "$EXPIRY_DATE" +%s 2>/dev/null || echo "0")
        CURRENT_EPOCH=$(date +%s)
        DAYS_LEFT=$(( (EXPIRY_EPOCH - CURRENT_EPOCH) / 86400 ))
        
        if [ $DAYS_LEFT -gt 7 ]; then
            print_step "✅ SSL $domain: Válido por $DAYS_LEFT dias"
        elif [ $DAYS_LEFT -gt 0 ]; then
            print_warning "⚠️  SSL $domain: Expira em $DAYS_LEFT dias"
        else
            add_error "❌ SSL $domain: EXPIRADO há $((-DAYS_LEFT)) dias"
        fi
    else
        add_error "❌ SSL $domain: Certificado não encontrado"
    fi
done

echo ""

# 5. Verificar conectividade das URLs
print_step "🌐 Verificando conectividade das URLs..."

URLS=(
    "https://api.consultoriawk.com/api/health:API Health"
    "https://admin.consultoriawk.com/:AdminLTE"
)

for url_desc in "${URLS[@]}"; do
    url=$(echo $url_desc | cut -d: -f1)
    desc=$(echo $url_desc | cut -d: -f2)
    
    if curl -f -s --max-time 10 "$url" >/dev/null 2>&1; then
        print_step "✅ $desc: ACESSÍVEL"
    else
        add_error "❌ $desc: INACESSÍVEL ($url)"
    fi
done

echo ""

# 6. Verificar espaço em disco
print_step "💾 Verificando espaço em disco..."

DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -lt 80 ]; then
    print_step "✅ Espaço em disco: ${DISK_USAGE}% usado"
elif [ $DISK_USAGE -lt 90 ]; then
    print_warning "⚠️  Espaço em disco: ${DISK_USAGE}% usado (ATENÇÃO)"
else
    add_error "❌ Espaço em disco: ${DISK_USAGE}% usado (CRÍTICO)"
fi

echo ""

# 7. Verificar memória
print_step "🧠 Verificando uso de memória..."

MEM_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2 }')
if [ $MEM_USAGE -lt 80 ]; then
    print_step "✅ Uso de memória: ${MEM_USAGE}%"
elif [ $MEM_USAGE -lt 90 ]; then
    print_warning "⚠️  Uso de memória: ${MEM_USAGE}% (ATENÇÃO)"
else
    add_error "❌ Uso de memória: ${MEM_USAGE}% (CRÍTICO)"
fi

echo ""

# 8. Verificar logs de erro recentes
print_step "📝 Verificando logs de erro recentes..."

ERROR_LOGS=(
    "/var/log/nginx/error.log"
    "/opt/wk-crm/wk-crm-laravel/storage/logs/laravel.log"
    "/var/log/php8.2-fpm.log"
)

for log_file in "${ERROR_LOGS[@]}"; do
    if [ -f "$log_file" ]; then
        RECENT_ERRORS=$(tail -100 "$log_file" | grep -i error | wc -l)
        if [ $RECENT_ERRORS -eq 0 ]; then
            print_step "✅ $log_file: Sem erros recentes"
        elif [ $RECENT_ERRORS -lt 5 ]; then
            print_warning "⚠️  $log_file: $RECENT_ERRORS erros recentes"
        else
            add_error "❌ $log_file: $RECENT_ERRORS erros recentes (CRÍTICO)"
        fi
    else
        print_warning "⚠️  Log não encontrado: $log_file"
    fi
done

echo ""
print_title "📊 RESUMO DO HEALTH CHECK"

if [ $ERROR_COUNT -eq 0 ]; then
    print_step "🎉 SISTEMA SAUDÁVEL - Nenhum problema encontrado!"
    echo "✅ Todos os serviços estão funcionando corretamente"
    echo "✅ URLs acessíveis externamente"
    echo "✅ Certificados SSL válidos"
    echo "✅ Recursos do sistema OK"
    exit 0
elif [ $ERROR_COUNT -lt 3 ]; then
    print_warning "⚠️  ATENÇÃO - $ERROR_COUNT problema(s) encontrado(s)"
    echo "📋 Verifique os itens marcados com ❌ acima"
    exit 1
else
    print_error "🚨 CRÍTICO - $ERROR_COUNT problemas encontrados!"
    echo "🔧 Intervenção necessária imediatamente"
    echo "📞 Contate o administrador do sistema"
    exit 2
fi