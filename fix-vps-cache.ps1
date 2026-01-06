# Script simplificado - apenas limpa caches
# Executa: .\fix-vps-cache.ps1

$VPS_HOST = "root@72.60.254.100"
$LARAVEL_PATH = "/var/www/html/wk-crm-laravel"

Write-Host "🔧 Limpando caches do Laravel na VPS..." -ForegroundColor Cyan
Write-Host ""

$commands = @(
    "cd $LARAVEL_PATH",
    "php artisan route:clear",
    "php artisan config:clear", 
    "php artisan cache:clear",
    "php artisan view:clear",
    "echo ''",
    "echo '✅ Caches limpos!'",
    "echo ''",
    "echo '📋 Rotas disponíveis com customer:'",
    "php artisan route:list | grep -i customer"
)

$fullCommand = $commands -join " && "

ssh $VPS_HOST "$fullCommand"

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ Caches limpos com sucesso!" -ForegroundColor Green
    Write-Host ""
    Write-Host "🧪 Agora testando a API..." -ForegroundColor Yellow
    
    Start-Sleep -Seconds 2
    
    # Testar endpoint
    Write-Host ""
    Write-Host "Gerando token..." -ForegroundColor Gray
    curl -s "https://api.consultoriawk.com/api/auth/test-customer" | Out-File -FilePath "temp-token.json"
    
    if (Test-Path "temp-token.json") {
        $tokenData = Get-Content "temp-token.json" | ConvertFrom-Json
        $token = $tokenData.token
        
        Write-Host "Testando /api/dashboard/customer-stats..." -ForegroundColor Yellow
        $response = curl -s -H "Authorization: Bearer $token" "https://api.consultoriawk.com/api/dashboard/customer-stats"
        
        Write-Host ""
        Write-Host "Resposta da API:" -ForegroundColor Cyan
        Write-Host $response
        
        Remove-Item "temp-token.json" -ErrorAction SilentlyContinue
        
        if ($response -like "*success*") {
            Write-Host ""
            Write-Host "🎉 API funcionando!" -ForegroundColor Green
        }
    }
} else {
    Write-Host ""
    Write-Host "❌ Erro ao limpar caches" -ForegroundColor Red
}
