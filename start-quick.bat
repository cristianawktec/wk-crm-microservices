@echo off
REM WK CRM Brasil - Inicializador Rápido
REM Arquitetura: DDD + SOLID + TDD - Português Brasileiro

echo ========================================
echo    WK CRM Brasil - Microservices
echo ========================================
echo.

echo 🐳 Verificando Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker nao esta instalado ou rodando!
    pause
    exit /b 1
)

echo ✅ Docker OK!
echo.

echo 🚀 Iniciando microservices...
echo.

REM Parar containers existentes
echo 🛑 Parando containers existentes...
docker compose down

REM Iniciar serviços de infraestrutura
echo 📦 Iniciando PostgreSQL e Redis...
docker compose up -d postgres redis

REM Aguardar 10 segundos
echo ⏳ Aguardando 10 segundos...
timeout /t 10 /nobreak >nul

REM Iniciar serviços backend
echo ⚙️ Iniciando APIs backend...
docker compose up -d wk-crm-laravel wk-gateway

REM Aguardar 15 segundos
echo ⏳ Aguardando 15 segundos...
timeout /t 15 /nobreak >nul

REM Mostrar status
echo.
echo 📊 Status dos containers:
docker compose ps

echo.
echo 🌐 URLs disponíveis:
echo   - Laravel API: http://localhost:8000
echo   - API Gateway: http://localhost:3000
echo   - Health Check: http://localhost:8000/api/health
echo   - Dashboard: http://localhost:8000/api/dashboard
echo.

echo 🎉 Sistema iniciado! Pressione qualquer tecla para continuar...
pause >nul