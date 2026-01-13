# Quick test script for WK AI Service (Windows PowerShell)
# Usage: .\test-ai-service.ps1

Write-Host "🤖 WK AI Service - Quick Test (Windows)" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

$BASE_URL = "http://localhost:8000"

# Test 1: Health
Write-Host "1. Testing Health Endpoint" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$BASE_URL/health" -Method Get -UseBasicParsing
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
    Write-Host ""
} catch {
    Write-Host "❌ Failed: $_" -ForegroundColor Red
    Write-Host ""
}

# Test 2: Root
Write-Host "2. Testing Root Endpoint" -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$BASE_URL/" -Method Get -UseBasicParsing
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
    Write-Host ""
} catch {
    Write-Host "❌ Failed: $_" -ForegroundColor Red
    Write-Host ""
}

# Test 3: Opportunity Analysis
Write-Host "3. Testing Opportunity Analysis" -ForegroundColor Yellow
try {
    $body = @{
        title = "Projeto ERP Cloud - Enterprise"
        description = "Implementação completa de sistema ERP na nuvem"
        value = 500000
        probability = 75
        status = "proposal"
        customer_name = "Multinacional XYZ"
        sector = "Manufatura"
    } | ConvertTo-Json

    $response = Invoke-WebRequest -Uri "$BASE_URL/analyze" -Method Post `
        -ContentType "application/json" `
        -Body $body `
        -UseBasicParsing
    
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
    Write-Host ""
} catch {
    Write-Host "❌ Failed: $_" -ForegroundColor Red
    Write-Host ""
}

# Test 4: Chat
Write-Host "4. Testing Chat Endpoint" -ForegroundColor Yellow
try {
    $body = @{
        question = "Qual é a melhor estratégia para fechar uma grande oportunidade?"
    } | ConvertTo-Json

    $response = Invoke-WebRequest -Uri "$BASE_URL/api/v1/chat" -Method Post `
        -ContentType "application/json" `
        -Body $body `
        -UseBasicParsing
    
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
    Write-Host ""
} catch {
    Write-Host "❌ Failed: $_" -ForegroundColor Red
    Write-Host ""
}

# Test 5: Legacy endpoint
Write-Host "5. Testing Legacy Endpoint (backward compatibility)" -ForegroundColor Yellow
try {
    $body = @{
        title = "Suporte técnico"
        value = 10000
        probability = 90
    } | ConvertTo-Json

    $response = Invoke-WebRequest -Uri "$BASE_URL/ai/opportunity-insights" -Method Post `
        -ContentType "application/json" `
        -Body $body `
        -UseBasicParsing
    
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
    Write-Host ""
} catch {
    Write-Host "❌ Failed: $_" -ForegroundColor Red
    Write-Host ""
}

Write-Host "✅ Tests completed!" -ForegroundColor Green
Write-Host ""
Write-Host "📊 Summary:" -ForegroundColor Cyan
Write-Host "- If you see JSON responses above, the service is working!"
Write-Host "- If risk_score is 50 and cached=true, GEMINI_API_KEY is not configured"
Write-Host "- Configure it with: `$env:GEMINI_API_KEY='your_key_here'"
