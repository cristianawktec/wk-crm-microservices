param(
    [string]$Server = "72.60.254.100",
    [string]$User = "root",
    [string]$VueDistPath = "C:/xampp/htdocs/crm/wk-customer-app/dist",
    [string]$AdminDistPath = "C:/xampp/htdocs/crm/wk-admin-frontend/dist",
    [string]$KeyPath = ""
)

function Ensure-Tool($name) {
    if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
        throw "Tool '$name' not found. Please install OpenSSH Client."
    }
}

Ensure-Tool ssh
Ensure-Tool scp

if ($KeyPath) {
    if (-not (Test-Path -Path $KeyPath)) {
        throw "Key file not found at '$KeyPath'"
    }
    Write-Host "Using SSH key: $KeyPath" -ForegroundColor Yellow
}

function Deploy-App {
    param(
        [string]$AppName,
        [string]$LocalPath,
        [string]$RemoteTemp,
        [string]$RemoteTarget
    )

    if (-not (Test-Path -Path $LocalPath)) {
        throw "Missing $AppName dist at '$LocalPath'"
    }

    $Leaf = Split-Path -Path $LocalPath -Leaf

    Write-Host "Deploying $AppName..." -ForegroundColor Cyan

    $prepCmd = "rm -rf $RemoteTemp; mkdir -p $RemoteTemp $RemoteTarget"
    if ($KeyPath) {
        ssh -i "$KeyPath" -o StrictHostKeyChecking=no "$User@$Server" "$prepCmd"
    } else {
        ssh -o StrictHostKeyChecking=no "$User@$Server" "$prepCmd"
    }

    $Dest = "$User@$Server:$RemoteTemp/"
    if ($KeyPath) {
        scp -i "$KeyPath" -r "$LocalPath" $Dest
    } else {
        scp -r "$LocalPath" $Dest
    }

    $copyCmd = "rm -rf $RemoteTarget/*; cp -r $RemoteTemp/$Leaf/* $RemoteTarget/; chown -R www-data:www-data $RemoteTarget; nginx -t && systemctl reload nginx; rm -rf $RemoteTemp"
    if ($KeyPath) {
        ssh -i "$KeyPath" -o StrictHostKeyChecking=no "$User@$Server" "$copyCmd"
    } else {
        ssh -o StrictHostKeyChecking=no "$User@$Server" "$copyCmd"
    }

    Write-Host "$AppName deployed to $RemoteTarget" -ForegroundColor Green
}

Deploy-App -AppName "Vue App" -LocalPath $VueDistPath -RemoteTemp "/root/app_dist" -RemoteTarget "/var/www/consultoriawk-crm/app"
Deploy-App -AppName "Angular Admin" -LocalPath $AdminDistPath -RemoteTemp "/root/admin_dist" -RemoteTarget "/var/www/consultoriawk-crm/admin"
