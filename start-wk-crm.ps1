#!/usr/bin/env powershell
<#
.SYNOPSIS
    Script para inicializar o ambiente completo WK CRM Brasil

.DESCRIPTION
    Este script verifica e inicializa todos os containers Docker necessários
    para o funcionamento completo do sistema WK CRM microservices.

.NOTES
    Arquitetura: DDD + SOLID + TDD
    Localização: Brasil - Português Brasileiro
#>

param(
    [switch]$Build = $false,
    [switch]$Clean = $false,
    [switch]$Logs = $false
)

Write-Host "🚀 WK CRM Brasil - Inicializador do Ambiente" -ForegroundColor Cyan
Write-Host "=" * 50 -ForegroundColor Gray

# Verificar se Docker está rodando
Write-Host "🐳 Verificando Docker..." -ForegroundColor Yellow
try {
    $dockerVersion = docker --version
    Write-Host "✅ Docker encontrado: $dockerVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker não está instalado ou não está rodando!" -ForegroundColor Red
    Write-Host "Por favor, instale o Docker Desktop e tente novamente." -ForegroundColor Red
    exit 1
}

# Verificar se Docker Compose está disponível
try {
    $composeVersion = docker compose version
    Write-Host "✅ Docker Compose encontrado: $composeVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker Compose não está disponível!" -ForegroundColor Red
    exit 1
}

# Verificar se estamos no diretório correto
if (-not (Test-Path "docker-compose.yml")) {
    Write-Host "❌ Arquivo docker-compose.yml não encontrado!" -ForegroundColor Red
    Write-Host "Execute este script no diretório raiz do projeto WK CRM." -ForegroundColor Red
    exit 1
}

Write-Host "`n🔧 Configurações:" -ForegroundColor Yellow

# Limpeza (se solicitado)
if ($Clean) {
    Write-Host "🧹 Limpando containers e volumes existentes..." -ForegroundColor Yellow
    docker compose down -v --remove-orphans
    docker system prune -f
    Write-Host "✅ Limpeza concluída!" -ForegroundColor Green
}

# Build (se solicitado ou primeira execução)
if ($Build -or $Clean) {
    Write-Host "🔨 Construindo containers..." -ForegroundColor Yellow
    docker compose build --no-cache
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Erro na construção dos containers!" -ForegroundColor Red
        exit 1
    }
    Write-Host "✅ Build concluído!" -ForegroundColor Green
}

# Iniciar serviços
Write-Host "`n🚀 Iniciando microservices..." -ForegroundColor Yellow

# Iniciar serviços de infraestrutura primeiro
Write-Host "📦 Iniciando serviços de infraestrutura (PostgreSQL, Redis)..." -ForegroundColor Cyan
docker compose up -d postgres redis

# Aguardar serviços ficarem prontos
Write-Host "⏳ Aguardando serviços ficarem prontos..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Verificar se PostgreSQL está pronto
$maxAttempts = 30
$attempt = 0
do {
    $attempt++
    Write-Host "Tentativa $attempt/$maxAttempts - Verificando PostgreSQL..." -ForegroundColor Gray
    $pgReady = docker compose exec -T postgres pg_isready -U wk_user 2>$null
    if ($pgReady -like "*accepting connections*") {
        Write-Host "✅ PostgreSQL está pronto!" -ForegroundColor Green
        break
    }
    Start-Sleep -Seconds 2
} while ($attempt -lt $maxAttempts)

if ($attempt -eq $maxAttempts) {
    Write-Host "❌ PostgreSQL não ficou pronto a tempo!" -ForegroundColor Red
    docker compose logs postgres
    exit 1
}

# Iniciar serviços backend
Write-Host "⚙️ Iniciando serviços backend..." -ForegroundColor Cyan
docker compose up -d wk-crm-laravel wk-crm-dotnet wk-products-api wk-ai-service

# Aguardar backends ficarem prontos
Start-Sleep -Seconds 15

# Iniciar gateway
Write-Host "🌐 Iniciando API Gateway..." -ForegroundColor Cyan
docker compose up -d wk-gateway

