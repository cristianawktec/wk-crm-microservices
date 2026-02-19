# Diagnostico VPS - PowerShell Script
# Executa diagnostico na VPS sem modificar nada

$VPS = "72.60.254.100"
$USER = "root"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "DIAGNOSTICO VPS - WK CRM" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "[1] Verificando commit atual..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'cd /opt/wk-crm/wk-crm-laravel; git log --oneline -1'

Write-Host ""
Write-Host "[2] Verificando MAIL_AUDIT_RECIPIENT..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'docker exec wk_crm_laravel php artisan config:show mail.audit_recipient'

Write-Host ""
Write-Host "[3] Verificando app.notification_email..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'docker exec wk_crm_laravel php artisan config:show app.notification_email'

Write-Host ""
Write-Host "[4] Ultimos logs de EMAIL..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'docker exec wk_crm_laravel tail -100 storage/logs/laravel.log' | Select-String -Pattern "email|mail" -CaseSensitive:$false | Select-Object -Last 15

Write-Host ""
Write-Host "[5] Ultimos logs de NOTIFICACAO..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'docker exec wk_crm_laravel tail -100 storage/logs/laravel.log' | Select-String -Pattern "notification" -CaseSensitive:$false | Select-Object -Last 15

Write-Host ""
Write-Host "[6] Ultimas 5 notificacoes no banco..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'docker exec wk_crm_mysql mysql -u root -proot wk_crm -e "SELECT id, user_id, type, title, created_at FROM notifications ORDER BY created_at DESC LIMIT 5;"'

Write-Host ""
Write-Host "[7] Ultimos 3 login audits..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'docker exec wk_crm_mysql mysql -u root -proot wk_crm -e "SELECT id, user_id, email, created_at FROM login_audits ORDER BY created_at DESC LIMIT 3;"'

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "DIAGNOSTICO COMPLETO" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
