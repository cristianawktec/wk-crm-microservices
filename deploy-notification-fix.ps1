#!/usr/bin/env pwsh
# Deploy NotificationService fix - Incluindo customer nas notificações

Write-Host "=== DEPLOY DA CORREÇÃO DE NOTIFICAÇÕES ===" -ForegroundColor Cyan
Write-Host ""

$localFile = "c:\xampp\htdocs\crm\wk-crm-laravel\app\Services\NotificationService.php"
$serverHost = "root@72.60.254.100"
$tmpFile = "/tmp/NotificationService.php"
$containerDest = "/var/www/html/app/Services/NotificationService.php"

# Step 1: Verificar estrutura do container
Write-Host "📁 [1/4] Verificando estrutura do container..." -ForegroundColor Yellow
ssh $serverHost "docker exec wk_crm_laravel ls -la /var/www/html/app/Services/ | head -5"

# Step 2: Copy file to server
Write-Host ""
Write-Host "📤 [2/4] Copiando arquivo para o servidor..." -ForegroundColor Yellow
Get-Content $localFile | ssh $serverHost "cat > $tmpFile"

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Arquivo copiado com sucesso" -ForegroundColor Green
} else {
    Write-Host "❌ Erro ao copiar arquivo" -ForegroundColor Red
    exit 1
}

# Step 3: Copy to Docker container
Write-Host ""
Write-Host "🐳 [3/4] Copiando para dentro do container Docker..." -ForegroundColor Yellow
ssh $serverHost "docker cp $tmpFile wk_crm_laravel:$containerDest"

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Arquivo copiado para o container" -ForegroundColor Green
} else {
    Write-Host "❌ Erro ao copiar para o container" -ForegroundColor Red
    exit 1
}

# Step 4: Clear Laravel cache
Write-Host ""
Write-Host "🧹 [4/4] Limpando cache do Laravel..." -ForegroundColor Yellow
ssh $serverHost "docker exec wk_crm_laravel php artisan cache:clear && docker exec wk_crm_laravel php artisan config:clear"

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Cache limpo com sucesso" -ForegroundColor Green
} else {
    Write-Host "⚠️ Aviso: Erro ao limpar cache (pode ser normal)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "✅ DEPLOY CONCLUÍDO!" -ForegroundColor Green
Write-Host ""
Write-Host "🔍 O que foi corrigido:" -ForegroundColor White
Write-Host "- Notificações agora são criadas para o CLIENTE que cria a oportunidade"
Write-Host "- Antes: Só managers/admins recebiam notificação"
Write-Host "- Agora: Cliente + Managers recebem notificação"
Write-Host ""
Write-Host "🧪 Como testar:" -ForegroundColor White
Write-Host "1. Acesse app.consultoriawk.com"
Write-Host "2. Faça login com admin@consultoriawk.com"
Write-Host "3. Crie uma NOVA oportunidade"
Write-Host "4. Verifique:"
Write-Host "   - Notificação na tela (bell badge +1)"
Write-Host "   - Página de Notificações (deve mostrar a nova)"
Write-Host "   - Email em noreply@consultoriawk.com"
Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
