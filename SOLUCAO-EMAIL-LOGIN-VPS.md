# SOLUÇÃO RÁPIDA - EMAIL DE LOGIN VPS

## O Problema:
- ❌ Email não é enviado quando faz login na VPS
- 📋 Causa: Falta `MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com` no `.env`

## Solução em 3 passos:

### PASSO 1: Adicionar config ao .env

Abra o arquivo `/opt/wk-crm/wk-crm-laravel/.env` e **no final do arquivo**, adicione esta linha:

```
MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com
```

Salve o arquivo.

### PASSO 2: Limpar cache

No terminal SSH da VPS, execute:

```bash
cd /opt/wk-crm/wk-crm-laravel
docker exec wk_crm_laravel php artisan config:cache
```

### PASSO 3: Verificar se funcionou

Execute este comando:

```bash
docker exec wk_crm_laravel php artisan config:show mail.audit_recipient
```

Se aparecer `'admin@consultoriawk.com'` → ✅ **FUNCIONOU!**

---

## Teste Final:

1. Faça logout do app
2. Faça login novamente
3. Verifique o email `admin@consultoriawk.com`
4. Deve ter chegado um email com título "Login Audit Report"

---

## Se não funcionar:

1. Verifique se `MAIL_AUDIT_RECIPIENT` está no final do `.env`
2. Verifique se rodou `config:cache` 
3. Confira se não há outras linhas iguais no `.env` (pode ter duplicado)
4. Se tiver duplicado, remova uma

---

**⚠️ IMPORTANTE:** Não mexa em outras configs, apenas adicione esta linha!
