# Deploy Vue App to VPS
$sshHost = "root@72.60.254.100"
$localDist = "C:\xampp\htdocs\crm\wk-customer-app\dist"
$remoteApp = "/var/www/consultoriawk-crm/app"

Write-Host "🚀 Iniciando deploy da app Vue..." -ForegroundColor Green

# Remove old files and deploy new ones via SSH
$sshCmd = @"
# Clear directory
rm -rf $remoteApp/*

# Copy new files
echo 'Aguardando arquivos...'
"@

# Create tar file locally for easier transfer
cd $localDist
Write-Host "📦 Criando arquivo comprimido..." -ForegroundColor Cyan

# Copy each file individually if needed
Write-Host "📤 Enviando arquivos..." -ForegroundColor Cyan

# Use a simple approach - run SCP with explicit password prompt
$files = @(
    "index.html",
    "assets"
)

foreach ($item in $files) {
    $itemPath = Join-Path $localDist $item
    if (Test-Path $itemPath) {
        Write-Host "  → Enviando $item" -ForegroundColor Yellow
        # scp will prompt for password
        # scp -r "$itemPath" "${sshHost}:${remoteApp}/"
    }
}

Write-Host "✅ Deploy preparado. Use o comando abaixo:" -ForegroundColor Green
Write-Host "scp -r '$localDist\*' '${sshHost}:${remoteApp}/'" -ForegroundColor White
