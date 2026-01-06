# Script para corrigir completamente o banco e senha
# Execute: .\fix-completo.ps1

$VPS = "root@72.60.254.100"

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  CORREÇÃO COMPLETA" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Criar script na VPS
$script = @'
#!/bin/bash
cd /var/www/html/wk-crm-laravel

echo "1. Corrigindo .env..."
sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=.*/DB_PORT=5433/' .env

echo "2. Limpando caches..."
php artisan cache:clear > /dev/null 2>&1
php artisan config:clear > /dev/null 2>&1
php artisan route:clear > /dev/null 2>&1
php artisan view:clear > /dev/null 2>&1
php artisan config:cache > /dev/null 2>&1

echo "3. Atualizando senha para Admin@123456..."
PGPASSWORD=secure_password_123 psql -h 127.0.0.1 -p 5433 -U wk_user -d wk_main -c "UPDATE users SET password = '\$2y\$12\$VwE3KqZr.mLBc9QJ0dX9XeFdJr1YC7vH6KfZpQGN0wZRnUQvqKgLm' WHERE email = 'admin@consultoriawk.com';"

echo "4. Reiniciando serviços..."
systemctl restart php8.3-fpm
systemctl restart nginx

echo ""
echo "✅ CONCLUÍDO!"
echo "URL: https://app.consultoriawk.com/login"
echo "Email: admin@consultoriawk.com"
echo "Senha: Admin@123456"
'@

Write-Host "Enviando script para VPS..." -ForegroundColor Yellow
$script | ssh $VPS 'cat > /root/fix.sh && chmod +x /root/fix.sh && bash /root/fix.sh'

Write-Host ""
Write-Host "=====================================" -ForegroundColor Green
Write-Host "  ✅ PRONTO!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""
Write-Host "Teste agora:" -ForegroundColor Cyan
Write-Host "URL: https://app.consultoriawk.com/login" -ForegroundColor White
Write-Host "Email: admin@consultoriawk.com" -ForegroundColor White
Write-Host "Senha: Admin@123456" -ForegroundColor White
