# 🚀 Plataforma de Microserviços WK CRM

Sistema de Gestão de Relacionamento com Cliente (CRM) de nível empresarial construído com arquitetura moderna de microserviços.

## 🏗️ Visão Geral da Arquitetura

```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│  Painel Admin   │  │  App Cliente    │  │ Serviço de IA   │
│   (Angular 18)  │  │    (Vue 3)      │  │ (Python/FastAPI)│
└─────────────────┘  └─────────────────┘  └─────────────────┘
         │                     │                     │
         └─────────────────────┼─────────────────────┘
                               │
                    ┌─────────────────┐
                    │   Gateway API   │
                    │  (Node.js/TS)   │
                    └─────────────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
    ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
    │   CRM Laravel   │ │   CRM .NET 8    │ │  API Produtos   │
    │  (Laravel 11)   │ │ (Clean Arch)    │ │   (Node.js)     │
    └─────────────────┘ └─────────────────┘ └─────────────────┘
              │                │                │
              └────────────────┼────────────────┘
                               │
                    ┌─────────────────┐
                    │   PostgreSQL    │
                    │     Redis       │
                    └─────────────────┘
```

## 🛠️ Stack Tecnológica

### Serviços Backend
- **🎯 Gateway API**: Node.js + TypeScript + Express
- **🏢 CRM Laravel**: Laravel 11 + DDD + PostgreSQL
- **🏢 CRM .NET**: .NET 8 + Clean Architecture + Entity Framework
- **📦 API Produtos**: Node.js + Express + PostgreSQL
- **🤖 Serviço IA**: Python + FastAPI + Google Gemini

### Aplicações Frontend
- **👨‍💼 Painel Admin**: Angular 18 + Angular Material
- **👤 App Cliente**: Vue 3 + Tailwind CSS

### Infraestrutura
- **🐳 Containerização**: Docker + Docker Compose
- **🗄️ Banco de Dados**: PostgreSQL 16
- **⚡ Cache**: Redis 7
- **🌐 Proxy Reverso**: Nginx
- **📊 Monitoramento**: Verificações de saúde integradas

## 🚀 Início Rápido

### Pré-requisitos
- Docker Desktop
- Node.js 20+
- .NET 8 SDK
- PHP 8.3+
- Python 3.11+

### 1. Clonar e Configurar
```bash
git clone <repository-url>
cd wk-crm-microservices
cp .env.example .env
```

### 2. Configurar Ambiente
Edite o arquivo `.env` com sua configuração:
```env
# Banco de Dados
POSTGRES_DB=wk_main
POSTGRES_USER=wk_user
POSTGRES_PASSWORD=secure_password_123

# Redis
REDIS_PASSWORD=redis_password

# Chaves API
GEMINI_API_KEY=your_gemini_api_key
```

### 3. Iniciar Todos os Serviços
```bash
# Iniciar todos os serviços
docker-compose up -d

# Verificar status
docker-compose ps

# Ver logs
docker-compose logs -f
```

### 4. Acessar Aplicações
- **🌐 Gateway Principal**: http://localhost:3000
- **👨‍💼 Painel Admin**: http://localhost:4200
- **👤 App Cliente**: http://localhost:3002
- **🗄️ Banco de Dados**: localhost:5432
- **⚡ Redis**: localhost:6379

## 📋 Desenvolvimento

### API CRM Laravel (Principal)
```bash
cd wk-crm-laravel
composer install
php artisan migrate
php artisan serve --port=8080
```

### API CRM .NET (Demo)
```bash
cd wk-crm-dotnet
dotnet restore
dotnet ef database update
dotnet run --urls=http://localhost:5000
```

### Serviços Node.js
```bash
# Gateway API
cd wk-gateway
npm install
npm run dev

# API Produtos
cd wk-products-api
npm install
npm run dev
```

### Serviço Python IA
```bash
cd wk-ai-service
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```

### Aplicações Frontend
```bash
# Angular Admin
cd wk-admin-frontend
npm install
ng serve --port 4200

# Vue App Cliente
cd wk-customer-app
npm install
npm run dev
```

## 🏗️ Estrutura do Projeto

