# Teste de Login Manual na API VPS
# Executa: .\test-login.ps1

Write-Host "🔐 Testando login na API..." -ForegroundColor Cyan
Write-Host ""

$apiUrl = "https://api.consultoriawk.com/api"
$email = "customer-test@wkcrm.local"
$password = "password123"

Write-Host "Endpoint: $apiUrl/auth/login" -ForegroundColor Gray
Write-Host "Email: $email" -ForegroundColor Gray
Write-Host ""

$body = @{
    email = $email
    password = $password
} | ConvertTo-Json

Write-Host "📡 Fazendo login..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$apiUrl/auth/login" `
        -Method Post `
        -Body $body `
        -ContentType "application/json"

    Write-Host ""
    Write-Host "✅ Login bem-sucedido!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Response:" -ForegroundColor Cyan
    $response | ConvertTo-Json -Depth 10
    
    if ($response.token) {
        Write-Host ""
        Write-Host "🎉 Token obtido!" -ForegroundColor Green
        Write-Host "Testando dashboard stats..." -ForegroundColor Yellow
        
        $statsResponse = Invoke-RestMethod -Uri "$apiUrl/dashboard/customer-stats" `
            -Method Get `
            -Headers @{
                "Authorization" = "Bearer $($response.token)"
                "Accept" = "application/json"
            }
        
        Write-Host ""
        Write-Host "📊 Dashboard Stats:" -ForegroundColor Cyan
        $statsResponse | ConvertTo-Json -Depth 10
        
        Write-Host ""
        Write-Host "✅ API funcionando perfeitamente!" -ForegroundColor Green
    }
} catch {
    Write-Host ""
    Write-Host "❌ Erro:" -ForegroundColor Red
    Write-Host $_.Exception.Message
}
