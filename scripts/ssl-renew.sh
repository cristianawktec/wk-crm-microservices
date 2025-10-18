#!/bin/bash

# 🔄 SSL Certificate Renewal Script
# Renova automaticamente os certificados SSL

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

print_title "🔄 Renovação Automática de Certificados SSL"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script deve ser executado como root"
    exit 1
fi

# Backup da configuração atual
print_step "💾 Fazendo backup da configuração atual..."
cp -r /etc/nginx/sites-available /etc/nginx/sites-available.backup.$(date +%Y%m%d_%H%M%S)
cp -r /etc/letsencrypt /etc/letsencrypt.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

print_step "📋 Listando certificados atuais:"
certbot certificates

echo ""
print_step "🔄 Tentando renovar certificados existentes..."

# Primeiro tentar renovação dos certificados existentes
if certbot renew --nginx --quiet; then
    print_step "✅ Renovação automática bem-sucedida!"
else
    print_warning "⚠️  Renovação automática falhou. Tentando recriar certificados..."
    
    # Se a renovação falhar, tentar recriar
    DOMAINS=("api.consultoriawk.com" "admin.consultoriawk.com")
    
    for domain in "${DOMAINS[@]}"; do
        print_step "🔧 Recriando certificado para $domain..."
        
        # Verificar se o domínio está apontando para este servidor
        DOMAIN_IP=$(dig +short $domain | tail -n1)
        SERVER_IP=$(curl -s ifconfig.me)
        
        if [ "$DOMAIN_IP" = "$SERVER_IP" ]; then
            print_step "✅ DNS para $domain está correto ($DOMAIN_IP)"
            
            # Tentar criar o certificado
            if certbot --nginx -d $domain --non-interactive --agree-tos --email admin@consultoriawk.com; then
                print_step "✅ Certificado para $domain criado com sucesso!"
            else
                print_error "❌ Falha ao criar certificado para $domain"
            fi
        else
            print_warning "⚠️  DNS para $domain não aponta para este servidor"
            print_step "📍 Domínio aponta para: $DOMAIN_IP"
            print_step "📍 Servidor está em: $SERVER_IP"
        fi
    done
fi

echo ""
print_step "🧪 Testando configuração do Nginx..."
if nginx -t; then
    print_step "✅ Configuração do Nginx válida"
    print_step "🔄 Recarregando Nginx..."
    systemctl reload nginx
    print_step "✅ Nginx recarregado com sucesso!"
else
    print_error "❌ Configuração do Nginx inválida!"
    print_step "🔄 Restaurando backup..."
    rm -rf /etc/nginx/sites-available
    mv /etc/nginx/sites-available.backup.* /etc/nginx/sites-available
    nginx -t && systemctl reload nginx
    print_step "✅ Backup restaurado"
    exit 1
fi

echo ""
print_step "🧪 Testando URLs após renovação:"

DOMAINS=("api.consultoriawk.com" "admin.consultoriawk.com")
for domain in "${DOMAINS[@]}"; do
    if curl -f -s -I "https://$domain" >/dev/null 2>&1; then
        print_step "✅ https://$domain - FUNCIONANDO"
    else
        print_warning "⚠️  https://$domain - Ainda com problemas"
    fi
done

echo ""
print_title "📋 RELATÓRIO DE RENOVAÇÃO"

print_step "📊 Status dos certificados após renovação:"
certbot certificates

echo ""
print_step "🔧 Configurando renovação automática..."

# Adicionar cron job para renovação automática
CRON_JOB="0 12 * * * /usr/bin/certbot renew --quiet && /bin/systemctl reload nginx"
if ! crontab -l 2>/dev/null | grep -q "certbot renew"; then
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    print_step "✅ Renovação automática configurada (diariamente às 12:00)"
else
    print_step "✅ Renovação automática já configurada"
fi

echo ""
print_step "✅ Renovação SSL concluída!"
print_step "📅 Próxima verificação automática: $(date -d 'tomorrow 12:00' '+%Y-%m-%d %H:%M')"
print_step "🌐 Teste suas URLs:"
echo "   https://api.consultoriawk.com/api/health"
echo "   https://admin.consultoriawk.com/"