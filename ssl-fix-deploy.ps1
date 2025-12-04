# 🔧 SSL Fix Deployment Script
# Executa o processo de diagnóstico e correção SSL na VPS

param(
    [string]$VPS_IP = "72.60.254.100",
    [string]$VPS_USER = "root",
    [ValidateSet("check", "renew", "health", "all")]
    [string]$Action = "all",
    [switch]$DryRun = $false
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

Write-Title "🔒 SSL Fix Deployment - WK CRM"

# Verificar se SSH está disponível
if (-not (Get-Command ssh -ErrorAction SilentlyContinue)) {
    Write-Error "SSH não encontrado. Instale OpenSSH."
    exit 1
}

if ($DryRun) {
    Write-Warning "MODO DRY-RUN - Apenas simulação"
}

Write-Step "Conectando à VPS: $VPS_USER@$VPS_IP"

# Função para executar comando na VPS
function Invoke-VPSCommand {
    param([string]$Command, [string]$Description)
    
    Write-Step $Description
    if ($DryRun) {
        Write-Host "DRY-RUN: $Command"
        return
    }
    
    ssh $VPS_USER@$VPS_IP $Command
    
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Falha ao executar: $Description"
        return $false
    }
    return $true
}

# Copiar scripts para a VPS
Write-Step "📁 Copiando scripts para a VPS..."
if (-not $DryRun) {
    scp scripts/*.sh "${VPS_USER}@${VPS_IP}:/opt/wk-crm/scripts/"
    ssh $VPS_USER@$VPS_IP "chmod +x /opt/wk-crm/scripts/*.sh"
}

switch ($Action) {
    "check" {
        Write-Title "🔍 Executando Diagnóstico SSL"
        Invoke-VPSCommand "/opt/wk-crm/scripts/ssl-check.sh" "Diagnóstico SSL"
    }
    
    "renew" {
        Write-Title "🔄 Executando Renovação SSL"
        Invoke-VPSCommand "/opt/wk-crm/scripts/ssl-renew.sh" "Renovação SSL"
    }
    
    "health" {
        Write-Title "🏥 Executando Health Check"
        Invoke-VPSCommand "/opt/wk-crm/scripts/health-check.sh" "Health Check do Sistema"
    }
    
    "all" {
        Write-Title "🔄 Executando Processo Completo"
        
        # 1. Diagnóstico
        if (Invoke-VPSCommand "/opt/wk-crm/scripts/ssl-check.sh" "Diagnóstico SSL inicial") {
            Write-Step "✅ Diagnóstico inicial concluído"
        } else {
            Write-Error "❌ Falha no diagnóstico inicial"
        }
        
        # 2. Renovação
        if (Invoke-VPSCommand "/opt/wk-crm/scripts/ssl-renew.sh" "Renovação de certificados SSL") {
            Write-Step "✅ Renovação SSL concluída"
        } else {
            Write-Warning "⚠️ Renovação SSL teve problemas"
        }
        
        # 3. Health Check final
        if (Invoke-VPSCommand "/opt/wk-crm/scripts/health-check.sh" "Health check final") {
            Write-Step "✅ Health check final passou"
        } else {
            Write-Warning "⚠️ Health check detectou problemas"
        }
    }
}

Write-Step "🧪 Testando URLs externas após correção..."

# Testar URLs externas
$urls = @(
    "https://api.consultoriawk.com/api/health",
    "https://admin.consultoriawk.com/"
)

foreach ($url in $urls) {
    try {
        if (-not $DryRun) {
            $response = Invoke-WebRequest -Uri $url -Method Head -TimeoutSec 10 -ErrorAction Stop
            if ($response.StatusCode -eq 200) {
                Write-Step "✅ $url - FUNCIONANDO"
            } else {
                Write-Warning "⚠️ $url - Status: $($response.StatusCode)"
            }
        } else {
            Write-Host "DRY-RUN: Testaria $url"
        }
    } catch {
        Write-Warning "⚠️ $url - Erro: $($_.Exception.Message)"
    }
}

Write-Title "🎯 SSL Fix Deployment Finalizado!"

Write-Host ""
Write-Host "📋 Próximos passos:"
Write-Host "1. ✅ Verificar URLs:"
Write-Host "   - ${Blue}https://api.consultoriawk.com/api/health${Reset}"
Write-Host "   - ${Blue}https://admin.consultoriawk.com/${Reset}"
Write-Host ""
Write-Host "2. ✅ Monitorar logs se necessário:"
Write-Host "   - ${Yellow}ssh $VPS_USER@$VPS_IP 'tail -f /var/log/nginx/error.log'${Reset}"
Write-Host "   - ${Yellow}ssh $VPS_USER@$VPS_IP 'tail -f /opt/wk-crm/wk-crm-laravel/storage/logs/laravel.log'${Reset}"
Write-Host ""
Write-Host "3. ✅ Para executar individualmente:"
Write-Host "   - ${Yellow}.\ssl-fix-deploy.ps1 -Action check${Reset}   # Só diagnóstico"
Write-Host "   - ${Yellow}.\ssl-fix-deploy.ps1 -Action renew${Reset}   # Só renovação"
Write-Host "   - ${Yellow}.\ssl-fix-deploy.ps1 -Action health${Reset}  # Só health check"
Write-Host ""

if ($DryRun) {
    Write-Warning "Este foi um DRY-RUN. Execute sem -DryRun para aplicar as mudanças."
}