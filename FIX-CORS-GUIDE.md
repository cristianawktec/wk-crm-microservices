# 🔧 Fix CORS: Duplicate Headers Issue

## Problema
O console do navegador mostra:
```
The 'Access-Control-Allow-Origin' header contains multiple values 'https://app.consultoriawk.com, *, *', but only one is allowed.
```

Isso acontece porque tanto o Nginx quanto o Laravel estão adicionando headers CORS.

## Solução

### Opção 1: Script Automático (Recomendado)

```bash
# No VPS como root
cd /root/wk-crm-microservices
bash fix-cors-nginx.sh
```

### Opção 2: Manual

1. **SSH no VPS:**
   ```bash
   ssh root@72.60.254.100
   ```

2. **Editar Nginx:**
   ```bash
   nano /etc/nginx/sites-available/api.consultoriawk.com
   ```

3. **Remover/Comentar estas linhas:**
   ```nginx
   # REMOVER ESTAS LINHAS (Laravel já gerencia):
   add_header 'Access-Control-Allow-Origin' '*';
   add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS, PUT, DELETE';
   add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization';
   add_header 'Access-Control-Expose-Headers' 'Content-Length,Content-Range';
   ```

4. **Testar e Recarregar:**
   ```bash
   nginx -t
   systemctl reload nginx
   ```

5. **Verificar:**
   - Abrir app.consultoriawk.com
   - F12 → Console → Verificar se erros CORS sumiram
   - SSE deve conectar sem erros

## Por que isso funciona?

- ✅ Laravel `CorsMiddleware` gerencia CORS corretamente (já implementado)
- ✅ Reflete o Origin correto dinamicamente
- ✅ Suporta credentials
- ✅ Trata OPTIONS preflight
- ❌ Nginx adicionando headers duplica e quebra CORS

## Resultado Esperado

Após o fix:
- ✅ SSE conecta sem erros CORS
- ✅ Notificações em tempo real funcionam
- ✅ Apenas um header `Access-Control-Allow-Origin: https://app.consultoriawk.com`

## Alternativa: Se não tiver acesso SSH

Se você não conseguir acessar o VPS, pode:
1. Desabilitar SSE temporariamente no frontend (comentar linha de inicialização)
2. Aguardar até ter acesso SSH para corrigir
3. Contatar suporte da Hostinger para ajudar com configuração Nginx
