#!/bin/bash
# Script para adicionar configs faltantes no .env da VPS

cd /opt/wk-crm/wk-crm-laravel

echo "========== VERIFICANDO E CORRIGINDO .ENV VPS =========="
echo ""

echo "[1] CONFIGS ATUAIS DE MAIL:"
grep "^MAIL_" .env || echo "Nenhuma config MAIL_ encontrada"
echo ""

echo "[2] VERIFICANDO SE MAIL_AUDIT_RECIPIENT EXISTE:"
if grep -q "^MAIL_AUDIT_RECIPIENT=" .env; then
    echo "✓ MAIL_AUDIT_RECIPIENT já existe"
    grep "^MAIL_AUDIT_RECIPIENT=" .env
else
    echo "✗ MAIL_AUDIT_RECIPIENT NÃO existe - ADICIONANDO"
    echo "" >> .env
    echo "# Email para receber auditorias de login" >> .env
    echo "MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com" >> .env
    echo "✓ MAIL_AUDIT_RECIPIENT adicionado"
fi
echo ""

echo "[3] VERIFICANDO SE APP_NOTIFICATION_EMAIL EXISTE:"
if grep -q "^APP_NOTIFICATION_EMAIL=" .env; then
    echo "✓ APP_NOTIFICATION_EMAIL já existe"
    grep "^APP_NOTIFICATION_EMAIL=" .env
else
    echo "✗ APP_NOTIFICATION_EMAIL NÃO existe - ADICIONANDO"
    echo "" >> .env
    echo "# Habilitar envio de emails em notificações" >> .env
    echo "APP_NOTIFICATION_EMAIL=true" >> .env
    echo "✓ APP_NOTIFICATION_EMAIL adicionado"
fi
echo ""

echo "[4] .ENV ATUALIZADO - CONFIGS MAIL:"
grep "^MAIL_\|^APP_NOTIFICATION" .env
echo ""

echo "[5] LIMPANDO CACHE DE CONFIG:"
docker exec wk_crm_laravel php artisan config:cache
echo ""

echo "[6] VERIFICANDO CONFIG APÓS CACHE:"
docker exec wk_crm_laravel php artisan config:show mail.audit_recipient
docker exec wk_crm_laravel php artisan config:show app.notification_email
echo ""

echo "========== FIM - .ENV CONFIGURADO =========="
