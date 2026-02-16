# Start WK CRM local stack at Windows logon
$ErrorActionPreference = 'Stop'

$root = 'C:\xampp\htdocs\wk-crm-microservices'
$adminScript = Join-Path $root 'scripts\start-admin-frontend.ps1'
$customerScript = Join-Path $root 'scripts\start-customer-app.ps1'
$logDir = Join-Path $root 'logs'
$logFile = Join-Path $logDir 'startup.log'

if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir | Out-Null
}

function Wait-DockerReady {
    $maxAttempts = 30
    for ($i = 1; $i -le $maxAttempts; $i++) {
        try {
            docker info | Out-Null
            return $true
        } catch {
            Start-Sleep -Seconds 2
        }
    }
    return $false
}

if (-not (Wait-DockerReady)) {
    'Docker not ready after 60 seconds.' | Out-File -FilePath $logFile -Append
    exit 1
}

Set-Location $root

"Starting docker compose at $(Get-Date -Format o)" | Out-File -FilePath $logFile -Append
$prevPreference = $ErrorActionPreference
$ErrorActionPreference = 'SilentlyContinue'
docker compose up -d *>> $logFile
$ErrorActionPreference = $prevPreference

if (Test-Path $adminScript) {
    Start-Process -FilePath 'powershell.exe' -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$adminScript`"" -WindowStyle Hidden
}

if (Test-Path $customerScript) {
    Start-Process -FilePath 'powershell.exe' -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$customerScript`"" -WindowStyle Hidden
}
