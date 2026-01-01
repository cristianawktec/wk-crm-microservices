# 🚀 Deploy Completo VPS - PowerShell
# Execute este script no seu PC (Windows)

$VPS_IP = "72.60.254.100"
$VPS_USER = "root"
$DIST_PATH = "C:\xampp\htdocs\crm\wk-customer-app\dist"
$DEPLOY_SCRIPT = "C:\xampp\htdocs\crm\deploy-vps-fix.sh"

Write-Host "🚀 Deploy WK CRM para VPS" -ForegroundColor Cyan
Write-Host "=" * 50

# Passo 1: Copiar script de deploy para VPS
Write-Host "`n📤 [1/3] Copiando script de deploy para VPS..." -ForegroundColor Yellow
scp $DEPLOY_SCRIPT "${VPS_USER}@${VPS_IP}:/tmp/deploy-fix.sh"

# Passo 2: Executar script no VPS
Write-Host "`n⚙️  [2/3] Executando deploy no VPS..." -ForegroundColor Yellow
ssh ${VPS_USER}@${VPS_IP} "chmod +x /tmp/deploy-fix.sh && /tmp/deploy-fix.sh"

# Passo 3: Copiar arquivos Vue
Write-Host "`n📦 [3/3] Copiando arquivos Vue para VPS..." -ForegroundColor Yellow
scp -r "$DIST_PATH\*" "${VPS_USER}@${VPS_IP}:/var/www/html/app/"

Write-Host "`n✅ Deploy completo!" -ForegroundColor Green
Write-Host "`n🌐 Teste em: https://app.consultoriawk.com" -ForegroundColor Cyan
Write-Host "   - Faça login" -ForegroundColor White
Write-Host "   - Clique em Sair" -ForegroundColor White
Write-Host "   - Faça refresh (F5)" -ForegroundColor White
Write-Host "   - Deve permanecer em /login (não voltar para dashboard)" -ForegroundColor White
Write-Host "`n⚡ Para testar Insights de IA:" -ForegroundColor Cyan
Write-Host "   - Abra uma oportunidade" -ForegroundColor White
Write-Host "   - Clique no botão de Insights" -ForegroundColor White
Write-Host "   - Deve retornar resultado ou fallback (não 404)" -ForegroundColor White
