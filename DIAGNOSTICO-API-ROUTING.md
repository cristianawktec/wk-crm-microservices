# 🔧 Diagnóstico: API Routing VPS vs Localhost

## 📊 **Situação Atual:**

### ✅ **Localhost (Funcionando):**
- **Docker:** `http://localhost:8000/api/health` ✅
- **XAMPP:** `http://localhost:8001/api/health` ✅
- **Status:** 200 OK com JSON válido

### ❌ **VPS (Problema):**
- **URL:** `https://api.consultoriawk.com/api/health` ❌
- **Status:** 404 Not Found
- **SSL:** ✅ Funcionando (certificados válidos)

---

## 🔍 **Plano de Diagnóstico:**

### **1. Verificar Estrutura de Rotas**
```bash
# Localhost (referência)
php artisan route:list | grep api

# VPS (problemática)
ssh root@72.60.254.100 "cd /opt/wk-crm/wk-crm-laravel && php artisan route:list | grep api"
```

### **2. Comparar Configuração Nginx**
```bash
# Verificar configuração atual na VPS
ssh root@72.60.254.100 "cat /etc/nginx/sites-available/api.consultoriawk.com"

# Comparar com configuração local funcionando
cat laravel_nginx.conf
```

### **3. Verificar Document Root**
```bash
# Na VPS
ssh root@72.60.254.100 "ls -la /opt/wk-crm/wk-crm-laravel/public/"

# Verificar se index.php existe e está correto
```

### **4. Testar Rotas Básicas**
```bash
# Teste direto no servidor (sem Nginx)
ssh root@72.60.254.100 "cd /opt/wk-crm/wk-crm-laravel && php artisan serve --host=127.0.0.1 --port=8080 &"

# Teste via curl interno
ssh root@72.60.254.100 "curl http://127.0.0.1:8080/api/health"
```

---

## 🎯 **Hipóteses do Problema:**

### **Hipótese 1: Document Root Incorreto**
- Nginx pode estar apontando para diretório errado
- Deveria apontar para `/opt/wk-crm/wk-crm-laravel/public`

### **Hipótese 2: URL Rewrite Problem**
- `.htaccess` não está funcionando
- Nginx não está fazendo rewrite correto para `index.php`

### **Hipótese 3: Laravel Routes Cache**
- Cache de rotas pode estar corrompido
- Precisa limpar e recriar

### **Hipótese 4: PHP-FPM Configuration**
- Nginx não está passando requests PHP corretamente
- FastCGI pode estar mal configurado

---

## 🔧 **Correções Planejadas:**

### **Correção 1: Atualizar Nginx Config**
```nginx
server {
    listen 443 ssl http2;
    server_name api.consultoriawk.com;
    
    root /opt/wk-crm/wk-crm-laravel/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### **Correção 2: Laravel Cache Clear**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan route:cache
```

### **Correção 3: Permissions Fix**
```bash
chown -R www-data:www-data /opt/wk-crm/wk-crm-laravel
chmod -R 755 /opt/wk-crm/wk-crm-laravel
chmod -R 775 /opt/wk-crm/wk-crm-laravel/storage
```

---

## 📋 **Checklist de Execução:**

- [ ] **1. Diagnóstico inicial** - Comparar rotas localhost vs VPS
- [ ] **2. Verificar Nginx config** - Document root e rewrites
- [ ] **3. Testar PHP direto** - Bypassing Nginx
- [ ] **4. Corrigir configuração** - Aplicar fixes necessários
- [ ] **5. Teste de validação** - Confirmar API funcionando
- [ ] **6. Deploy via CI/CD** - Push e merge da correção

---

## 🚀 **Próximo Passo:**

Executar diagnóstico inicial comparando localhost vs VPS para identificar a causa raiz do problema 404.

**Comando inicial:**
```bash
ssh root@72.60.254.100 "cd /opt/wk-crm/wk-crm-laravel && php artisan route:list"
```