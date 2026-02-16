# Funcionalidade: Email de Auditoria de Login

## Descrição
Quando um **admin** acessa o endpoint de auditoria de login (`/api/admin/login-audits`), um email é automaticamente enviado para o endereço configurado com um **relatório completo** de acessos ao sistema.

## Arquivos Criados/Modificados

### 1. **App/Mail/LoginAuditMail.php** (NOVO)
- Mailable que formata os dados de auditoria para envio por email
- Recebe coleção de audits, email de destinatário e quem disparou a auditoria

### 2. **resources/views/emails/login-audit.blade.php** (NOVO)
- Template HTML do email com:
  - Cabeçalho com informações do relatório
  - Tabela formatada com dados:
    - Data/Hora do login
    - Usuário (nome)
    - Email do usuário
    - Endereço IP
    - Navegador (browser)
    - Sistema Operacional (SO)
    - Tipo de Dispositivo
    - Rota acessada
  - Aviso de segurança

### 3. **app/Http/Controllers/Api/LoginAuditController.php** (MODIFICADO)
- Importado `LoginAuditMail` e `Mail`
- Adicionado método `sendAuditEmail()` que:
  - Lê email de destinatário da config
  - Limita a 50 registros mais recentes
  - Enfileira email para processamento assíncrono
  - Inclui tratamento de erros

### 4. **config/mail.php** (NOVO)
- Arquivo de configuração do Laravel Mail
- Define opções de SMTP, Mailgun, Postmark, etc
- Inclui linha para `audit_recipient` que vem da variável de ambiente

### 5. **.env** (MODIFICADO)
- Adicionadas variáveis de configuração:
  ```
  MAIL_MAILER=log
  MAIL_HOST=localhost
  MAIL_PORT=1025
  MAIL_USERNAME=
  MAIL_PASSWORD=
  MAIL_ENCRYPTION=null
  MAIL_FROM_ADDRESS=noreply@consultoriawk.com.br
  MAIL_FROM_NAME="WK CRM"
  MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com
  ```

## Como Funciona

1. **Admin acessa** `/api/admin/login-audits`
2. **Controller valida** se é admin
3. **Busca dados** de auditoria do banco
4. **Dispara email** com:
   - Timestamp de quando foi gerado
   - Nome de quem disparou
   - Todos os registros de login (últimos 50)
5. **Email é enfileirado** para envio assíncrono

## Variáveis de Ambiente

**Para alterar o email de destino**, edite `.env` ou `.env.vps`:
```bash
MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com
```

## Configuração de Email

Para produção em VPS, configure com SMTP real:
```bash
MAIL_MAILER=smtp
MAIL_HOST=seu-smtp.com.br
MAIL_PORT=587
MAIL_USERNAME=seu-usuario
MAIL_PASSWORD=sua-senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=sistema@consultoriawk.com.br
```

## Testes

Para testar localmente com modo "log":
```bash
# Verificar arquivo de log
tail -f storage/logs/laravel.log

# O email será mostrado lá
```

## Segurança

- ✅ Apenas admins podem acessar o endpoint
- ✅ Email inclui aviso de dados sensíveis
- ✅ Erros de envio são capturados e logados
- ✅ Email enfileirado para não bloquear a request
