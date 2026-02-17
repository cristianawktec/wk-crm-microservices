# Deploy Mail Configuration to VPS
# Updates .env.vps with MAIL_AUDIT_RECIPIENT and restarts services

$vpsServer = "72.60.254.100"
$vpsPath = "/var/www/wk-crm-laravel"
$sshUser = "root"
$envVpsLocal = ".\wk-crm-laravel\.env.vps"
$envVpsRemote = "$vpsPath/.env.vps"

Write-Host "🚀 Starting deployment of mail configuration..." -ForegroundColor Green
Write-Host "   Server: $vpsServer"
Write-Host "   File: $envVpsRemote"
Write-Host ""

# Check if local file exists
if (-Not (Test-Path $envVpsLocal)) {
    Write-Host "❌ Local file not found: $envVpsLocal" -ForegroundColor Red
    exit 1
}

# Read the file content
$envContent = Get-Content $envVpsLocal -Raw

# Use SCP to upload the file
Write-Host "📤 Uploading .env.vps..."
Write-Host "   Using: scp $envVpsLocal ${sshUser}@${vpsServer}:${envVpsRemote}" -ForegroundColor Cyan

# Try using PuTTY plink if available, otherwise try native ssh
try {
    # First, try with native OpenSSH
    $keyPath = "$env:USERPROFILE\.ssh\id_rsa"
    if (Test-Path $keyPath) {
        & scp -i $keyPath -o StrictHostKeyChecking=no $envVpsLocal "${sshUser}@${vpsServer}:${envVpsRemote}" 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ File uploaded successfully" -ForegroundColor Green
        } else {
            Write-Host "❌ SCP upload failed" -ForegroundColor Red
            exit 1
        }
    } else {
        Write-Host "⚠️  SSH key not found at $keyPath" -ForegroundColor Yellow
        Write-Host "   Please configure SSH keys or upload file manually" -ForegroundColor Yellow
        exit 1
    }
} catch {
    Write-Host "❌ Error during upload: $_" -ForegroundColor Red
    exit 1
}

# Execute remote commands
Write-Host ""
Write-Host "⚙️  Executing remote commands..."

$remoteCmd = @"
cd $vpsPath && \
cp .env.vps .env && \
echo "✅ Copied .env.vps to .env" && \
php artisan config:cache && \
echo "✅ Config cache cleared" && \
systemctl restart laravel-crm 2>/dev/null || echo "⚠️  Service restart skipped (may require manual restart)" && \
echo "✅ Deployment complete!"
"@

ssh -i "$env:USERPROFILE\.ssh\id_rsa" -o StrictHostKeyChecking=no "${sshUser}@${vpsServer}" $remoteCmd

if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Remote commands completed with warnings" -ForegroundColor Yellow
} else {
    Write-Host ""
    Write-Host "✅ Mail configuration deployed successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📧 Configuration details:"
    Write-Host "   From: noreply@consultoriawk.com"
    Write-Host "   To: admin@consultoriawk.com"
    Write-Host "   Server: smtp.titan.email:465"
}
