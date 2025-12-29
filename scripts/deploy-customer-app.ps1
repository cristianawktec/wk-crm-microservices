# Deploy Vue Customer App to VPS
param(
    [string]$VPS_HOST = "72.60.254.100",
    [string]$VPS_USER = "root",
    [string]$AppPath = "/var/www/html/app",
    [switch]$BuildOnly = $false
)

$ErrorActionPreference = "Stop"

function Info($m){ Write-Host "[INFO] $m" }
function Warn($m){ Write-Host "[WARN] $m" }
function Fail($m){ Write-Host "[ERROR] $m"; exit 1 }

try {
    Info "Building wk-customer-app (production)"
    Push-Location "$PSScriptRoot/../wk-customer-app"
    if (-not (Get-Command npm -ErrorAction SilentlyContinue)) { Fail "npm não encontrado. Instale Node.js/NPM." }
    npm ci
    npm run build
} catch {
    Pop-Location
    Fail "Falha ao buildar: $($_.Exception.Message)"
}
Pop-Location

if ($BuildOnly) {
    Warn "BuildOnly ativo — não enviando para VPS"
    exit 0
}

try {
    Info "Limpando destino no VPS: $AppPath"
    ssh "$VPS_USER@$VPS_HOST" "rm -rf $AppPath/* || true"

    Info "Enviando dist para VPS"
    $dist = "$PSScriptRoot/../wk-customer-app/dist/*"
    scp -r $dist "$VPS_USER@$VPS_HOST:$AppPath/"

    Info "Ajustando permissões e recarregando nginx"
    ssh "$VPS_USER@$VPS_HOST" "chown -R www-data:www-data $AppPath && chmod -R 755 $AppPath && systemctl reload nginx"

    Info "Verificação rápida de logs"
    ssh "$VPS_USER@$VPS_HOST" "tail -n 50 /var/log/nginx/error.log || true"
    ssh "$VPS_USER@$VPS_HOST" "tail -n 50 /var/log/php8.2-fpm.log || true"

    Info "✅ Deploy do customer app concluído"
} catch {
    Fail "Falha no deploy: $($_.Exception.Message)"
}
