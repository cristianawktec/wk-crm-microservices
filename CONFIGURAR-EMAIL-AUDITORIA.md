# Configuração de Email para Auditoria

## Problema
O email de auditoria pode não estar sendo enviado porque:

1. **Em localhost com `MAIL_MAILER=log`**: Os emails vão para o arquivo de logs, não para caixa de entrada real
2. **Sem configuração SMTP**: Não há servidor configurado para enviar emails

## Solução

### Para Testar Localmente

Primeiro, teste usando o endpoint de teste:
```
GET http://localhost:8000/api/admin/login-audits/send-test-email
Authorization: Bearer {seu_token}
```

Verá a resposta com status do email e informações do driver configurado.

### Para Visualizar Logs Locais

Os emails em modo `log` são salvos em:
```
storage/logs/laravel.log
```

Monitore com:
```bash
tail -f storage/logs/laravel.log
```

### Para Produção (VPS com HostGator)

Você precisa configurar um servidor SMTP real. Opções:

#### Opção 1: SMTP do HostGator
Se tem cPanel, configure assim no `.env.vps`:
```ini
MAIL_MAILER=smtp
MAIL_HOST=mail.consultoriawk.com.br
MAIL_PORT=587
MAIL_USERNAME=seu-usuario@consultoriawk.com.br
MAIL_PASSWORD=sua-senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=sistema@consultoriawk.com.br
MAIL_FROM_NAME="WK CRM"
MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com
```

#### Opção 2: Gmail SMTP
```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@gmail.com
MAIL_FROM_NAME="WK CRM"
```

#### Opção 3: SendGrid (Recomendado)
```ini
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=sua-api-key
MAIL_FROM_ADDRESS=sistema@consultoriawk.com.br
MAIL_FROM_NAME="WK CRM"
```

#### Opção 4: Mailgun
```ini
MAIL_MAILER=mailgun
MAILGUN_SECRET=sua-secret
MAILGUN_DOMAIN=seu-dominio.mailgun.org
MAIL_FROM_ADDRESS=sistema@consultoriawk.com.br
MAIL_FROM_NAME="WK CRM"
```

## Como Verificar Qual Usar

1. **Acesse seu cPanel HostGator**
2. **Procure por "Email" ou "Mail"**
3. **Verifique**:
   - Mail Server (geralmente `mail.seu-dominio.com.br`)
   - Porta (geralmente 587 ou 465)
   - Credenciais disponíveis

## Próximos Passos

1. Escolha uma opção de email acima
2. Edite `/wk-crm-laravel/.env.vps` com as credenciais
3. Faça git pull na VPS
4. Teste acessando `/api/admin/login-audits`
5. Se não receber, verifique os logs da VPS:
   ```bash
   ssh root@72.60.254.100
   cd /opt/wk-crm
   docker compose logs wk-crm-laravel | grep Email
   ```

## Endpoint de Teste

Para testar imediatamente:
```
GET http://localhost:8000/api/admin/login-audits/send-test-email
Authorization: Bearer {token}
```

Resposta com debug info:
```json
{
  "success": true,
  "message": "Email de teste enviado com sucesso!",
  "recipient": "admin@consultoriawk.com",
  "records_sent": 10,
  "triggered_by": "admin@consultoriawk.com",
  "mail_driver": "smtp",
  "mail_host": "mail.consultoriawk.com.br"
}
```

## Dica de Segurança

Nunca commit credenciais de email no git! Use variáveis de ambiente:
- `.env` para local
- `.env.vps` (gitignore) para produção
- Varie as senhas por ambiente
