<#
.SYNOPSIS
  Empacota `wk-crm-laravel/public/docs` e transfere para a VPS, extraindo em `public`.

.PARAMETER VpsUser
  Usuário SSH na VPS (ex: root)

.PARAMETER VpsHost
  Endereço IP ou hostname da VPS

.PARAMETER RemotePath
  Caminho remoto onde o `public` do Laravel está localizado (default: /opt/wk-crm/wk-crm-laravel/public)

.PARAMETER SshKeyPath
  Caminho para a chave privada SSH (opcional). Se omitido, `ssh`/`scp` utilizará agente/credenciais do sistema.
#>

param(
    [Parameter(Mandatory=$true)][string]$VpsUser,
    [Parameter(Mandatory=$true)][string]$VpsHost,
    [Parameter(Mandatory=$false)][string]$RemotePath = "/opt/wk-crm/wk-crm-laravel/public",
    [Parameter(Mandatory=$false)][string]$SshKeyPath
)

Set-StrictMode -Version Latest

# Descobre raiz do repo assumindo esta estrutura: <root>/wk-crm-laravel/scripts
# 1) sobe um nível (../) para chegar em wk-crm-laravel, 2) verifica public/docs
$repoRoot = (Get-Item (Join-Path (Get-Location).Path "..") ).FullName
$docsDir = Join-Path $repoRoot "public\docs"

if (-not (Test-Path $docsDir)) {
    # fallback: talvez o script esteja sendo executado a partir da raiz do repo
    $docsDirAlt = Join-Path (Get-Location).Path "../public/docs"
    if (Test-Path $docsDirAlt) {
        $docsDir = (Resolve-Path $docsDirAlt).Path
    } else {
        Write-Error "Diretório '$docsDir' não encontrado. Execute o script a partir da pasta 'scripts' ou ajuste o caminho."; exit 1
    }
}

$tmpArchive = Join-Path $env:TEMP "wkcrmdocs_$(Get-Random).tgz"

Write-Host "Compactando '$docsDir' -> $tmpArchive ..."

# Use tar se disponível (Windows 10+ tem tar), fallback para Compress-Archive (zip)
try {
    & tar -czf $tmpArchive -C (Split-Path $docsDir -Parent) (Split-Path $docsDir -Leaf)
    $archiveType = 'tgz'
} catch {
    $zipPath = Join-Path $env:TEMP "wkcrmdocs_$(Get-Random).zip"
    Compress-Archive -Path $docsDir -DestinationPath $zipPath -Force
    $tmpArchive = $zipPath
    $archiveType = 'zip'
}

Write-Host "Arquivo gerado: $tmpArchive ($archiveType)"

$scpCmd = 'scp'
$sshCmd = 'ssh'
if ($SshKeyPath) {
  $scpArgs = @('-i', $SshKeyPath, $tmpArchive, "${VpsUser}@${VpsHost}:/tmp/")
  $sshKeyOpt = @('-i', $SshKeyPath)
} else {
  $scpArgs = @($tmpArchive, "${VpsUser}@${VpsHost}:/tmp/")
  $sshKeyOpt = @()
}

Write-Host ("Enviando arquivo para {0}@{1}:/tmp/ ..." -f $VpsUser, $VpsHost)
& $scpCmd @scpArgs
if ($LASTEXITCODE -ne 0) { Write-Error "Falha no scp (código $LASTEXITCODE)"; exit 1 }

Write-Host "Extraindo e instalando na VPS em $RemotePath ..."

$remoteCommands = @()
if ($archiveType -eq 'tgz') {
    $remoteCommands += "mkdir -p $RemotePath && tar -xzf /tmp/$(Split-Path $tmpArchive -Leaf) -C $RemotePath && sudo chown -R www-data:www-data $RemotePath/docs && sudo chmod -R 755 $RemotePath/docs"
} else {
    # zip fallback
    $remoteCommands += "mkdir -p $RemotePath && unzip -o /tmp/$(Split-Path $tmpArchive -Leaf) -d $RemotePath && sudo chown -R www-data:www-data $RemotePath/docs && sudo chmod -R 755 $RemotePath/docs"
}
$remoteCommands += "sudo nginx -t && sudo systemctl reload nginx"

$remoteCmdJoined = $remoteCommands -join ' && '

Write-Host "Executando comandos remotos: $remoteCmdJoined"
& $sshCmd @sshKeyOpt ("{0}@{1}" -f $VpsUser, $VpsHost) $remoteCmdJoined
if ($LASTEXITCODE -ne 0) { Write-Error "Comandos remotos tiveram falha (código $LASTEXITCODE). Verifique logs na VPS."; exit 1 }

Write-Host "Deploy concluído com sucesso. Limpeza local do arquivo temporário: $tmpArchive"
Remove-Item $tmpArchive -ErrorAction SilentlyContinue

Write-Host "Acesse: https://api.consultoriawk.com/docs/index.html"
