# 🚀 Deploy Automático VPS - PowerShell Script
# Execute este script no Windows para fazer deploy na VPS Hostinger

param(
    [string]$VPS_IP = "72.60.254.100",
    [string]$VPS_USER = "root",
    [switch]$TestOnly = $false,
    [switch]$BackupFirst = $true
)

# Cores para output
$Red = "`e[31m"
$Green = "`e[32m"
$Yellow = "`e[33m"
$Blue = "`e[34m"
$Reset = "`e[0m"

function Write-Step {
    param([string]$Message)
    Write-Host "${Green}[INFO]${Reset} $Message"
}

function Write-Warning {
    param([string]$Message)
    Write-Host "${Yellow}[WARNING]${Reset} $Message"
}

function Write-Error {
    param([string]$Message)
    Write-Host "${Red}[ERROR]${Reset} $Message"
}

function Write-Title {
    param([string]$Message)
    Write-Host "${Blue}================================${Reset}"
    Write-Host "${Blue}$Message${Reset}"
    Write-Host "${Blue}================================${Reset}"
}

Write-Title "🚀 WK CRM - Deploy VPS Hostinger"

# Verificar se SSH está disponível
if (-not (Get-Command ssh -ErrorAction SilentlyContinue)) {
    Write-Error "SSH não encontrado. Instale OpenSSH ou use PuTTY."
    exit 1
}

Write-Step "Conectando à VPS: $VPS_USER@$VPS_IP"

