@echo off
REM WK CRM Brasil - Inicializador Local (Sem Redis)
echo ========================================
echo   WK CRM Brasil - Servidor Local
echo ========================================
echo.

cd /d "C:\xampp\htdocs\crm\wk-crm-laravel"

echo 🔧 Configurando cache para file system...
echo CACHE_STORE=file > temp_env.txt
echo SESSION_DRIVER=file >> temp_env.txt
echo QUEUE_CONNECTION=database >> temp_env.txt

REM Atualizar .env
powershell -Command "(gc .env) -replace 'CACHE_STORE=redis', 'CACHE_STORE=file' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'SESSION_DRIVER=redis', 'SESSION_DRIVER=file' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'QUEUE_CONNECTION=redis', 'QUEUE_CONNECTION=database' | Out-File -encoding ASCII .env"

echo ✅ Configuração atualizada!

echo 🧹 Limpando cache...
if exist "storage\framework\cache\data" rmdir /s /q "storage\framework\cache\data"
if exist "storage\framework\views" rmdir /s /q "storage\framework\views"
if exist "bootstrap\cache" del /q "bootstrap\cache\*.php"

mkdir storage\framework\cache\data 2>nul
mkdir storage\framework\views 2>nul

echo ✅ Cache limpo!

echo 🚀 Iniciando servidor na porta 8001...
echo.
echo URLs disponíveis:
echo   - Raiz: http://localhost:8001
echo   - Health: http://localhost:8001/api/health
echo   - Dashboard: http://localhost:8001/api/dashboard
echo.

start http://localhost:8001
php artisan serve --host=0.0.0.0 --port=8001