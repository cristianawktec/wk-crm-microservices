#!/bin/bash
# Debug: verificar configuração nginx e testar endpoint

echo "=== 1. Verificar configuração Nginx app.consultoriawk.com ==="
cat /etc/nginx/sites-available/app.consultoriawk.com 2>/dev/null || echo "Arquivo não existe"

echo ""
echo "=== 2. Verificar links simbólicos ==="
ls -la /etc/nginx/sites-enabled/ | grep app

echo ""
echo "=== 3. Testar endpoint direto na API ==="
curl -i https://api.consultoriawk.com/api/trends/analyze?period=year 2>&1 | head -20

echo ""
echo "=== 4. Verificar estrutura /var/www/consultoriawk-crm ==="
ls -la /var/www/consultoriawk-crm/ | head -20

echo ""
echo "=== 5. Verificar app/ ==="
ls -la /var/www/consultoriawk-crm/app/ | head -20
