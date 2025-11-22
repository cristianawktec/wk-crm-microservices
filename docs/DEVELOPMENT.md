# 🚀 WK CRM Development Guide

## 🏁 Getting Started

### 1. Prerequisites Check
Ensure you have these installed:
- ✅ Docker Desktop (Windows)
- ✅ Node.js 20+
- ✅ .NET 8 SDK  
- ✅ PHP 8.3+ with Composer
- ✅ Python 3.11+
- ✅ VS Code with extensions

### 2. Environment Setup
```bash
# Copy environment template
cp .env.example .env

# Edit with your configuration
code .env
```

### 3. Quick Development Start
```bash
# Option 1: Full Docker Stack (Recommended)
docker-compose up -d

# Option 2: Individual Services for Development
# Terminal 1 - Database Services
docker-compose up -d postgres redis

# Terminal 2 - Laravel CRM (Primary)
cd wk-crm-laravel
composer install
php artisan migrate
php artisan serve --port=8000

# Terminal 3 - API Gateway
cd wk-gateway
npm install
npm run dev

# Terminal 4 - Products API
cd wk-products-api
npm install
npm run dev

# Terminal 5 - Admin Frontend
cd wk-admin-frontend
npm install
ng serve --port=4200
```

## 🎯 Development Workflow

### Laravel CRM (Primary) - DDD Approach
```bash
cd wk-crm-laravel

# Install dependencies
composer install

# Database setup
php artisan migrate
php artisan db:seed

# Run tests
php artisan test

# Code style
./vendor/bin/pint
```

**DDD Structure:**
```
app/Domain/
├── Customer/
│   ├── Entities/
│   ├── ValueObjects/
│   ├── Services/
│   └── Repositories/
├── Order/
└── Common/

app/Application/
├── UseCases/
├── DTOs/
└── Services/

app/Infrastructure/
├── Repositories/
├── Services/
└── External/
```

### .NET CRM (Demo) - Clean Architecture
```bash
cd wk-crm-dotnet

# Restore packages
dotnet restore

# Database migration
dotnet ef database update

# Run tests
dotnet test

# Start API
dotnet run --project src/WebApi
```

**Clean Architecture Structure:**
```
src/Domain/           # Entities, Value Objects, Enums
src/Application/      # Use Cases, DTOs, Interfaces
src/Infrastructure/   # Data Access, External Services
src/WebApi/          # Controllers, Middleware
```

### Node.js Services
```bash
# API Gateway
cd wk-gateway
npm install
npm run dev          # http://localhost:3000

# Products API  
cd wk-products-api
npm install
npm run dev          # http://localhost:3001
```

