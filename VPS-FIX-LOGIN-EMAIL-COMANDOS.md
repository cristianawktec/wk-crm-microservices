# CORREÇÃO VPS - Email de Login
## Execute estes comandos NO TERMINAL SSH da VPS

Você já está conectado à VPS (vi no terminal da imagem).
Execute os comandos abaixo UM POR UM:

```bash
# 1. Ir para o diretório do Laravel
cd /opt/wk-crm/wk-crm-laravel

# 2. Adicionar a config faltante ao .env
echo "MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com" >> .env

# 3. Verificar se foi adicionado
tail -5 .env

# 4. Limpar cache de configuração
docker exec wk_crm_laravel php artisan config:cache

# 5. Verificar se a config foi carregada
docker exec wk_crm_laravel php artisan config:show mail.audit_recipient

# 6. PRONTO! Agora faça logout e login novamente
# O email de login deve chegar em admin@consultoriawk.com
```

## O que cada comando faz:

1. **cd** - Entra no diretório do projeto Laravel
2. **echo >> .env** - Adiciona a linha `MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com` ao arquivo .env
3. **tail -5** - Mostra as últimas 5 linhas do .env (para confirmar que foi adicionado)
4. **config:cache** - Limpa e recarrega todas as configurações
5. **config:show** - Mostra o valor carregado da config (deve mostrar `admin@consultoriawk.com`)
6. **Teste** - Faça logout do app e login novamente para testar

## Resultado esperado:

Após executar o comando 5, você deve ver:
```
'admin@consultoriawk.com'
```

Se aparecer isso, FUNCIONOU! ✅

## Teste final:

1. Abra o app: https://app.consultoriawk.com
2. Faça logout
3. Faça login novamente com qualquer usuário
4. Verifique o email admin@consultoriawk.com
5. Deve ter chegado um email com "Login Audit Report"

---

**IMPORTANTE:** Não precisa reiniciar containers, só o `config:cache` já basta!