# Aguardar gateway ficar pronto
Start-Sleep -Seconds 10

# Iniciar frontends
Write-Host "🎨 Iniciando aplicações frontend..." -ForegroundColor Cyan
docker compose up -d wk-admin-frontend wk-customer-app

# Iniciar nginx (proxy reverso)
Write-Host "🔄 Iniciando Nginx..." -ForegroundColor Cyan
docker compose up -d nginx

# Verificar status dos serviços
Write-Host "`n📊 Status dos serviços:" -ForegroundColor Yellow
docker compose ps

# Verificar saúde dos serviços
Write-Host "`n🏥 Verificando saúde dos serviços..." -ForegroundColor Yellow

$services = @(
    @{Name="PostgreSQL"; Url=""; Command="docker compose exec -T postgres pg_isready -U wk_user"},
    @{Name="Redis"; Url=""; Command="docker compose exec -T redis redis-cli ping"},
    @{Name="Laravel API"; Url="http://localhost:8000/api/health"; Command=""},
    @{Name="Gateway"; Url="http://localhost:3000/health"; Command=""},
    @{Name="Admin Frontend"; Url="http://localhost:4200"; Command=""},
    @{Name="Customer App"; Url="http://localhost:3002"; Command=""}
)

foreach ($service in $services) {
    Write-Host "Verificando $($service.Name)..." -ForegroundColor Gray
    
    if ($service.Command) {
        try {
            $result = Invoke-Expression $service.Command
            if ($result -like "*OK*" -or $result -like "*ready*" -or $result -like "*PONG*") {
                Write-Host "✅ $($service.Name) está funcionando!" -ForegroundColor Green
            } else {
                Write-Host "⚠️ $($service.Name) pode ter problemas." -ForegroundColor Yellow
            }
        } catch {
            Write-Host "❌ $($service.Name) não está respondendo." -ForegroundColor Red
        }
    } elseif ($service.Url) {
        try {
            $response = Invoke-WebRequest -Uri $service.Url -TimeoutSec 5 -UseBasicParsing
            if ($response.StatusCode -eq 200) {
                Write-Host "✅ $($service.Name) está funcionando!" -ForegroundColor Green
            } else {
                Write-Host "⚠️ $($service.Name) retornou status $($response.StatusCode)" -ForegroundColor Yellow
            }
        } catch {
            Write-Host "❌ $($service.Name) não está acessível." -ForegroundColor Red
        }
    }
}

# Mostrar URLs importantes
Write-Host "`n🌐 URLs do sistema:" -ForegroundColor Cyan
Write-Host "API Gateway:      http://localhost:3000" -ForegroundColor White
Write-Host "Laravel API:      http://localhost:8000" -ForegroundColor White
Write-Host "Admin Frontend:   http://localhost:4200" -ForegroundColor White
Write-Host "Customer App:     http://localhost:3002" -ForegroundColor White
Write-Host "Nginx (Proxy):    http://localhost:80" -ForegroundColor White

Write-Host "`n📋 APIs disponíveis:" -ForegroundColor Cyan
Write-Host "Health Check:     http://localhost:3000/health" -ForegroundColor White
Write-Host "Laravel Health:   http://localhost:8000/api/health" -ForegroundColor White
Write-Host "Dashboard:        http://localhost:8000/api/dashboard" -ForegroundColor White
Write-Host "Clientes:         http://localhost:8000/api/clientes" -ForegroundColor White
Write-Host "Leads:            http://localhost:8000/api/leads" -ForegroundColor White
Write-Host "Oportunidades:    http://localhost:8000/api/oportunidades" -ForegroundColor White

# Mostrar logs se solicitado
if ($Logs) {
    Write-Host "`n📝 Mostrando logs dos containers..." -ForegroundColor Yellow
    docker compose logs --tail=50
}

Write-Host "`n🎉 Ambiente WK CRM Brasil iniciado com sucesso!" -ForegroundColor Green
Write-Host "Para parar todos os containers: docker compose down" -ForegroundColor Gray
Write-Host "Para ver logs em tempo real: docker compose logs -f" -ForegroundColor Gray