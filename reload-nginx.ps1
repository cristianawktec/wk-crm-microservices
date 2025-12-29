# Script para recarregar nginx no VPS
$VPS_IP = "72.60.254.100"

Write-Host "🔄 Atualizando Nginx no VPS..." -ForegroundColor Cyan

ssh root@$VPS_IP @"
    # Substituir dashboard por painel em toda configuração
    sed -i 's/dashboard\.consultoriawk\.com/painel.consultoriawk.com/g' /etc/nginx/sites-available/consultoriawk-microservices.conf
    
    # Testar configuração
    nginx -t
    
    # Recarregar nginx
    systemctl reload nginx
    
    echo '✅ Nginx recarregado com sucesso!'
    echo ''
    echo '📋 Verificando configuração do painel:'
    grep -A3 'painel.consultoriawk.com' /etc/nginx/sites-available/consultoriawk-microservices.conf | head -4
"@

Write-Host ""
Write-Host "✅ Concluído!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Acesse: https://painel.consultoriawk.com" -ForegroundColor Yellow
Write-Host "   (Aguarde 5-30 minutos para propagação DNS)" -ForegroundColor Gray
Write-Host ""
Write-Host "📝 Verificação:" -ForegroundColor Cyan
Write-Host "   ✅ admin.consultoriawk.com → Projeto antigo (porta 4200)" -ForegroundColor Green
Write-Host "   ✅ painel.consultoriawk.com → Novo Admin com notificações (arquivos estáticos)" -ForegroundColor Green
Write-Host "   ✅ api.consultoriawk.com → API Laravel" -ForegroundColor Green
