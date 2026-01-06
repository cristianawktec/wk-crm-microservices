# 🔥 SOLUÇÃO DEFINITIVA - ERRO "postgres"

**Problema:** O erro continua aparecendo mesmo após corrigir `.env`

**Causa Raiz:** O Laravel está usando um `.env` cacheado ou há múltiplas instâncias rodando

---

## ✅ SOLUÇÃO COMPLETA

Execute estes comandos **NA VPS via SSH**:

```bash
# 1. PARAR TODOS OS SERVIÇOS
systemctl stop php8.3-fpm nginx

# 2. DELETAR TODOS OS CACHES DO LARAVEL
cd /var/www/html/wk-crm-laravel
rm -rf bootstrap/cache/config.php
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
rm -rf storage/framework/sessions/*

# 3. VERIFICAR .ENV ESTÁ CORRETO
cat .env | grep ^DB_
# Deve mostrar:
# DB_HOST=127.0.0.1
# DB_PORT=5433

# Se não estiver, corrija:
sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=.*/DB_PORT=5433/' .env

# 4. DELETAR OPCACHE (cache do PHP)
echo 'opcache.enable=0' > /etc/php/8.3/fpm/conf.d/99-disable-opcache.ini

# 5. RECRIAR CACHE DO LARAVEL
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# 6. REINICIAR SERVIÇOS
systemctl start php8.3-fpm
systemctl start nginx

# 7. TESTAR
php artisan tinker --execute="echo 'DB_HOST: ' . env('DB_HOST') . \"\n\";"
```

---

## 🔍 SE AINDA NÃO FUNCIONAR

### Opção A: Verificar se há outro Laravel rodando

```bash
# Procurar por outros .env na VPS
find / -name ".env" -path "*/wk-crm-laravel/*" 2>/dev/null

# Ver qual diretório o Nginx está servindo
cat /etc/nginx/sites-available/api.consultoriawk.com
```

### Opção B: Deploy limpo do zero

```bash
# Backup do .env atual
cp /var/www/html/wk-crm-laravel/.env ~/backup.env

# Clonar código atualizado
cd /var/www/html
rm -rf wk-crm-laravel
git clone SEU_REPOSITORIO wk-crm-laravel
cd wk-crm-laravel

# Restaurar .env correto
cat > .env << 'EOF'
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:L4LL6OVmj/o2lXI4NvDZid/MBc9FbV6kw8djOh+DRto=
APP_DEBUG=false
APP_URL=https://api.consultoriawk.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=wk_main
DB_USERNAME=wk_user
DB_PASSWORD=secure_password_123
EOF

# Instalar dependências
composer install --no-dev --optimize-autoloader

# Permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar
systemctl restart php8.3-fpm nginx
```

---

## 🌐 LIMPAR CACHE DO NAVEGADOR

1. Pressione `Ctrl + Shift + Delete`
2. Selecione "Imagens e arquivos em cache"
3. Selecione "Todo o período"
4. Clique em "Limpar dados"

Ou use **modo anônimo** para testar: `Ctrl + Shift + N`

---

## 📊 VERIFICAR SE FUNCIONOU

```bash
# Na VPS
cd /var/www/html/wk-crm-laravel

# Testar conexão
php artisan tinker --execute="
try {
    DB::connection()->getPdo();
    echo 'CONEXÃO OK!\n';
    echo 'Usuários: ' . App\Models\User::count() . '\n';
} catch (Exception \$e) {
    echo 'ERRO: ' . \$e->getMessage() . '\n';
}
"
```

**Se mostrar "CONEXÃO OK!" e "Usuários: 2"**, teste o login:

- URL: https://app.consultoriawk.com/login
- Email: `admin@consultoriawk.com`
- Senha: `Admin@123456`

---

## 🆘 ÚLTIMA ALTERNATIVA

Se NADA funcionar, o problema pode estar no **Vue compilado**. Nesse caso:

```bash
# Encontrar onde está o Vue
find /var/www -name "index.html" -path "*/wk-customer*" 2>/dev/null

# Fazer rebuild do Vue
cd /caminho/correto/wk-customer-app
npm run build

# Copiar dist para produção
cp -r dist/* /var/www/app.consultoriawk.com/
```

---

**Última atualização:** 03/01/2026 18:20  
**Status:** Aguardando teste com cache PHP desabilitado
