# WK CRM Brasil - Docker Desktop PowerShell Fix
# Script para habilitar recursos necessários no Windows

param(
    [switch]$Force = $false
)

Write-Host "🐳 WK CRM Brasil - Docker Desktop Fix" -ForegroundColor Cyan
Write-Host "=" * 50 -ForegroundColor Gray

# Verificar se está rodando como administrador
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "❌ Este script precisa ser executado como ADMINISTRADOR!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Soluções:" -ForegroundColor Yellow
    Write-Host "1. Clique com botão direito no PowerShell e 'Executar como administrador'" -ForegroundColor White
    Write-Host "2. Ou execute: Start-Process PowerShell -Verb RunAs" -ForegroundColor White
    Write-Host ""
    pause
    exit 1
}

Write-Host "✅ Executando como administrador!" -ForegroundColor Green
Write-Host ""

# Verificar versão do Windows
$osInfo = Get-CimInstance -ClassName Win32_OperatingSystem
Write-Host "💻 Sistema: $($osInfo.Caption) - Build $($osInfo.BuildNumber)" -ForegroundColor Cyan

# Verificar se WSL2 está disponível
if ($osInfo.BuildNumber -lt 18362) {
    Write-Host "❌ Windows muito antigo para WSL2. Build mínimo: 18362" -ForegroundColor Red
    Write-Host "Considere atualizar o Windows ou usar Docker Toolbox." -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host "✅ Windows compatível com WSL2!" -ForegroundColor Green
Write-Host ""

# Função para habilitar recursos
function Enable-WindowsFeature {
    param($FeatureName, $DisplayName)
    
    Write-Host "🔧 Habilitando $DisplayName..." -ForegroundColor Yellow
    
    try {
        $result = Enable-WindowsOptionalFeature -Online -FeatureName $FeatureName -All -NoRestart
        if ($result.RestartNeeded) {
            Write-Host "✅ $DisplayName habilitado (requer reinicialização)" -ForegroundColor Green
            return $true
        } else {
            Write-Host "✅ $DisplayName já estava habilitado" -ForegroundColor Green
            return $false
        }
    } catch {
        Write-Host "⚠️ Erro ao habilitar $DisplayName : $($_.Exception.Message)" -ForegroundColor Yellow
        return $false
    }
}

# Habilitar recursos necessários
$needsRestart = $false

$needsRestart = (Enable-WindowsFeature -FeatureName "VirtualMachinePlatform" -DisplayName "Virtual Machine Platform") -or $needsRestart
$needsRestart = (Enable-WindowsFeature -FeatureName "Microsoft-Windows-Subsystem-Linux" -DisplayName "Windows Subsystem for Linux") -or $needsRestart

# Tentar habilitar Hyper-V (só funciona em Windows Pro/Enterprise)
try {
    $needsRestart = (Enable-WindowsFeature -FeatureName "Microsoft-Hyper-V-All" -DisplayName "Hyper-V") -or $needsRestart
} catch {
    Write-Host "ℹ️ Hyper-V não disponível (Windows Home Edition)" -ForegroundColor Cyan
}

Write-Host ""

# Verificar se WSL2 está instalado
Write-Host "🔧 Verificando WSL2..." -ForegroundColor Yellow
try {
    $wslVersion = wsl --version 2>$null
    if ($wslVersion) {
        Write-Host "✅ WSL2 está instalado!" -ForegroundColor Green
    } else {
        Write-Host "⚠️ WSL2 não encontrado, instalando..." -ForegroundColor Yellow
        wsl --install --no-distribution
        $needsRestart = $true
    }
} catch {
    Write-Host "⚠️ WSL2 precisa ser instalado manualmente" -ForegroundColor Yellow
    Write-Host "Execute: wsl --install" -ForegroundColor White
}

Write-Host ""

# Definir WSL2 como padrão
Write-Host "🔧 Configurando WSL2 como padrão..." -ForegroundColor Yellow
try {
    wsl --set-default-version 2
    Write-Host "✅ WSL2 definido como padrão!" -ForegroundColor Green
} catch {
    Write-Host "⚠️ Configure manualmente: wsl --set-default-version 2" -ForegroundColor Yellow
}

Write-Host ""

# Verificar Docker Desktop
Write-Host "🐳 Verificando Docker Desktop..." -ForegroundColor Yellow
$dockerPath = "${env:ProgramFiles}\Docker\Docker\Docker Desktop.exe"
if (Test-Path $dockerPath) {
    Write-Host "✅ Docker Desktop encontrado!" -ForegroundColor Green
} else {
    Write-Host "❌ Docker Desktop não encontrado!" -ForegroundColor Red
    Write-Host "Baixe em: https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe" -ForegroundColor White
}

Write-Host ""
Write-Host "📋 Resumo das correções:" -ForegroundColor Cyan
Write-Host "✅ Virtual Machine Platform: Habilitado" -ForegroundColor Green
Write-Host "✅ Windows Subsystem for Linux: Habilitado" -ForegroundColor Green
Write-Host "✅ WSL2: Configurado como padrão" -ForegroundColor Green

if ($needsRestart) {
    Write-Host ""
    Write-Host "⚠️ REINICIALIZAÇÃO NECESSÁRIA!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Após reiniciar:" -ForegroundColor Yellow
    Write-Host "1. Abra o Docker Desktop" -ForegroundColor White
    Write-Host "2. Execute: .\start-quick.bat" -ForegroundColor White
    Write-Host "3. Teste: docker --version" -ForegroundColor White
    Write-Host ""
    
    if ($Force -or (Read-Host "Reiniciar agora? (S/N)") -eq "S") {
        Write-Host "🔄 Reiniciando em 10 segundos..." -ForegroundColor Yellow
        Start-Sleep 3
        Restart-Computer -Force
    }
} else {
    Write-Host ""
    Write-Host "🎉 Tudo configurado! Tente iniciar o Docker Desktop agora." -ForegroundColor Green
    Write-Host ""
    Write-Host "Se ainda houver problemas:" -ForegroundColor Yellow
    Write-Host "1. Reinicie o computador manualmente" -ForegroundColor White
    Write-Host "2. Reinstale o Docker Desktop" -ForegroundColor White
    Write-Host "3. Verifique se a virtualização está habilitada na BIOS" -ForegroundColor White
}