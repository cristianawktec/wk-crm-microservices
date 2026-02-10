# Start Angular admin frontend dev server
$ErrorActionPreference = 'Stop'

$root = 'C:\xampp\htdocs\wk-crm-microservices\wk-admin-frontend'
$logDir = 'C:\xampp\htdocs\wk-crm-microservices\logs'
$logFile = Join-Path $logDir 'admin-frontend-startup.log'

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

$ngCmd = Join-Path $root 'node_modules\.bin\ng.cmd'
$ngJs = Join-Path $root 'node_modules\@angular\cli\bin\ng.js'
if (Test-Path $ngCmd) {
	& $ngCmd serve --host 0.0.0.0 --proxy-config proxy.conf.json *>> $logFile
} elseif (Test-Path $ngJs) {
	"ng.cmd not found. Using node to run ng.js." | Out-File -FilePath $logFile -Append
	& node $ngJs serve --host 0.0.0.0 --proxy-config proxy.conf.json *>> $logFile
} else {
	"Angular CLI not found. Running npm start as fallback." | Out-File -FilePath $logFile -Append
	& $npmCmd start *>> $logFile
}
