# WK AI Service - Quick Start (Windows)
# Este script inicia o serviço em modo demo (sem dependências externas)

Write-Host "🤖 WK AI Service - Quick Start" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Caminho para o serviço
$servicePath = Split-Path -Parent $MyInvocation.MyCommand.Path
Write-Host "📁 Serviço: $servicePath" -ForegroundColor Yellow
Write-Host ""

# Verificar Python
Write-Host "✅ Verificando Python..." -ForegroundColor Yellow
$pythonVersion = & python --version 2>&1
Write-Host "   $pythonVersion" -ForegroundColor Green
Write-Host ""

# Iniciar serviço em novo terminal
Write-Host "🚀 Iniciando serviço..." -ForegroundColor Yellow
Write-Host ""

Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$servicePath'; python main_simple.py"

# Aguardar inicialização
Start-Sleep -Seconds 2

# Testar
Write-Host ""
Write-Host "🧪 Testando endpoints..." -ForegroundColor Yellow
Write-Host ""

# Health check
Write-Host "1. GET /health" -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/health" -Method Get -UseBasicParsing -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        Write-Host "   ✅ OK" -ForegroundColor Green
        $response.Content | ConvertFrom-Json | ConvertTo-Json
    }
} catch {
    Write-Host "   ❌ Erro: $_" -ForegroundColor Red
}

Write-Host ""

# Analyze test
Write-Host "2. POST /analyze" -ForegroundColor Cyan
try {
    $body = @{
        title = "Projeto ERP"
        value = 250000
        probability = 75
    } | ConvertTo-Json
    
    $response = Invoke-WebRequest -Uri "http://localhost:8000/analyze" -Method Post `
        -ContentType "application/json" `
        -Body $body `
        -UseBasicParsing `
        -ErrorAction SilentlyContinue
    
    if ($response.StatusCode -eq 200) {
        Write-Host "   ✅ OK" -ForegroundColor Green
        $response.Content | ConvertFrom-Json | ConvertTo-Json
    }
} catch {
    Write-Host "   ❌ Erro: $_" -ForegroundColor Red
}

Write-Host ""

# Chat test
Write-Host "3. POST /api/v1/chat" -ForegroundColor Cyan
try {
    $body = @{
        question = "Como aumentar vendas?"
    } | ConvertTo-Json
    
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/chat" -Method Post `
        -ContentType "application/json" `
        -Body $body `
        -UseBasicParsing `
        -ErrorAction SilentlyContinue
    
    if ($response.StatusCode -eq 200) {
        Write-Host "   ✅ OK" -ForegroundColor Green
        $response.Content | ConvertFrom-Json | ConvertTo-Json
    }
} catch {
    Write-Host "   ❌ Erro: $_" -ForegroundColor Red
}

Write-Host ""
Write-Host "✅ Testes concluídos!" -ForegroundColor Green
Write-Host ""
Write-Host "📝 Nota: O serviço está rodando em modo DEMO" -ForegroundColor Yellow
Write-Host "   Para usar Google Gemini real, configure GEMINI_API_KEY" -ForegroundColor Yellow
Write-Host "   e use main.py com FastAPI" -ForegroundColor Yellow
