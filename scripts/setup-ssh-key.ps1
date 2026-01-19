param(
    [string]$Server = "72.60.254.100",
    [string]$User = "root",
    [string]$KeyPath = "$env:USERPROFILE/.ssh/id_ed25519",
    [string]$Comment = "wk-vps"
)

function Ensure-Tool($name) {
    if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
        throw "Tool '$name' not found. Please install OpenSSH Client."
    }
}

Ensure-Tool ssh
Ensure-Tool scp
Ensure-Tool ssh-keygen

if (-not (Test-Path -Path $KeyPath)) {
    Write-Host "Generating SSH key at $KeyPath" -ForegroundColor Cyan
    ssh-keygen -t ed25519 -C $Comment -f $KeyPath -N ""
} else {
    Write-Host "SSH key already exists at $KeyPath" -ForegroundColor Yellow
}

$PubKey = "$KeyPath.pub"
if (-not (Test-Path -Path $PubKey)) {
    throw "Public key not found: $PubKey"
}

Write-Host "Uploading public key to $User@$Server (you will be prompted for password)" -ForegroundColor Cyan
scp "$PubKey" "$User@$Server:/root/"

$remoteCmd = "mkdir -p ~/.ssh && cat /root/$(Split-Path -Leaf $PubKey) >> ~/.ssh/authorized_keys && rm -f /root/$(Split-Path -Leaf $PubKey) && chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys"
ssh "$User@$Server" "$remoteCmd"

Write-Host "SSH key installed. You should now be able to connect without password." -ForegroundColor Green
