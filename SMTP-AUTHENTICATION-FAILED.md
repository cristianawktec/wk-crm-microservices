# ❌ PROBLEMA: AUTENTICAÇÃO SMTP FALHOU

## 🔴 ERRO IDENTIFICADO

```
❌ Failed to authenticate on SMTP server
Code: 535 5.7.8 Error: authentication failed
Servidor: smtp.titan.email
Usuário: admin@consultoriawk.com
Senha testada: admin3113#
```

---

## 🎯 CAUSA PROVÁVEL

A senha SMTP fornecida (`admin3113#`) está sendo **rejeitada** pelo servidor Titan Email.

**Possíveis razões:**
1. ✅ A senha está **incorreta** ou expirou
2. ✅ É necessária uma **senha de aplicativo** específica (não a mesma do webmail)
3. ✅ A conta precisa **habilitar acesso SMTP** no painel
4. ✅ O servidor SMTP correto é outro (não smtp.titan.email)

---

## 🔧 COMO RESOLVER

### **Passo 1: Verificar Credenciais no Painel Titan**

1. Acesse: https://titan.email (ou painel HostGator)
2. Faça login com: **admin@consultoriawk.com**
3. Vá em **Configurações** → **Senha e Segurança**
4. Verifique se existe opção **"Senhas de Aplicativo"** ou **"App Passwords"**

Se existir, você precisa:
- Criar uma **senha específica para SMTP**
- Usar essa senha em vez de `admin3113#`

---

### **Passo 2: Verificar Configurações SMTP**

No painel do Titan Email, procure por:
- **Configurações de Email** ou **SMTP Settings**
- Confirme:
  - **Servidor SMTP:** `smtp.titan.email` ✅ ou outro?
  - **Porta:** `587` (TLS) ou `465` (SSL)
  - **Autenticação:** Requerida
  - **Username:** `admin@consultoriawk.com` (email completo)

---

### **Passo 3: Testar Manualmente**

Para confirmar que as credenciais funcionam, teste com cliente de email:

**Thunderbird / Outlook / Mail:**
```
Servidor SMTP: smtp.titan.email
Porta: 587
Segurança: STARTTLS
Usuário: admin@consultoriawk.com
Senha: [senha correta]
```

Se conseguir enviar email manualmente, a senha está correta.

---

## 📋 CONFIGURAÇÕES ALTERNATIVAS TESTADAS

### ❌ Tentativa 1: Porta 587 + TLS
```env
MAIL_HOST=smtp.titan.email
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_PASSWORD=admin3113#
```
**Resultado:** Erro 535 - autenticação falhou

### ❌ Tentativa 2: Porta 465 + SSL
```env
MAIL_HOST=smtp.titan.email
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_PASSWORD=admin3113#
```
**Resultado:** Não testada completamente (primeiro teste falhou)

---

## ✅ SOLUÇÃO TEMPORÁRIA APLICADA

Sistema voltou para **modo LOG** (desenvolvimento):

```env
MAIL_MAILER=log
```

**O que isso significa:**
- ✅ Login funciona normalmente
- ✅ Email é "enviado" para `storage/logs/laravel.log`
- ❌ Email NÃO chega na caixa de entrada real
- ✅ Útil para desenvolvimento/testes

---

## 🚀 PRÓXIMOS PASSOS

### **URGENTE - Validar Credenciais:**

1. ✅ Entre no painel Titan Email
2. ✅ Verifique/gere senha de aplicativo para SMTP
3. ✅ Confirme servidor SMTP correto
4. ✅ Teste envio manual com cliente de email

### **Depois de obter credenciais corretas:**

```bash
# Editar .env no Laravel:
nano wk-crm-laravel/.env

# Adicionar:
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email  # confirmar servidor correto
MAIL_PORT=587              # ou 465
MAIL_USERNAME=admin@consultoriawk.com
MAIL_PASSWORD=[SENHA_CORRETA_AQUI]
MAIL_ENCRYPTION=tls        # ou ssl
MAIL_FROM_ADDRESS=admin@consultoriawk.com
MAIL_AUDIT_RECIPIENT=admin@consultoriawk.com

# Limpar cache:
php artisan config:clear

# Testar:
php test-smtp.php
```

---

## 📧 INFORMAÇÕES DE CONTATO TITAN

**Suporte HostGator/Titan:**
- Painel: https://hostgator.com.br
- Chat/Telefone: Verificar no painel
- Documentação: https://www.hostgator.com.br/ajuda

**O que perguntar ao suporte:**
> "Preciso configurar SMTP para envio de emails via aplicação Laravel.
> Qual o servidor SMTP correto, porta e tipo de senha (normal ou app password)?"

---

## 🧪 TESTE RÁPIDO DISPONÍVEL

Depois de obter credenciais corretas:

```bash
# No terminal do projeto:
docker exec wk_crm_laravel php test-smtp.php

# Deve aparecer:
# ✅ Email enviado com sucesso!
```

---

**Status Atual:** ⚠️ **AGUARDANDO SENHA SMTP CORRETA**  
**Sistema:** ✅ Funcionando em modo LOG  
**Próxima Ação:** Validar credenciais no painel Titan Email