```
wk-crm-microservices/
├── 🔧 infrastructure/           # Docker, Nginx, deployment
├── 📦 wk-crm-laravel/          # Laravel 11 + DDD
│   ├── app/Domain/             # Entidades e serviços do domínio
│   ├── app/Application/        # Casos de uso e DTOs
│   └── app/Infrastructure/     # Repositórios e serviços externos
├── 📦 wk-crm-dotnet/           # .NET 8 + Clean Architecture
│   ├── src/Domain/             # Entidades do domínio
│   ├── src/Application/        # CQRS + MediatR
│   ├── src/Infrastructure/     # Acesso a dados e serviços externos
│   └── src/WebApi/             # Controladores REST API
├── 📦 wk-products-api/         # Node.js + Express
├── 📦 wk-gateway/              # Gateway API + Auth
├── 🎨 wk-admin-frontend/       # Painel admin Angular 18
├── 🎨 wk-customer-app/         # Portal cliente Vue 3
├── 🤖 wk-ai-service/           # Python + FastAPI + IA
└── 📚 docs/                    # Documentação
```

## 🔒 Recursos de Segurança

- Autenticação e Autorização JWT
- Controle de acesso baseado em funções (RBAC)
- Limitação de taxa de API
- Validação de requisições
- Proteção CORS
- Cabeçalhos de segurança Helmet
- Proteção de consultas de banco de dados
- Gerenciamento de sessão Redis

## 📊 Princípios de Desenvolvimento

### Design Orientado ao Domínio (DDD)
- **Entidades**: Objetos centrais do negócio
- **Objetos de Valor**: Contêineres de dados imutáveis
- **Agregados**: Limites de consistência
- **Repositórios**: Abstração de acesso a dados
- **Serviços**: Coordenação de lógica de negócio

### Arquitetura Limpa (.NET)
- **Camada de Domínio**: Entidades e regras de negócio
- **Camada de Aplicação**: Casos de uso e interfaces
- **Camada de Infraestrutura**: Dados e serviços externos
- **Camada de Apresentação**: Controladores REST API

### Princípios SOLID
- Princípio da Responsabilidade Única
- Princípio Aberto/Fechado
- Princípio da Substituição de Liskov
- Princípio da Segregação de Interface
- Princípio da Inversão de Dependência

## 🧪 Testes

### Testes Unitários
```bash
# Laravel
cd wk-crm-laravel && php artisan test

# .NET
cd wk-crm-dotnet && dotnet test

# Node.js
cd wk-gateway && npm test
cd wk-products-api && npm test

# Python
cd wk-ai-service && pytest

# Angular
cd wk-admin-frontend && ng test

# Vue
cd wk-customer-app && npm run test:unit
```

### Testes de Integração
```bash
# Executar todos os serviços
docker-compose up -d

# Executar suite de teste de integração
npm run test:integration
```

## 📈 Monitoramento e Verificações de Saúde

### Endpoints de Saúde
- **Gateway**: http://localhost:3000/health
- **CRM Laravel**: http://localhost:8080/api/health
- **CRM .NET**: http://localhost:5000/health
- **API Produtos**: http://localhost:3001/health
- **Serviço IA**: http://localhost:8080/health

### Logging
Todos os serviços implementam logging estruturado com Winston (Node.js), Serilog (.NET) e Python logging.

## 🚀 Deploy

### Docker Produção
```bash
# Construir todas as imagens
docker-compose -f docker-compose.prod.yml build

# Deploy para produção
docker-compose -f docker-compose.prod.yml up -d
```

### Configurações Específicas por Ambiente
- `docker-compose.yml` - Desenvolvimento
- `docker-compose.prod.yml` - Produção
- `docker-compose.test.yml` - Testes

## 🎯 Tasks Disponíveis no VS Code

Use `Ctrl+Shift+P` e digite "Tasks" para acessar:

- **Iniciar Microserviços WK CRM**: Inicia todos os containers Docker
- **Iniciar Servidor Laravel**: Inicia servidor de desenvolvimento Laravel
- **Parar Servidor Laravel**: Para o servidor Laravel
- **Parar Microserviços**: Para todos os containers
- **Ver Logs Laravel**: Visualiza logs em tempo real
- **Limpar Cache Laravel**: Remove cache de configuração

## 🤝 Contribuindo

1. Faça fork do repositório
2. Crie branch de feature (`git checkout -b feature/amazing-feature`)
3. Commit suas mudanças (`git commit -m 'Add amazing feature'`)
4. Push para a branch (`git push origin feature/amazing-feature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 👨‍💻 Autor

**Cristian MS**  
- Email: cristianms.awk@gmail.com
- GitHub: [@cristianawktec](https://github.com/cristianawktec)

## 🙏 Agradecimentos

- Framework Laravel
- .NET Foundation
- Equipe Vue.js
- Equipe Angular
- FastAPI
- Comunidade Docker

---

**Construído com ❤️ usando arquitetura moderna de microserviços e melhores práticas.**