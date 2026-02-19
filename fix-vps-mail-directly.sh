#!/bin/bash
set -e

cd /opt/wk-crm/wk-crm-laravel

echo "=== CORRIGINDO EMAIL DE LOGIN NA VPS ==="
echo ""

# 1. Verificar se ja existe
if grep -q "^MAIL_AUDIT_RECIPIENT=" .env; then
    echo "[1] MAIL_AUDIT_RECIPIENT ja existe"
    grep "^MAIL_AUDIT_RECIPIENT=" .env
else
    echo "[1] Adicionando MAIL_AUDIT_RECIPIENT"
    echo "" >> .env
    echo "MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com" >> .env
    echo "Adicionado!"
fi

echo ""
echo "[2] Verificando conteudo .env (tail -5):"
tail -5 .env

echo ""
echo "[3] Limpando cache Laravel:"
docker exec wk_crm_laravel php artisan config:cache

echo ""
echo "[4] Verificando config carregada:"
docker exec wk_crm_laravel php artisan config:show mail.audit_recipient

echo ""
echo "=== CORRECAO COMPLETA ==="
echo "Agora faca login novamente e confira se o email chega"
