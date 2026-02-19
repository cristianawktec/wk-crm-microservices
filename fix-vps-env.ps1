# Fix VPS .env - Adiciona configs faltantes
# Executa passo a passo para garantir sucesso

$VPS = "72.60.254.100"
$USER = "root"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "CORRIGINDO .ENV VPS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "[1] Adicionando MAIL_AUDIT_RECIPIENT..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'cd /opt/wk-crm/wk-crm-laravel && echo "" >> .env && echo "# Email para receber auditorias de login" >> .env && echo "MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com" >> .env && echo "Config adicionada"'

Write-Host ""
Write-Host "[2] Adicionando APP_NOTIFICATION_EMAIL..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'cd /opt/wk-crm/wk-crm-laravel && echo "" >> .env && echo "# Habilitar envio de emails em notificacoes" >> .env && echo "APP_NOTIFICATION_EMAIL=true" >> .env && echo "Config adicionada"'

Write-Host ""
Write-Host "[3] Verificando configs adicionadas..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'cd /opt/wk-crm/wk-crm-laravel && tail -10 .env'

Write-Host ""
Write-Host "[4] Limpando cache de config..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'docker exec wk_crm_laravel php artisan config:cache'

Write-Host ""
Write-Host "[5] Verificando MAIL_AUDIT_RECIPIENT apos cache..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'docker exec wk_crm_laravel php artisan config:show mail.audit_recipient'

Write-Host ""
Write-Host "[6] Verificando APP_NOTIFICATION_EMAIL apos cache..." -ForegroundColor Yellow
ssh ${USER}@${VPS} 'docker exec wk_crm_laravel php artisan config:show app.notification_email'

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "CORRECAO CONCLUIDA" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
