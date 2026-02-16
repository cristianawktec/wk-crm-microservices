# Fix VPS GEMINI Model - Usar mesmo modelo do localhost

Write-Host "`n🔧 Corrigindo modelo GEMINI no VPS...`n" -ForegroundColor Cyan

# Upload fix script
Write-Host "📤 Enviando script de correção..." -ForegroundColor Yellow
scp fix-vps-gemini-model.sh root@72.60.254.100:/var/www/wk-crm-api/

# Execute on VPS
Write-Host "`n🚀 Executando correção no VPS...`n" -ForegroundColor Green
ssh root@72.60.254.100 "cd /var/www/wk-crm-api && chmod +x fix-vps-gemini-model.sh && ./fix-vps-gemini-model.sh"

Write-Host "`n✅ Correção concluída!`n" -ForegroundColor Green
Write-Host "Teste agora em: https://app.consultoriawk.com" -ForegroundColor Cyan
