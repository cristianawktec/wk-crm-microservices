# CORREÇÃO VPS - EMAIL DE LOGIN
## Executar via SSH na VPS

**PROBLEMA IDENTIFICADO:**
- VPS não tem `MAIL_AUDIT_RECIPIENT` configurado no `.env`
- VPS não tem `APP_NOTIFICATION_EMAIL` configurado no `.env`
- Por isso login não envia email

## COMANDOS PARA EXECUTAR NA VPS

```bash
# 1. Conectar na VPS
ssh root@72.60.254.100

# 2. Entrar no diretório do Laravel
cd /opt/wk-crm/wk-crm-laravel

# 3. Verificar se configs já existem
grep "MAIL_AUDIT_RECIPIENT\|APP_NOTIFICATION_EMAIL" .env

# 4. Se NÃO existirem, adicionar ao .env
cat << 'EOF' >> .env

# Email para receber auditorias de login
MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com

# Habilitar envio de emails em notificações
APP_NOTIFICATION_EMAIL=true
EOF

# 5. Verificar se foi adicionado
tail -10 .env

# 6. Limpar cache de config
docker exec wk_crm_laravel php artisan config:cache

# 7. Verificar se config está carregada
docker exec wk_crm_laravel php artisan config:show mail.audit_recipient
docker exec wk_crm_laravel php artisan config:show app.notification_email

# 8. Testar email de login (se tiver o comando)
docker exec wk_crm_laravel php artisan email:test-login-audit

# 9. Verificar notificações no banco (PostgreSQL)
docker exec wk_postgres psql -U postgres -d wk_crm -c "SELECT id, user_id, type, title, created_at FROM notifications ORDER BY created_at DESC LIMIT 5;"

# 10. Verificar login audits no banco
docker exec wk_postgres psql -U postgres -d wk_crm -c "SELECT id, user_id, email, created_at FROM login_audits ORDER BY created_at DESC LIMIT 3;"
```

## RESULTADO ESPERADO

Após executar esses comandos:
- ✅ Login de customer deve enviar email para admin@consultoriawk.com
- ✅ Notificações devem aparecer no banco (já estão funcionando segundo logs)
- ✅ Emails de oportunidade continuam funcionando (já funcionam)

## VERIFICAÇÃO DOS LOGS

Para confirmar problemas no futuro:
```bash
docker exec wk_crm_laravel tail -100 storage/logs/laravel.log | grep -i "email sent successfully"
```

## OBSERVAÇÕES

**LOCALHOST está OK - NÃO MEXER!**

**VPS - Status atual (antes da correção):**
- ✅ Emails de oportunidade: FUNCIONANDO
- ✅ Notificações no banco: SENDO CRIADAS  
- ❌ Email de login SMTP: NÃO ENVIA (falta config)

**VPS - Status após correção:**
- ✅ Tudo deve funcionar
