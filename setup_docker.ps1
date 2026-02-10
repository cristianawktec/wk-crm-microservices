#!/usr/bin/env powershell
# Script para reconstruir e iniciar containers

$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
$logFile = "C:\xampp\htdocs\wk-crm-microservices\docker_setup.log"

function Log-Message {
    param([string]$message)
    "$timestamp - $message" | Tee-Object -FilePath $logFile -Append | Write-Host
}

Log-Message "========== Iniciando setup dos containers =========="

# Ir para o diretório
Set-Location C:\xampp\htdocs\wk-crm-microservices
Log-Message "Diretório: $(Get-Location)"

# Remover containers antigos se existirem
Log-Message "Removendo containers antigos..."
docker-compose down --remove-orphans 2>&1 | Tee-Object -FilePath $logFile -Append | Select-Object -Last 5

Start-Sleep -Seconds 3

# Iniciar containers
Log-Message "Iniciando containers..."
docker-compose up -d 2>&1 | Tee-Object -FilePath $logFile -Append

# Aguardar inicialização
Start-Sleep -Seconds 10

# Verificar status
Log-Message "Status dos containers:"
docker ps | Tee-Object -FilePath $logFile -Append

# Listar imagens
Log-Message "Imagens Docker disponíveis:"
docker images | Select-Object -First 5 | Tee-Object -FilePath $logFile -Append

Log-Message "========== Setup completo =========="
Log-Message "Log salvo em: $logFile"