if ($TestOnly) {
    Write-Warning "Modo TESTE - Apenas verificação do sistema"
    
    $TestCommands = @"
echo '🧪 TESTE: Verificando sistema na VPS...'
echo '📁 Verificando diretório do projeto:'
ls -la /opt/wk-crm/
echo ''
echo '🔄 Verificando status do Git:'
cd /opt/wk-crm && git status
echo ''
echo '🌐 Verificando serviços:'
systemctl is-active nginx
systemctl is-active php8.2-fpm
systemctl is-active postgresql
echo ''
echo '🔍 Testando URLs:'
curl -I http://localhost/admin/ | head -1
curl -X GET http://localhost/api/health | head -1
echo ''
echo '✅ Teste concluído!'
"@

    ssh $VPS_USER@$VPS_IP $TestCommands
    
} else {
    Write-Step "Executando deploy completo na VPS..."
    
    # Build Angular production locally and upload to VPS
    Write-Step "📦 Building Angular production (local)..."
    Push-Location "wk-admin-frontend"
    try {
        npm run build:prod
    } catch {
        Write-Error "Falha ao executar 'npm run build:prod'. Verifique dependências e tente novamente."
        Pop-Location
        exit 1
    }
    Pop-Location

    Write-Step "📤 Limpando /var/www/html/admin no VPS e enviando build..."
    # Remove arquivos antigos no VPS
    ssh $VPS_USER@$VPS_IP "rm -rf /var/www/html/admin/* || true"

    # Envia build (usa dist/admin-frontend)
    Push-Location "wk-admin-frontend"
    try {
        $dest = $VPS_USER + '@' + $VPS_IP + ':/var/www/html/admin/'
        & scp -r .\dist\admin-frontend\* $dest
    } catch {
        Write-Error "Falha ao enviar arquivos via scp: $_"
        Pop-Location
        exit 1
    }
    Pop-Location

    # Ajustar permissões no VPS
    ssh $VPS_USER@$VPS_IP "chown -R www-data:www-data /var/www/html/admin && chmod -R 755 /var/www/html/admin"

    $DeployCommands = @"
echo '🚀 Iniciando deploy do WK CRM...'

# Navegar para o projeto
cd /opt/wk-crm

# Fazer backup se solicitado
if [ '$BackupFirst' = 'True' ]; then
    echo '💾 Fazendo backup...'
    cp -r /opt/wk-crm /opt/wk-crm-backup-`$(date +%Y%m%d_%H%M%S)
fi

echo '📥 Atualizando código do Git...'
git fetch origin
git reset --hard origin/main
git pull origin main --no-edit

echo '📊 Últimos commits:'
git log --oneline -3

echo '⚙️ Atualizando Laravel...'
cd /opt/wk-crm/wk-crm-laravel

# Garantir que os containers estão rodando e executar comandos dentro do container
cd /opt/wk-crm
echo '🔁 Garantindo que containers Docker estejam ativos...'
docker compose up -d

echo '🔧 Executando comandos Laravel dentro do container wk-crm-laravel'
docker compose exec -T wk-crm-laravel php artisan config:clear || true
docker compose exec -T wk-crm-laravel php artisan route:clear || true
docker compose exec -T wk-crm-laravel php artisan view:clear || true
docker compose exec -T wk-crm-laravel php artisan cache:clear || true

echo '📦 Instalando dependências via composer dentro do container'
docker compose exec -T wk-crm-laravel composer install --optimize-autoloader --no-dev || true

echo '🗃️ Aplicando migrations dentro do container'
docker compose exec -T wk-crm-laravel php artisan migrate --force || true

echo '⚡ Recriando caches dentro do container'
docker compose exec -T wk-crm-laravel php artisan config:cache || true
docker compose exec -T wk-crm-laravel php artisan route:cache || true
docker compose exec -T wk-crm-laravel php artisan view:cache || true

# Permissões
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo '🎨 Atualizando AdminLTE...'
echo '🎨 Admin build enviado (via scp). Permissões atualizadas.'

echo '🔄 Reiniciando serviços...'
systemctl restart php8.2-fpm
systemctl reload nginx

echo '🧪 Testando sistema...'
echo 'AdminLTE:'
curl -I http://localhost/admin/ | head -1

echo 'API Health:'
curl -X GET http://localhost/api/health

echo 'API Dashboard:'
curl -X GET http://localhost/api/dashboard

echo ''
echo '🎉 Deploy concluído!'
echo '🌐 URLs para testar:'
echo '   AdminLTE: https://consultoriawk.com/admin/'
echo '   API: https://api.consultoriawk.com/api/health'
"@

    ssh $VPS_USER@$VPS_IP $DeployCommands
}

Write-Step "✅ Operação concluída!"

# Testar URLs externas
Write-Step "🌐 Testando URLs externas..."

try {
    $AdminTest = Invoke-WebRequest -Uri "https://consultoriawk.com/admin/" -Method Head -TimeoutSec 10
    if ($AdminTest.StatusCode -eq 200) {
        Write-Step "✅ AdminLTE acessível externamente"
    }
} catch {
    Write-Warning "⚠️ AdminLTE pode não estar acessível: $($_.Exception.Message)"
}

try {
    $ApiTest = Invoke-WebRequest -Uri "https://api.consultoriawk.com/api/health" -TimeoutSec 10
    if ($ApiTest.StatusCode -eq 200) {
        Write-Step "✅ API acessível externamente"
        Write-Step "📊 Resposta da API: $($ApiTest.Content)"
    }
} catch {
    Write-Warning "⚠️ API pode não estar acessível: $($_.Exception.Message)"
}

Write-Title "🎯 Deploy Finalizado!"
Write-Host ""
Write-Host "📋 Próximos passos:"
Write-Host "1. ✅ Testar AdminLTE: ${Blue}https://consultoriawk.com/admin/${Reset}"
Write-Host "2. ✅ Testar API: ${Blue}https://api.consultoriawk.com/api/health${Reset}"
Write-Host "3. ✅ Verificar dados: ${Blue}https://api.consultoriawk.com/api/dashboard${Reset}"
Write-Host ""
Write-Host "🔧 Em caso de problemas:"
Write-Host "   - Execute: ${Yellow}.\deploy-vps.ps1 -TestOnly${Reset}"
Write-Host "   - Verifique logs: ${Yellow}ssh root@72.60.254.100 'tail -f /var/log/nginx/error.log'${Reset}"
Write-Host ""