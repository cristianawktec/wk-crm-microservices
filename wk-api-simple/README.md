# WK CRM - Simple API

API simples em PHP para demonstrar integração com o frontend AdminLTE.

## Endpoints Disponíveis

### Dashboard
- `GET /api/dashboard` - Métricas do dashboard

### Clientes
- `GET /api/customers` - Lista todos os clientes
- `GET /api/customers/{id}` - Busca cliente específico
- `POST /api/customers` - Cria novo cliente

### Oportunidades
- `GET /api/opportunities` - Lista todas as oportunidades

### Health Check
- `GET /api/health` - Status da API

## Como usar

1. Inicie o servidor PHP:
```bash
php -S localhost:8000
```

2. Teste os endpoints:
```bash
curl http://localhost:8000/api/health
curl http://localhost:8000/api/dashboard
curl http://localhost:8000/api/customers
```

## CORS

A API já está configurada com CORS para permitir requisições do frontend.

## Dados

Os dados são simulados em memória. Em produção, conectar com MySQL/PostgreSQL.