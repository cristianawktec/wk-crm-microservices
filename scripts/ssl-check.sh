#!/bin/bash

# 🔍 SSL Certificate Health Check
# Verifica status dos certificados SSL e renova se necessário

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

print_title "🔒 SSL Certificate Health Check"

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script deve ser executado como root"
    exit 1
fi

# Domínios para verificar
DOMAINS=("api.consultoriawk.com" "admin.consultoriawk.com")

print_step "📋 Verificando certificados SSL..."

# Verificar certificados existentes
print_step "🔍 Listando certificados do Certbot:"
certbot certificates || {
    print_warning "Certbot não instalado ou sem certificados"
    print_step "Instalando Certbot..."
    apt update
    apt install -y certbot python3-certbot-nginx
}

echo ""
print_step "🧪 Testando conectividade SSL dos domínios:"

for domain in "${DOMAINS[@]}"; do
    echo "📡 Testando $domain..."
    
    # Verificar se o certificado está válido
    if openssl s_client -connect "$domain:443" -servername "$domain" < /dev/null 2>/dev/null | openssl x509 -noout -dates 2>/dev/null; then
        print_step "✅ Certificado para $domain encontrado"
        
        # Verificar data de expiração
        EXPIRY=$(openssl s_client -connect "$domain:443" -servername "$domain" < /dev/null 2>/dev/null | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
        EXPIRY_EPOCH=$(date -d "$EXPIRY" +%s 2>/dev/null || echo "0")
        CURRENT_EPOCH=$(date +%s)
        DAYS_LEFT=$(( (EXPIRY_EPOCH - CURRENT_EPOCH) / 86400 ))
        
        if [ $DAYS_LEFT -gt 7 ]; then
            print_step "✅ $domain: Certificado válido por mais $DAYS_LEFT dias"
        elif [ $DAYS_LEFT -gt 0 ]; then
            print_warning "⚠️  $domain: Certificado expira em $DAYS_LEFT dias - RENOVAÇÃO NECESSÁRIA"
        else
            print_error "❌ $domain: Certificado EXPIRADO há $((-DAYS_LEFT)) dias"
        fi
    else
        print_error "❌ $domain: Certificado não encontrado ou inválido"
    fi
    echo ""
done

echo ""
print_step "🔧 Verificando configuração do Nginx:"

# Verificar se os arquivos de certificado existem
for domain in "${DOMAINS[@]}"; do
    CERT_PATH="/etc/letsencrypt/live/$domain/fullchain.pem"
    KEY_PATH="/etc/letsencrypt/live/$domain/privkey.pem"
    
    if [ -f "$CERT_PATH" ] && [ -f "$KEY_PATH" ]; then
        print_step "✅ Arquivos de certificado para $domain encontrados"
    else
        print_warning "⚠️  Arquivos de certificado para $domain NÃO encontrados"
        print_step "📁 Esperado em: $CERT_PATH"
    fi
done

# Testar configuração do Nginx
if nginx -t >/dev/null 2>&1; then
    print_step "✅ Configuração do Nginx válida"
else
    print_error "❌ Configuração do Nginx com erros:"
    nginx -t
fi

echo ""
print_title "📊 RESUMO DO DIAGNÓSTICO"

# Health check das URLs
print_step "🌐 Testando acesso às URLs:"

for domain in "${DOMAINS[@]}"; do
    if curl -f -s -I "https://$domain" >/dev/null 2>&1; then
        print_step "✅ https://$domain - ACESSÍVEL"
    else
        print_error "❌ https://$domain - INACESSÍVEL"
    fi
done

echo ""
print_step "📋 Para renovar certificados manualmente:"
echo "   certbot renew --nginx --dry-run  # Teste"
echo "   certbot renew --nginx           # Renovação real"
echo ""
print_step "📋 Para criar novos certificados:"
echo "   certbot --nginx -d api.consultoriawk.com"
echo "   certbot --nginx -d admin.consultoriawk.com"
echo ""
print_step "✅ Diagnóstico SSL concluído!"