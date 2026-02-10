@echo off
REM Restart Docker Containers Script
echo.
echo === Iniciando Containers ===
echo.

cd /d c:\xampp\htdocs\wk-crm-microservices

echo Removendo containers antigos...
docker-compose down --remove-orphans
timeout /t 3 /nobreak

echo.
echo Iniciando containers...
docker-compose up -d

timeout /t 10 /nobreak

echo.
echo === Status dos Containers ===
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo.
echo === Verificando Health Check ===
docker ps | find "wk_ai_service"
docker ps | find "wk_crm_laravel"

echo.
echo Operacao concluida!
pause