### Frontend Development
```bash
# Angular Admin Panel

---

## 🧩 UI Components & Theming (Admin Frontend)

### ThemeService
- Centraliza controle de tema claro/escuro usando CSS variables aplicadas em `:root`.
- Arquivo: `src/app/core/services/theme.service.ts`.
- Variáveis principais de cor: `--color-primary`, `--color-accent`, `--color-success`, `--color-warning`, `--color-danger`.
- Persistência: chave `wkcrm_theme` no `localStorage`.
- Uso rápido:
```ts
constructor(private theme: ThemeService) {}
this.theme.toggleTheme(); // alterna entre light/dark
```

### SmallBoxComponent
- Componente para métricas principais (cards destacados do dashboard).
- Arquivo: `src/app/components/small-box/*`.
- Inputs:
  - `title: string`
  - `value: string | number`
  - `icon: string` (classes Font Awesome)
  - `color: 'info' | 'warning' | 'success' | 'danger'`
- Exemplo:
```html
<app-small-box title="Vendas" [value]="42" icon="fas fa-shopping-cart" color="success"></app-small-box>
```

### InfoBoxComponent
- Métricas secundárias e informações detalhadas (texto + progresso opcional).
- Arquivo: `src/app/components/info-box/*`.
- Inputs:
  - `title`, `value`, `icon`, `color` (inclui 'primary'), `subtitle?`, `progressPercent?` (0–100)
- Exemplo:
```html
<app-info-box title="Conversão" [value]="taxa + '%'" icon="fas fa-percentage" color="success" [progressPercent]="taxa"></app-info-box>
```

### Cores de Gráficos (Chart.js via ng2-charts)
- As cores dos gráficos são recalculadas a cada mudança de tema lendo as CSS vars.
- Lógica em `dashboard.component.ts` (`updateCharts`).
- Para adicionar nova cor temática, inclua a var em `ThemeService` e utilize em `updateCharts`.

### Boas Práticas de Estilo
- Evitar dependência de CSS global legado do AdminLTE: usamos componentes isolados.
- Preferir gradientes definidos via CSS vars para consistência entre light/dark.
- Não usar jQuery: toda interação deve ser Angular (bindings / services / RxJS).

### Extensão Futura
- Criar `KpiCardComponent` para métricas com comparação (ex: variação mês anterior).
- Criar `ThemePickerComponent` para seleção visual de paletas adicionais.
- Internacionalização de labels (Angular i18n ou ngx-translate).

---

## 📐 Responsividade & Sidebar
- Sidebar fixa: largura 250px (desktop) e colapsa para 64px.
- Wrapper ajusta margens automaticamente via classes (`collapsed`).
- Componentes (`SmallBox`, `InfoBox`) utilizam grid responsivo `repeat(auto-fit, minmax(220px, 1fr))`.
- Em telas < 768px as colunas tornam-se 100%.

Para garantir que novos componentes se adaptem:
```scss
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}
@media (max-width: 768px) { .dashboard-grid { grid-template-columns: 1fr; } }
```

---

## 🛠️ Extensão dos Temas
- Adicionar novo tema: incluir objeto em `themes` no `ThemeService`.
- Nome deve ser único e seguir tipo `AppTheme` se ampliado.
- Variáveis mínimas recomendadas: `--app-bg`, `--card-bg`, `--text-*`, `--border-color`, `--hover-bg`, paleta `--color-*`.

---

## ✅ Checklist ao Criar Novo Componente UI
- Definir inputs claros (sem lógica de formatação escondida).
- Usar `ChangeDetectionStrategy.OnPush` para performance.
- Aplicar cores via CSS vars (nunca valores fixos hardcoded se já existir uma var equivalente).
- Testar em tema claro e escuro.
- Validar responsividade (desktop, mobile, sidebar colapsada).
- Documentar no `DEVELOPMENT.md` se for componente reutilizável.

cd wk-admin-frontend
npm install
ng serve --port=4200  # http://localhost:4200

# Vue Customer App
cd wk-customer-app
npm install
npm run dev          # http://localhost:3002
```

### Python AI Service
```bash
cd wk-ai-service
pip install -r requirements.txt
uvicorn main:app --reload --port=8080  # http://localhost:8080
```

## 🐳 Docker Development

### Full Stack
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop all services
docker-compose down

# Rebuild specific service
docker-compose build wk-gateway
docker-compose up -d wk-gateway
```

### Individual Services
```bash
# Database only
docker-compose up -d postgres redis

# Backend services only
docker-compose up -d wk-gateway wk-crm-laravel wk-products-api

# Frontend services only
docker-compose up -d wk-admin-frontend wk-customer-app
```

## 🔧 VS Code Configuration

### Recommended Extensions
- ✅ C# DevKit (Installed)
- ✅ TypeScript Importer (Installed)  
- ✅ Python (Installed)
- ✅ Angular Language Service (Installed)
- ✅ Vue - Official (Installed)
- 📦 PHP Intelephense
- 📦 Laravel Extension Pack
- 📦 Docker
- 📦 GitLens

### Workspace Settings
```json
{
  "folders": [
    { "name": "🏗️ Infrastructure", "path": "./infrastructure" },
    { "name": "🟡 CRM Laravel", "path": "./wk-crm-laravel" },
    { "name": "🔵 CRM .NET", "path": "./wk-crm-dotnet" },
    { "name": "🟢 Gateway", "path": "./wk-gateway" },
    { "name": "🟢 Products API", "path": "./wk-products-api" },
    { "name": "🔴 Admin Frontend", "path": "./wk-admin-frontend" },
    { "name": "🟢 Customer App", "path": "./wk-customer-app" },
    { "name": "🐍 AI Service", "path": "./wk-ai-service" }
  ]
}
```

## 🧪 Testing Strategy

### Unit Testing
```bash
# Laravel
cd wk-crm-laravel && php artisan test

# .NET
cd wk-crm-dotnet && dotnet test

# Node.js Gateway
cd wk-gateway && npm test

# Node.js Products
cd wk-products-api && npm test

# Angular
cd wk-admin-frontend && ng test

# Vue
cd wk-customer-app && npm run test:unit

# Python
cd wk-ai-service && pytest
```

### Integration Testing
```bash
# Start test environment
docker-compose -f docker-compose.test.yml up -d

# Run integration tests
npm run test:integration
```

## 🔍 Debugging

### VS Code Debug Configuration
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Laravel API",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}/wk-crm-laravel"
      }
    },
    {
      "name": ".NET API",
      "type": "coreclr",
      "request": "launch",
      "program": "${workspaceFolder}/wk-crm-dotnet/src/WebApi/bin/Debug/net8.0/WebApi.dll",
      "args": [],
      "cwd": "${workspaceFolder}/wk-crm-dotnet/src/WebApi"
    }
  ]
}
```

## 📊 Monitoring & Health

### Health Check Endpoints
```bash
# Check all services
curl http://localhost:3000/health    # Gateway
curl http://localhost:8000/health    # Laravel CRM
curl http://localhost:5000/health    # .NET CRM
curl http://localhost:3001/health    # Products API
curl http://localhost:8080/health    # AI Service
```

### Docker Service Status
```bash
# Check running containers
docker-compose ps

# View service logs
docker-compose logs wk-gateway
docker-compose logs wk-crm-laravel

# Monitor resources
docker stats
```

## 🚀 Deployment

### Local Development
```bash
# Development mode
docker-compose up -d

# Development with file watching
docker-compose -f docker-compose.dev.yml up -d
```

### Production Build
```bash
# Build production images
docker-compose -f docker-compose.prod.yml build

# Deploy to production
docker-compose -f docker-compose.prod.yml up -d
```

## 🔧 Common Issues & Solutions

### Docker Issues
```bash
# Clean Docker cache
docker system prune -a

# Rebuild without cache
docker-compose build --no-cache

# Reset volumes
docker-compose down -v
docker-compose up -d
```

### Port Conflicts
```bash
# Check port usage
netstat -an | find "3000"
netstat -an | find "5432"

# Kill process on port
npx kill-port 3000
```

### Database Issues
```bash
# Reset database
docker-compose down postgres
docker volume rm wk-crm_postgres_data
docker-compose up -d postgres

# Laravel migrations
cd wk-crm-laravel
php artisan migrate:fresh --seed
```

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [.NET Documentation](https://docs.microsoft.com/dotnet)
- [Angular Documentation](https://angular.io/docs)
- [Vue.js Documentation](https://vuejs.org/guide)
- [FastAPI Documentation](https://fastapi.tiangolo.com)
- [Docker Documentation](https://docs.docker.com)

---

**Happy Coding! 🚀**

---
## Sidebar Flex Shell Migration
Para eliminar a sobreposição do dashboard sob o menu lateral, migramos de `mat-sidenav-container` para uma abordagem flex pura.

Estrutura:
```html
<div class="app-shell" [class.collapsed]="!sidenavOpened" [class.mobile]="isMobile" [class.mobile-open]="isMobile && sidenavOpened">
  <aside class="sidebar"> ... </aside>
  <main class="main-area"> ... </main>
</div>
```

Principais pontos:
- Sidebar fixa 250px em desktop; futura opção de colapso para 64px.
- Mobile overlay usa translateX (sem empurrar conteúdo) e classe `mobile-open`.
- Conteúdo não depende mais de `margin-left` ou transforms internas.
- Removidos hacks `body.sidebar-open` / `body.sidebar-collapsed`.
- Classe legado `.shifted` removida.
- Método `applySidebarBodyClass()` vazio, preservado apenas até refactor final.

Benefícios:
- Layout previsível e sem flash.
- Facilita ajuste de largura/temas sem conflito com Angular Material.
- Código de estilo mais simples e rastreável.

Checklist pós-migração:
- Verificar que `.app-shell` aparece no DOM.
- Confirmar que `.sidebar` tem largura correta em desktop.
- Inspecionar ausência de margin-left artificiais no `.main-area`.
- Testar toggle em mobile: abrir/fechar sem deslocar conteúdo.

Próximos aprimoramentos sugeridos:
- Ocultar texto em modo colapsado (mostrar apenas ícones).
- Animação suave para transição de largura (width + opacity labels).
- Preferir CSS container queries para refinamentos de layout.