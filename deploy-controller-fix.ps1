#!/usr/bin/env pwsh
# Deploy da correção do bug de oportunidades
# Arquivo: CustomerDashboardController.php
# Bug corrigido: Fallback de dados DEMO removido

Write-Host "=== DEPLOY DA CORREÇÃO DO BUG DE OPORTUNIDADES ===" -ForegroundColor Cyan
Write-Host ""

$localFile = "c:\xampp\htdocs\crm\wk-crm-laravel\app\Http\Controllers\Api\CustomerDashboardController.php"
$serverHost = "root@72.60.254.100"

# Step 1: Copy file to server
Write-Host "📤 [1/3] Copiando arquivo para o servidor..." -ForegroundColor Yellow
Get-Content $localFile | ssh $serverHost "cat > /tmp/CustomerDashboardController.php"

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Arquivo copiado com sucesso" -ForegroundColor Green
} else {
    Write-Host "❌ Erro ao copiar arquivo" -ForegroundColor Red
    exit 1
}

# Step 2: Copy to Docker container
Write-Host ""
Write-Host "🐳 [2/3] Copiando para dentro do container Docker..." -ForegroundColor Yellow
ssh $serverHost "docker cp /tmp/CustomerDashboardController.php wk_crm_laravel:/app/app/Http/Controllers/Api/CustomerDashboardController.php"

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Arquivo copiado para o container" -ForegroundColor Green
} else {
    Write-Host "❌ Erro ao copiar para o container" -ForegroundColor Red
    exit 1
}

# Step 3: Clear Laravel cache
Write-Host ""
Write-Host "🧹 [3/3] Limpando cache do Laravel..." -ForegroundColor Yellow
ssh $serverHost "docker exec wk_crm_laravel php artisan cache:clear && docker exec wk_crm_laravel php artisan config:clear && docker exec wk_crm_laravel php artisan route:clear"

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
Write-Host "- Removido fallback de dados DEMO que substituía oportunidades reais"
Write-Host "- Agora getOpportunities() retorna apenas dados reais do banco"
Write-Host ""
Write-Host "🧪 Como testar:" -ForegroundColor White
Write-Host "1. Acesse app.consultoriawk.com"
Write-Host "2. Faça login com admin@consultoriawk.com"
Write-Host "3. Crie uma nova oportunidade"
Write-Host "4. Recarregue a página (F5)"
Write-Host "5. Verifique se TODAS as oportunidades aparecem (sem sumir)"
Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
