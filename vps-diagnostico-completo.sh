#!/bin/bash
# Script de diagnostico completo - executa em uma unica sessao SSH

echo "========== DIAGNOSTICO VPS WK CRM =========="
echo ""

echo "[1] COMMIT ATUAL:"
cd /opt/wk-crm/wk-crm-laravel
git log --oneline -1
echo ""

echo "[2] MAIL_AUDIT_RECIPIENT:"
docker exec wk_crm_laravel php artisan config:show mail.audit_recipient
echo ""

echo "[3] APP.NOTIFICATION_EMAIL:"
docker exec wk_crm_laravel php artisan config:show app.notification_email
echo ""

echo "[4] ULTIMOS LOGS DE EMAIL (20 linhas):"
docker exec wk_crm_laravel tail -100 storage/logs/laravel.log | grep -i "email\|mail" | tail -20
echo ""

echo "[5] ULTIMOS LOGS DE NOTIFICACAO (20 linhas):"
docker exec wk_crm_laravel tail -100 storage/logs/laravel.log | grep -i "notification" | tail -20
echo ""

echo "[6] ULTIMAS 5 NOTIFICACOES NO BANCO:"
docker exec wk_crm_mysql mysql -u root -proot wk_crm -e "SELECT id, SUBSTRING(user_id, 1, 8) as user, type, title, created_at FROM notifications ORDER BY created_at DESC LIMIT 5;"
echo ""

echo "[7] ULTIMOS 3 LOGIN AUDITS:"
docker exec wk_crm_mysql mysql -u root -proot wk_crm -e "SELECT SUBSTRING(id, 1, 8) as id, SUBSTRING(user_id, 1, 8) as user, email, created_at FROM login_audits ORDER BY created_at DESC LIMIT 3;"
echo ""

echo "[8] VERIFICAR ARQUIVO .ENV - NOTIFICACAO:"
docker exec wk_crm_laravel grep -E "NOTIFICATION_EMAIL|APP_NOTIFICATION" .env || echo "Nenhuma config encontrada no .env"
echo ""

echo "========== FIM DO DIAGNOSTICO =========="
