# Test both email flows on VPS
# Usage: .\tests-vps-emails.ps1

$VPS = "72.60.254.100"
$SSH_USER = "root"
$CONTAINER = "wk_crm_laravel"

Write-Host "================================================" -ForegroundColor Green
Write-Host "WK CRM - VPS EMAIL VERIFICATION TESTS" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""

# Test 1: Update repository and check if revert was deployed
Write-Host "`n[TEST 1] Pulling latest code from GitHub..." -ForegroundColor Cyan
$pullResult = ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no "${SSH_USER}@${VPS}" `
  "cd /opt/wk-crm/wk-crm-laravel && git pull 2>&1"
Write-Host "   Pull result: $pullResult" -ForegroundColor Gray

Write-Host "`n[TEST 1b] Checking if revert was deployed..." -ForegroundColor Cyan
$result = ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no "${SSH_USER}@${VPS}" `
  "cd /opt/wk-crm/wk-crm-laravel && git log --oneline -1 2>&1"

if ($result -like "*Revert*") {
    Write-Host "✅ Revert deployed successfully" -ForegroundColor Green
    Write-Host "   $result" -ForegroundColor Gray
} else {
    Write-Host "⚠️  Revert may not be deployed" -ForegroundColor Yellow
    Write-Host "   $result" -ForegroundColor Gray
}

# Test 2: Check mail config on VPS
Write-Host "`n[TEST 2] Checking mail configuration..." -ForegroundColor Cyan
$mailConfig = ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no "${SSH_USER}@${VPS}" `
  "docker exec ${CONTAINER} php artisan config:show mail.audit_recipient 2>&1"

Write-Host "   MAIL_AUDIT_RECIPIENT on VPS:" -ForegroundColor Gray
Write-Host "   $mailConfig" -ForegroundColor Cyan | Select-Object -First 3

# Test 3: Run login audit test on VPS
Write-Host "`n[TEST 3] Testing login audit email on VPS..." -ForegroundColor Cyan
$loginTest = ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no "${SSH_USER}@${VPS}" `
  "docker exec ${CONTAINER} php artisan email:test-login-audit 2>&1" 2>&1

if ($loginTest -like "*sent successfully*") {
    Write-Host "✅ Login email sent successfully on VPS" -ForegroundColor Green
} else {
    Write-Host "⚠️  Login email test result:" -ForegroundColor Yellow
    Write-Host $loginTest -ForegroundColor Gray
}

# Test 4: Cache clear on VPS (to ensure fresh config)
Write-Host "`n[TEST 4] Clearing config cache on VPS..." -ForegroundColor Cyan
$cacheResult = ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no "${SSH_USER}@${VPS}" `
  "docker exec ${CONTAINER} php artisan config:cache 2>&1"

if ($cacheResult -like "*cached*") {
    Write-Host "✅ Config cache cleared and refreshed" -ForegroundColor Green
} else {
    Write-Host "⚠️  Cache result: $cacheResult" -ForegroundColor Yellow
}

Write-Host "`n================================================" -ForegroundColor Green
Write-Host "VERIFICATION TESTS COMPLETE" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
