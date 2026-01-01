# Deploy script usando SSH key
$vpsHost = "72.60.254.100"
$vpsUser = "root"
$sshKey = $env:SSH_KEY_PATH  # Usar variável de ambiente se existir

# Preparar comando SSH com key se disponível
$sshCmd = if ($sshKey) {
    "ssh -i $sshKey ${vpsUser}@${vpsHost}"
} else {
    "ssh ${vpsUser}@${vpsHost}"
}

Write-Host "🚀 Iniciando deploy no VPS..."
Write-Host "Host: $vpsHost"

# Função para executar comando SSH
function Run-SSH($cmd) {
    if ($sshKey) {
        Invoke-Expression "ssh -i $sshKey ${vpsUser}@${vpsHost} `"$cmd`""
    } else {
        Invoke-Expression "ssh ${vpsUser}@${vpsHost} `"$cmd`""
    }
}

# 1. Pull repository no VPS
Write-Host "📦 Fazendo git pull no VPS..."
Run-SSH "cd /var/www/html/wk-crm-laravel && git pull origin main"

# 2. Deploy Vue app via SCP
Write-Host "📋 Copiando arquivos Vue construídos..."
$distPath = "C:\xampp\htdocs\crm\wk-customer-app\dist"
Get-ChildItem -Path $distPath -Recurse | ForEach-Object {
    $relativePath = $_.FullName.Substring($distPath.Length + 1)
    $vpsPath = "/var/www/html/app/$relativePath"
    
    if ($_.PSIsContainer) {
        Write-Host "  📁 Criando diretório: $vpsPath"
    } else {
        Write-Host "  📄 Copiando: $relativePath"
        scp -r "$($_.FullName)" "${vpsUser}@${vpsHost}:$vpsPath"
    }
}

# 3. Limpar cache Laravel
Write-Host "🧹 Limpando cache do Laravel..."
Run-SSH "cd /var/www/html/wk-crm-laravel && docker compose exec -T wk-crm-laravel php artisan config:clear && docker compose exec -T wk-crm-laravel php artisan cache:clear"

Write-Host "✅ Deploy concluído!"
