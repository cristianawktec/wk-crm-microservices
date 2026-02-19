#!/bin/bash
# Adiciona MAIL_AUDIT_RECIPIENT ao .env da VPS

echo "=== ADICIONANDO MAIL_AUDIT_RECIPIENT ao .env da VPS ==="
echo ""

cd /opt/wk-crm/wk-crm-laravel

echo "[1] Verificando se ja existe..."
if grep -q "MAIL_AUDIT_RECIPIENT" .env; then
    echo "AVISO: MAIL_AUDIT_RECIPIENT ja existe no .env"
    grep "MAIL_AUDIT_RECIPIENT" .env
else
    echo "Adicionando MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com"
    echo "" >> .env
    echo "MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com" >> .env
    echo "Adicionado com sucesso!"
fi

echo ""
echo "[2] Conteudo atual do .env (ultimas 15 linhas):"
tail -15 .env

echo ""
echo "[3] Limpando cache de config..."
docker exec wk_crm_laravel php artisan config:cache

echo ""
echo "[4] Verificando config carregada..."
docker exec wk_crm_laravel php artisan config:show mail.audit_recipient

echo ""
echo "=== CONFIGURACAO CONCLUIDA ==="
echo "Agora faca um login na VPS e verifique se o email chega!"
