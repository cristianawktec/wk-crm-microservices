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