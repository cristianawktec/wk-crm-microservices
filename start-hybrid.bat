@echo off
REM WK CRM Brasil - Modo Híbrido (Docker + XAMPP)
REM Docker: PostgreSQL + Redis | XAMPP: Laravel API

echo ========================================
echo   WK CRM Brasil - Modo Híbrido
echo ========================================
echo.

echo 🐳 Verificando Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker não está disponível!
    echo Usando apenas XAMPP...
    goto :xampp_only
)

echo ✅ Docker OK!
echo.

echo 📦 Iniciando infraestrutura Docker...
docker compose up -d postgres redis

echo ⏳ Aguardando PostgreSQL ficar pronto...
timeout /t 10 /nobreak >nul

REM Verificar se PostgreSQL está pronto
:check_postgres
docker compose exec -T postgres pg_isready -U wk_user >nul 2>&1
if %errorlevel% neq 0 (
    echo ⏳ PostgreSQL ainda não está pronto, aguardando...
    timeout /t 3 /nobreak >nul
    goto :check_postgres
)

echo ✅ PostgreSQL está pronto!
echo.

echo 🔧 Configurando Laravel para usar PostgreSQL Docker...
cd wk-crm-laravel

REM Criar .env com PostgreSQL Docker
echo APP_NAME="WK CRM Brasil" > .env
echo APP_ENV=local >> .env
echo APP_KEY= >> .env
echo APP_DEBUG=true >> .env
echo APP_TIMEZONE="America/Sao_Paulo" >> .env
echo APP_URL=http://localhost:8001 >> .env
echo APP_LOCALE=pt_BR >> .env
echo APP_FALLBACK_LOCALE=en >> .env
echo APP_FAKER_LOCALE=pt_BR >> .env
echo. >> .env
echo LOG_CHANNEL=stack >> .env
echo LOG_LEVEL=debug >> .env
echo. >> .env
echo # PostgreSQL Docker >> .env
echo DB_CONNECTION=pgsql >> .env
echo DB_HOST=127.0.0.1 >> .env
echo DB_PORT=5432 >> .env
echo DB_DATABASE=wk_main >> .env
echo DB_USERNAME=wk_user >> .env
echo DB_PASSWORD=secure_password_123 >> .env
echo. >> .env
echo # Redis Docker >> .env
echo REDIS_HOST=127.0.0.1 >> .env
echo REDIS_PASSWORD=redis_password_123 >> .env
echo REDIS_PORT=6379 >> .env
echo. >> .env
echo CACHE_STORE=redis >> .env
echo SESSION_DRIVER=redis >> .env
echo QUEUE_CONNECTION=redis >> .env

echo 🔑 Gerando chave da aplicação...
php artisan key:generate

echo 📊 Executando migrations...
php artisan migrate --force

goto :start_server

:xampp_only
echo 📦 Modo XAMPP apenas (MySQL)...
cd wk-crm-laravel

REM Criar .env com MySQL XAMPP
echo APP_NAME="WK CRM Brasil" > .env
echo APP_ENV=local >> .env
echo APP_KEY= >> .env
echo APP_DEBUG=true >> .env
echo APP_TIMEZONE="America/Sao_Paulo" >> .env
echo APP_URL=http://localhost:8001 >> .env
echo APP_LOCALE=pt_BR >> .env
echo APP_FALLBACK_LOCALE=en >> .env
echo APP_FAKER_LOCALE=pt_BR >> .env
echo. >> .env
echo LOG_CHANNEL=stack >> .env
echo LOG_LEVEL=debug >> .env
echo. >> .env
echo # MySQL XAMPP >> .env
echo DB_CONNECTION=mysql >> .env
echo DB_HOST=127.0.0.1 >> .env
echo DB_PORT=3306 >> .env
echo DB_DATABASE=wk_crm >> .env
echo DB_USERNAME=root >> .env
echo DB_PASSWORD= >> .env
echo. >> .env
echo CACHE_STORE=file >> .env
echo SESSION_DRIVER=file >> .env
echo QUEUE_CONNECTION=database >> .env

echo 🔑 Gerando chave da aplicação...
php artisan key:generate

echo 🗄️ Criando banco MySQL...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS wk_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul

echo 📊 Executando migrations...
php artisan migrate --force

:start_server
echo.
echo 🌐 Iniciando servidor Laravel...
echo.
echo URLs disponíveis:
echo   - API: http://localhost:8001
echo   - Health: http://localhost:8001/api/health
echo   - Dashboard: http://localhost:8001/api/dashboard
echo   - Produção: https://api.consultoriawk.com
echo   - Admin: https://admin.consultoriawk.com
echo.
echo 📊 Status Docker:
docker compose ps 2>nul
echo.
echo Pressione Ctrl+C para parar
echo.

start http://localhost:8001/api/health
php artisan serve --host=0.0.0.0 --port=8001