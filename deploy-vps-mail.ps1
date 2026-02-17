#!/usr/bin/env pwsh

# Deploy script for VPS - Updates Laravel configuration and restarts services

$vpsServer = "72.60.254.100"
$vpsPath = "/var/www/wk-crm-laravel"
$sshUser = "root"

Write-Host "Deploying to VPS..." -ForegroundColor Green
Write-Host "   Server: $vpsServer"
Write-Host "   Path: $vpsPath"
Write-Host ""

# Commands to execute - using semicolons instead of &&
$cmd = "cd $vpsPath; echo 'Pulling latest changes...'; git pull origin main; cp .env.vps .env; php artisan config:cache; systemctl restart laravel-crm; echo 'Deployment complete!'"

Write-Host "Executing commands on $sshUser@$vpsServer..." -ForegroundColor Cyan
Write-Host ""

# Execute via SSH
ssh "${sshUser}@${vpsServer}" $cmd

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "VPS deployment successful!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "SSH command returned exit code: $LASTEXITCODE" -ForegroundColor Yellow
}
