# Script de Deploy Manual para VPS
# Execute este script se você tiver acesso SSH ao servidor

# Configuração
$vpsIp = "72.60.254.100"
$vpsUser = "root"
$crumPath = "/root/crm"

Write-Host "🚀 Iniciando Deploy WK CRM..." -ForegroundColor Green

# Conectar via SSH e fazer pull
Write-Host "`n📥 Puxando código do GitHub..." -ForegroundColor Cyan
ssh "$vpsUser@$vpsIp" "cd $crumPath && git pull 2>&1"

# Limpar cache
Write-Host "`n🧹 Limpando cache..." -ForegroundColor Cyan
ssh "$vpsUser@$vpsIp" "docker exec wk_crm_laravel php artisan optimize:clear"

# Verificar logs recentes
Write-Host "`n📋 Últimas linhas do log..." -ForegroundColor Cyan
ssh "$vpsUser@$vpsIp" "docker exec wk_crm_laravel tail -n 20 storage/logs/laravel.log"

Write-Host "`n✅ Deploy completo!" -ForegroundColor Green

# Testar email
Write-Host "`n📧 Testando envio de email..." -ForegroundColor Yellow
$result = curl -s "https://api.consultoriawk.com/api/test-email"
$result | ConvertFrom-Json | ConvertTo-Json

Write-Host "`n✨ Verifique seu email em cristian@consultoriawk.com!" -ForegroundColor Green
