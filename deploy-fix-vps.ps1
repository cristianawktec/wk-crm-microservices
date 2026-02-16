# Script para fazer deploy da correção do modal de IA na VPS
Write-Host "🚀 Deploy da correção do modal de IA para VPS" -ForegroundColor Cyan
Write-Host ""

$vpsHost = "root@72.60.254.100"

Write-Host "1️⃣  Enviando pacote de assets..." -ForegroundColor Yellow
scp customer-app-fix.zip ${vpsHost}:~/
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erro ao enviar arquivo" -ForegroundColor Red
    exit 1
}

Write-Host "2️⃣  Enviando script de aplicação..." -ForegroundColor Yellow  
scp apply-fix-vps.sh ${vpsHost}:~/
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erro ao enviar script" -ForegroundColor Red
    exit 1
}

Write-Host "3️⃣  Executando deploy na VPS..." -ForegroundColor Yellow
ssh ${vpsHost} "chmod +x ~/apply-fix-vps.sh && ~/apply-fix-vps.sh"
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erro ao executar deploy" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "✅ Deploy concluído com sucesso!" -ForegroundColor Green
Write-Host "🌐 Acesse: https://app.consultoriawk.com" -ForegroundColor Cyan
Write-Host "💡 Dica: Use Ctrl+Shift+R para forçar refresh no navegador" -ForegroundColor Gray
