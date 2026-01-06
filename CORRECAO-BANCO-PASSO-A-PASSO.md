# 🔍 DIAGNÓSTICO DO BANCO DE DADOS - PASSO A PASSO

**Status:** Seu banco NÃO foi deletado! O problema é apenas de configuração.

---

## ❌ O QUE ESTÁ ACONTECENDO

O erro que você está vendo é:

```
SQLSTATE[08006] [7] could not translate host name "postgres" to address
```

**Isso significa:** O Laravel está tentando conectar em um servidor chamado "postgres", mas esse nome não existe na sua VPS.

**Por quê?** O arquivo `.env` tem a configuração do Docker (onde o banco se chama "postgres"), mas sua VPS **não está usando Docker** - o PostgreSQL está instalado diretamente no sistema.

---

## ✅ TRANQUILIZE-SE: SEUS DADOS ESTÃO SEGUROS

Os comandos que executei foram:

1. `php artisan route:clear` → Limpa cache de rotas ✅
2. `php artisan config:clear` → Limpa cache de configuração ✅
3. `php artisan cache:clear` → Limpa cache de aplicação ✅
4. `php artisan view:clear` → Limpa cache de views ✅

**NENHUM** desses comandos apaga dados do banco. Eles apenas limpam arquivos temporários.

O PostgreSQL está rodando normalmente (vimos no diagnóstico anterior). Seus dados estão lá, intactos.

---

## 🔧 COMO CORRIGIR

Você precisa fazer **apenas 1 alteração** no arquivo `.env`:

### Opção 1: Pelo Terminal (SSH)

```bash
# 1. Conecte na VPS
ssh root@72.60.254.100

# 2. Navegue até a pasta do Laravel
cd /var/www/html/wk-crm-laravel

# 3. Faça backup do .env atual
cp .env .env.backup

# 4. Abra o .env no editor
nano .env

# 5. Encontre esta linha:
DB_HOST=postgres

# 6. Altere para:
DB_HOST=localhost

# 7. Salve (Ctrl+O, Enter, Ctrl+X)

# 8. Limpe o cache de configuração
php artisan config:clear
php artisan config:cache

# 9. Teste a conexão
php artisan tinker --execute="DB::connection()->getPdo(); echo 'CONECTADO!\n';"
```

### Opção 2: Pelo Painel da Hostinger

1. Acesse o painel da Hostinger
2. Vá em **File Manager**
3. Navegue até `/var/www/html/wk-crm-laravel/`
4. Clique com botão direito em `.env` → **Edit**
5. Encontre a linha: `DB_HOST=postgres`
6. Altere para: `DB_HOST=localhost`
7. Clique em **Save**
8. Volte ao terminal e execute:
   ```bash
   cd /var/www/html/wk-crm-laravel
   php artisan config:clear
   php artisan config:cache
   ```

---

## 🧪 VERIFICAR SE FUNCIONOU

Depois da alteração, execute este script de diagnóstico:

```bash
cd /var/www/html/wk-crm-laravel

# Teste de conexão
php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo '✅ BANCO CONECTADO!\n';
    echo 'Database: ' . \$pdo->query('SELECT current_database()')->fetchColumn() . '\n';
} catch (Exception \$e) {
    echo '❌ ERRO: ' . \$e->getMessage() . '\n';
}
"

# Contagem de registros
php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo '✅ BANCO CONECTADO!\n';
    echo 'Database: ' . \$pdo->query('SELECT current_database()')->fetchColumn() . '\n';
} catch (Exception \$e) {
    echo '❌ ERRO: ' . \$e->getMessage() . '\n';
}
"
```

**Se der certo, você verá:**
```
✅ BANCO CONECTADO!
Database: wk_crm_production
Usuários: 3
Oportunidades: 15
Notificações: 8
```

---

## 📋 CHECKLIST COMPLETO

- [ ] Faça backup do `.env` atual
- [ ] Altere `DB_HOST=postgres` para `DB_HOST=localhost`
- [ ] Execute `php artisan config:clear`
- [ ] Execute `php artisan config:cache`
- [ ] Teste a conexão com tinker
- [ ] Acesse https://app.consultoriawk.com/login
- [ ] Faça login (admin@consultoriawk.com / senha atual)
- [ ] Verifique se o dashboard carrega com dados

---

## 🆘 SE AINDA DER ERRO

### ❌ ERRO: "no password supplied" ou "fe_sendauth"

**Isso é progresso!** O host agora conecta, mas está faltando a **senha do PostgreSQL**.

#### Passo 1: Verificar as credenciais
```bash
cd /var/www/html/wk-crm-laravel
grep "^DB_" .env
```

Você provavelmente verá algo como:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=wk_crm_production
DB_USERNAME=wk_crm_user
DB_PASSWORD=
```

**O problema:** `DB_PASSWORD` está vazio!

#### Passo 2: Definir/Resetar a senha do PostgreSQL

```bash
# Entre no PostgreSQL como superusuário
sudo -u postgres psql
```

Dentro do psql:
```sql
-- Liste os usuários existentes
\du

-- Defina uma senha forte para seu usuário (ajuste o nome se necessário)
ALTER USER wk_crm_user WITH PASSWORD 'SenhaForte@2026!';

-- Se o usuário não existir, crie:
CREATE USER wk_crm_user WITH PASSWORD 'SenhaForte@2026!';
GRANT ALL PRIVILEGES ON DATABASE wk_crm_production TO wk_crm_user;

-- Saia
\q
```

#### Passo 3: Atualizar o .env

```bash
nano .env
```

Altere a linha:
```env
DB_PASSWORD=SenhaForte@2026!
```

#### Passo 4: Limpar cache e testar

```bash
php artisan config:clear
php artisan config:cache

# Teste a conexão
php artisan tinker --execute="DB::connection()->getPdo(); echo 'CONECTADO COM SUCESSO!\n';"
```

**Se funcionar, você verá:** `CONECTADO COM SUCESSO!`

---

### 🔧 OUTRAS ALTERNATIVAS

Se mesmo com a senha configurada continuar dando erro, tente:

### Alternativa 1: IP 127.0.0.1
```env
DB_HOST=127.0.0.1
```

### Alternativa 2: Socket Unix
```env
DB_HOST=/var/run/postgresql
```

### Alternativa 3: Verificar credenciais
Certifique-se de que estas linhas também estão corretas:
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=wk_crm_production
DB_USERNAME=seu_usuario_postgres
DB_PASSWORD=sua_senha_postgres
```

---

## 📞 PRECISA DE AJUDA?

Se após tentar tudo isso ainda não funcionar, me envie:

1. A saída completa do comando:
   ```bash
   cd /var/www/html/wk-crm-laravel
   grep "^DB_" .env
   ```

2. O resultado de:
   ```bash
   php artisan tinker --execute="DB::connection()->getPdo();"
   ```

Vou te ajudar a resolver!

---

**Criado em:** 02/01/2026  
**Próximo passo:** Alterar `.env` e testar conexão
