# 🔔 Teste de Correção de Notificações Duplicadas

**Data:** 10 de Janeiro de 2026  
**Problema Resolvido:** Notificações duplicadas ao criar oportunidades  
**Commit:** 99d352d

---

## 🐛 Problema Original

Quando o usuário admin/manager criava uma oportunidade, recebia **múltiplas notificações** da mesma ação porque:
- O sistema buscava todos os admins/managers na base
- Incluía o próprio criador na lista de destinatários
- Não removia duplicatas de user_id

## ✅ Solução Implementada

### 1. **Exclusão do Criador**
- O usuário que cria/atualiza uma oportunidade **não recebe** notificação da própria ação
- Parâmetro `$createdBy` ou `$changedBy` é passado nos métodos de notificação

### 2. **Remoção de Duplicatas**
- `array_unique()` garante que cada user_id aparece apenas uma vez na lista
- `array_diff()` remove o ID do criador/alterador

### 3. **Logs Aprimorados**
- Contador de destinatários após exclusões
- Registro de quem foi excluído (`excluded_creator`, `changed_by`)
- Logs em cada etapa do processo

---

## 🧪 Como Testar

### Teste 1: Criar Oportunidade (Principal)
1. Faça login em https://app.consultoriawk.com
2. Vá para **Oportunidades** → **Nova Oportunidade**
3. Preencha os dados:
   - Título: "Teste Notificação - Sem Duplicatas"
   - Valor: R$ 10.000,00
   - Status: Open
4. Clique em **Salvar**

**Resultado Esperado:**
- ✅ Toast aparece com sucesso
- ✅ **APENAS 1 notificação** criada (visível no sino 🔔)
- ✅ Ao clicar no sino, você **NÃO** deve ver a notificação (você é o criador)
- ✅ Se houver outros admins, eles recebem 1 notificação cada

### Teste 2: Atualizar Status
1. Edite a oportunidade criada
2. Mude o status de **Open** para **Negotiation**
3. Salve

**Resultado Esperado:**
- ✅ Você **NÃO recebe** notificação (você fez a mudança)
- ✅ Outros admins/managers recebem 1 notificação de "Status Atualizado"

### Teste 3: Atualizar Valor (>10%)
1. Edite a oportunidade
2. Mude o valor de R$ 10.000,00 para R$ 15.000,00 (aumento de 50%)
3. Salve

**Resultado Esperado:**
- ✅ Você **NÃO recebe** notificação
- ✅ Outros admins recebem notificação "📈 Valor Alterado"

### Teste 4: Múltiplos Usuários (se disponível)
1. Crie um segundo usuário admin (ou use existente)
2. Faça login com o **primeiro usuário**
3. Crie uma oportunidade
4. Faça logout e login com o **segundo usuário**
5. Verifique as notificações

**Resultado Esperado:**
- ✅ Segundo usuário vê **1 notificação** da oportunidade criada pelo primeiro
- ✅ Primeiro usuário **NÃO** vê notificação (ele criou)

---

## 📊 Validação nos Logs

Para verificar nos logs do Laravel (backend):

```bash
# VPS
ssh root@72.60.254.100
docker exec -it wk_crm_laravel tail -f storage/logs/laravel.log
```

Busque por linhas como:
```
[NotificationService] managerIds fetched
  count: 2
  excluded_creator: 123
  ms: 45
```

Se `excluded_creator` aparece e `count` é menor que o total de admins, está funcionando!

---

## 🔍 Verificação no Banco de Dados

```sql
-- Ver últimas notificações criadas
SELECT 
    id,
    user_id,
    type,
    title,
    message,
    created_at
FROM notifications
WHERE type = 'opportunity_created'
ORDER BY created_at DESC
LIMIT 10;

-- Contar notificações por oportunidade
SELECT 
    data->>'opportunity_id' as opp_id,
    data->>'opportunity_title' as title,
    COUNT(*) as notification_count
FROM notifications
WHERE type = 'opportunity_created'
GROUP BY data->>'opportunity_id', data->>'opportunity_title'
HAVING COUNT(*) > 1
ORDER BY notification_count DESC;
```

**Resultado Esperado:** Nenhuma oportunidade deve ter `notification_count > número de admins - 1`

---

## 📝 Checklist de Validação

- [ ] Criar oportunidade gera apenas 1 notificação no total
- [ ] Criador não recebe sua própria notificação
- [ ] Notificação aparece no sino 🔔 dos outros admins
- [ ] Clicar em "Ver Detalhes" abre a oportunidade correta
- [ ] Marcar como lida funciona
- [ ] Atualizar status não notifica o alterador
- [ ] Atualizar valor (+10%) não notifica o alterador
- [ ] Logs mostram `excluded_creator` presente
- [ ] Banco não tem notificações duplicadas para mesma ação

---

## 🐛 Se Ainda Ver Duplicatas

1. **Limpar notificações antigas** (banco de dados):
```sql
DELETE FROM notifications WHERE created_at < NOW() - INTERVAL '1 hour';
```

2. **Verificar roles do usuário**:
```sql
SELECT u.id, u.name, r.name as role
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.id = SEU_USER_ID;
```

Se o usuário tem múltiplas roles (admin E manager), isso é normal — o fix já garante que ele aparece apenas uma vez na lista.

3. **Verificar múltiplas conexões SSE**:
- Abra DevTools → Network
- Filtre por "stream"
- Deve haver **apenas 1** conexão ativa para `/api/notifications/stream`
- Se houver mais de 1, feche e reabra o navegador

---

## 📄 Arquivos Alterados

- `wk-crm-laravel/app/Services/NotificationService.php`
  - `opportunityCreated()`: Adiciona `array_unique()` e `array_diff()`
  - `opportunityStatusChanged()`: Adiciona exclusão do alterador
  - `opportunityValueChanged()`: Adiciona exclusão do alterador
  
- `wk-crm-laravel/app/Http/Controllers/Api/OpportunityController.php`
  - `store()`: Passa `$request->user()` como criador
  - `update()`: Passa `$request->user()` como alterador

---

## ✅ Critérios de Sucesso

✔️ **Zero notificações duplicadas** para o mesmo evento  
✔️ **Criador não recebe** notificação da própria ação  
✔️ **Outros admins recebem** exatamente 1 notificação cada  
✔️ **Logs confirmam** exclusão do criador  
✔️ **Performance mantida** (< 100ms para notifyMany)  

---

**Status:** ✅ Deploy completo na VPS (commit 99d352d)  
**Próximo Teste:** Criar nova oportunidade e validar
