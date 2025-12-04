@echo off
REM WK CRM Brasil - Alternativa XAMPP (sem Docker)
REM Para rodar o sistema usando XAMPP enquanto resolve Docker

echo ========================================
echo   WK CRM Brasil - Modo XAMPP
echo ========================================
echo.

echo 🔧 Iniciando WK CRM usando XAMPP (sem Docker)...
echo.

REM Verificar se XAMPP está rodando
echo 📦 Verificando XAMPP...
tasklist | find "httpd.exe" >nul
if %errorlevel% equ 0 (
    echo ✅ Apache está rodando!
) else (
    echo ❌ Apache não está rodando!
    echo Inicie o XAMPP Control Panel e ligue Apache + MySQL
    pause
    exit /b 1
)

tasklist | find "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo ✅ MySQL está rodando!
) else (
    echo ❌ MySQL não está rodando!
    echo Inicie o XAMPP Control Panel e ligue MySQL
    pause
    exit /b 1
)

echo.
echo 🚀 Iniciando Laravel via PHP built-in server...
echo.

REM Navegar para o diretório Laravel
cd /d "C:\xampp\htdocs\crm\wk-crm-laravel"

REM Verificar se .env existe
if not exist ".env" (
    echo 📝 Criando arquivo .env...
    copy .env.example .env
    php artisan key:generate
)

REM Configurar banco para MySQL (XAMPP)
echo 🔧 Configurando para MySQL...
powershell -Command "(gc .env) -replace 'DB_CONNECTION=pgsql', 'DB_CONNECTION=mysql' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'DB_HOST=postgres', 'DB_HOST=127.0.0.1' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'DB_PORT=5432', 'DB_PORT=3306' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'DB_DATABASE=wk_main', 'DB_DATABASE=wk_crm' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'DB_USERNAME=wk_user', 'DB_USERNAME=root' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'DB_PASSWORD=secure_password', 'DB_PASSWORD=' | Out-File -encoding ASCII .env"

echo ✅ Configuração MySQL aplicada!
echo.

REM Criar banco se não existir
echo 🗄️ Criando banco de dados...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS wk_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

REM Executar migrations (se existirem)
echo 📊 Executando migrations...
php artisan migrate --force 2>nul

REM Iniciar servidor Laravel
echo 🌐 Iniciando servidor Laravel na porta 8001...
echo.
echo URLs disponíveis:
echo   - API: http://localhost:8001
echo   - Health: http://localhost:8001/api/health
echo   - Dashboard: http://localhost:8001/api/dashboard
echo.
echo Pressione Ctrl+C para parar o servidor
echo.

start http://localhost:8001
php artisan serve --host=0.0.0.0 --port=8001