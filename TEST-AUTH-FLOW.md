# Teste do Fluxo de Autenticação

## Problema Identificado

Você estava sendo redirecionado diretamente para o dashboard ao acessar `/` mesmo sem fazer login. Isso ocorria porque:

1. **Token Expirado**: O localStorage ainda continha um token antigo de testes anteriores
2. **Sem Validação**: O frontend não validava se o token ainda era válido no backend
3. **Redirect Automático**: O LoginComponent verificava apenas `isAuthenticated()` (token existe) e redirecionava

## Solução Implementada

### Backend (Laravel)
- ✅ Rota `/auth/me` protegida com `auth:sanctum` middleware
- ✅ Retorna 401 se o token for inválido/expirado
- ✅ Valida o token contra o banco de dados

### Frontend (Angular)
1. **AuthService**: Adicionado método `verifyToken()`
   - Faz requisição GET ao `/auth/me`
   - Limpa dados se o token for inválido

2. **LoginComponent**: Validação ao carregar
   - Se encontrar token no localStorage, verifica com backend
   - Se válido → vai para dashboard
   - Se inválido → força logout e fica na página de login

3. **AuthGuard**: Proteção de rotas
   - Verifica token com backend
   - Se inválido → faz logout automático
   - Se não autenticado → redireciona para /login

## Como Testar

### Teste 1: Acesso Sem Token (ESPERADO: Deve ir para login)
```
1. Abrir DevTools (F12)
2. Limpar Application → Local Storage
3. Acessar http://localhost/admin/
4. RESULTADO: Deve ser redirecionado para /login
```

### Teste 2: Acesso Com Token Válido (ESPERADO: Dashboard funciona)
```
1. Fazer login normalmente
2. Atualizar página (F5)
3. RESULTADO: Deve continuar no dashboard (localStorage tem token válido)
```

### Teste 3: Acesso Com Token Expirado (ESPERADO: Retorna a login)
```
1. Fazer login
2. Ir ao DevTools → Application → Local Storage
3. Deletar campo "token" manualmente
4. Atualizar página
5. RESULTADO: Deve ser redirecionado para /login
```

### Teste 4: API Sem Token (ESPERADO: 401 Unauthorized)
```powershell
curl -i http://localhost:8000/api/customers
# HTTP/1.1 401 Unauthorized
```

### Teste 5: API Com Token Válido (ESPERADO: Dados retornam)
```powershell
# Login primeiro
$login = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
  -Method Post `
  -Body '{"email":"admin@consultoriawk.com","password":"Admin@123456"}' `
  -ContentType "application/json"

$token = $login.token

# Acessar API com token
Invoke-RestMethod -Uri "http://localhost:8000/api/customers" `
  -Headers @{Authorization="Bearer $token"}
# Deve retornar array de clientes
```

## Arquivos Modificados

1. **wk-admin-frontend/src/app/services/auth.service.ts**
   - Adicionado `verifyToken()` method
   - Melhoria em `isAuthenticated()`

2. **wk-admin-frontend/src/app/guards/auth.guard.ts**
   - Adicionada verificação de token com backend
   - Logout automático se token inválido

3. **wk-admin-frontend/src/app/components/login/login.component.ts**
   - Validação de token ao carregar
   - Logout se token expirado

4. **wk-crm-laravel/routes/api.php**
   - Adicionada rota `/auth/me` protegida
   - Movida para dentro do grupo `auth:sanctum`

5. **wk-admin-frontend/src/environments/environment.prod.ts**
   - Atualizada URL da API para produção: `https://api.consultoriawk.com/api`

## Próximos Passos

1. Teste em ambos os ambientes (localhost e VPS)
2. Verifique o erro na página de customers da VPS
3. Se necessário, ajuste a URL da API em produção

## Resumo

✅ Autenticação agora é **obrigatória** para acessar qualquer rota
✅ Token é **validado** com o backend ao acessar a aplicação
✅ Token **expirado** força logout automático
✅ Acesso SEM token **redireciona** para login
