#!/bin/bash
# Diagnóstico VPS - Problemas de Email e Notificação

echo "=========================================="
echo "DIAGNÓSTICO VPS - WK CRM"
echo "=========================================="
echo ""

# 1. Verificar commit atual
echo "[1] Commit atual na VPS:"
cd /opt/wk-crm/wk-crm-laravel
git log --oneline -1
echo ""

# 2. Verificar configuração de email
echo "[2] Configuração de Email:"
docker exec wk_crm_laravel php artisan config:show mail.audit_recipient
echo ""

# 3. Verificar configuração de notificações
echo "[3] Configuração de Notificações:"
docker exec wk_crm_laravel php artisan config:show app.notification_email
echo ""

# 4. Verificar últimos logs relacionados a email
echo "[4] Últimos logs de EMAIL (últimas 100 linhas):"
docker exec wk_crm_laravel tail -100 storage/logs/laravel.log | grep -i "email\|mail" | tail -20
echo ""

# 5. Verificar últimos logs de notificação
echo "[5] Últimos logs de NOTIFICAÇÃO (últimas 100 linhas):"
docker exec wk_crm_laravel tail -100 storage/logs/laravel.log | grep -i "notification" | tail -20
echo ""

# 6. Verificar últimas notificações criadas no banco
echo "[6] Últimas 5 notificações no banco de dados:"
docker exec wk_crm_mysql mysql -u root -proot -e "USE wk_crm; SELECT id, user_id, type, title, created_at FROM notifications ORDER BY created_at DESC LIMIT 5;"
echo ""

# 7. Verificar últimos login_audits
echo "[7] Últimos 3 login audits no banco:"
docker exec wk_crm_mysql mysql -u root -proot -e "USE wk_crm; SELECT id, user_id, email, created_at FROM login_audits ORDER BY created_at DESC LIMIT 3;"
echo ""

echo "=========================================="
echo "DIAGNÓSTICO COMPLETO"
echo "=========================================="
