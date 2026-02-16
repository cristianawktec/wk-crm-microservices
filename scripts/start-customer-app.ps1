# Start Vue customer app dev server
$ErrorActionPreference = 'Stop'

$root = 'C:\xampp\htdocs\wk-crm-microservices\wk-customer-app'
$logDir = 'C:\xampp\htdocs\wk-crm-microservices\logs'
$logFile = Join-Path $logDir 'customer-app-startup.log'

if (-not (Test-Path $logDir)) {
	New-Item -ItemType Directory -Path $logDir | Out-Null
}

Set-Location $root

$npmCmd = (Get-Command npm.cmd -ErrorAction SilentlyContinue).Source
if (-not $npmCmd) {
	$npmCmd = 'npm'
}

if (-not (Test-Path (Join-Path $root 'node_modules'))) {
	& $npmCmd install *>> $logFile
}

& $npmCmd run dev *>> $logFile
