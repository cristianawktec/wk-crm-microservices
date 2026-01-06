# Script para corrigir rotas 404 na VPS
# Executa: .\fix-vps-routes.ps1

$VPS_HOST = "root@72.60.254.100"
$LARAVEL_PATH = "/var/www/html/wk-crm-laravel"

Write-Host "🚀 Iniciando correção de rotas na VPS..." -ForegroundColor Cyan
Write-Host ""

# Comandos a serem executados na VPS
$commands = @(
    "cd $LARAVEL_PATH",
    "echo '📦 Atualizando código...'",
    "git status",
    "git pull origin main",
    "echo '🔧 Limpando caches...'",
    "php artisan route:clear",
    "php artisan config:clear",
    "php artisan cache:clear",
    "php artisan view:clear",
    "echo '✅ Recriando cache de rotas...'",
    "php artisan route:cache",
    "php artisan config:cache",
    "echo '📋 Verificando rotas customer...'",
    "php artisan route:list --path=customer",
    "echo '🎉 Deploy concluído!'"
)

$fullCommand = $commands -join " && "

Write-Host "Executando comandos na VPS..." -ForegroundColor Yellow
Write-Host "Host: $VPS_HOST" -ForegroundColor Gray
Write-Host "Path: $LARAVEL_PATH" -ForegroundColor Gray
Write-Host ""

# Executar via SSH
ssh $VPS_HOST "$fullCommand"

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ Deploy concluído com sucesso!" -ForegroundColor Green
    Write-Host ""
    Write-Host "🧪 Testando endpoint..." -ForegroundColor Cyan
    
    # Gerar novo token e testar
    Write-Host ""
    Write-Host "Gerando token de teste..." -ForegroundColor Yellow
    $tokenResponse = curl -s "https://api.consultoriawk.com/api/auth/test-customer"
    
    if ($tokenResponse) {
        try {
            $tokenData = $tokenResponse | ConvertFrom-Json
            $token = $tokenData.token
            
            Write-Host "Token gerado: ${token}" -ForegroundColor Gray
            Write-Host ""
            Write-Host "Testando /api/dashboard/customer-stats..." -ForegroundColor Yellow
            
            $statsResponse = curl -s -H "Authorization: Bearer $token" "https://api.consultoriawk.com/api/dashboard/customer-stats"
            Write-Host $statsResponse
            
            Write-Host ""
            Write-Host "✅ API respondendo corretamente!" -ForegroundColor Green
        } catch {
            Write-Host "⚠️ Erro ao testar API: $_" -ForegroundColor Red
        }
    }
} else {
    Write-Host ""
    Write-Host "❌ Erro no deploy. Código de saída: $LASTEXITCODE" -ForegroundColor Red
    Write-Host "Verifique as mensagens de erro acima." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "📊 Acesse o dashboard em: https://app.consultoriawk.com" -ForegroundColor Cyan
