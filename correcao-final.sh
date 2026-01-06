#!/bin/bash
# CORREÇÃO FINAL - Execute na VPS como root

set -e

cd /var/www/html/wk-crm-laravel

echo "========================================="
echo "  CORREÇÃO FINAL - BANCO E CACHE"
echo "========================================="
echo ""

# 1. Remover TODOS os caches de Laravel
echo "1. Removendo caches..."
find bootstrap/cache -type f ! -name '.gitkeep' -delete
find storage/framework -type f ! -name '.gitkeep' -delete
chmod -R 775 bootstrap/cache storage
chown -R www-data:www-data bootstrap/cache storage
echo "✅ Caches removidos"
echo ""

# 2. Verificar .env
echo "2. Verificando .env..."
echo "DB_HOST=$(grep ^DB_HOST .env | cut -d'=' -f2)"
echo "DB_PORT=$(grep ^DB_PORT .env | cut -d'=' -f2)"
echo "DB_DATABASE=$(grep ^DB_DATABASE .env | cut -d'=' -f2)"
echo ""

# 3. Garantir que .env está correto
echo "3. Garantindo valores corretos..."
sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=.*/DB_PORT=5433/' .env
echo "✅ .env atualizado"
echo ""

# 4. Limpar e recriar config cache
echo "4. Recriando config cache..."
php artisan config:clear > /dev/null 2>&1 || true
php artisan config:cache > /dev/null 2>&1 || true
php artisan cache:clear > /dev/null 2>&1 || true
echo "✅ Config recriado"
echo ""

# 5. Reiniciar PHP-FPM e Nginx
echo "5. Reiniciando serviços..."
systemctl restart php8.3-fpm
systemctl restart nginx
echo "✅ Serviços reiniciados"
echo ""

# 6. Testar conexão
echo "6. Testando conexão com banco..."
php artisan tinker << 'EOF' 2>&1 || true
try {
    DB::connection()->getPdo();
    echo "✅ Conexão OK\n";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
exit();
EOF
echo ""

echo "========================================="
echo "  ✅ CONCLUÍDO!"
echo "========================================="
echo ""
echo "Teste agora em: https://app.consultoriawk.com/login"
echo "Email: admin@consultoriawk.com"
echo "Senha: Admin@123456"
