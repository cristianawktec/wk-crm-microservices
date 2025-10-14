# 🚀 WK CRM Microservices Platform

Enterprise-grade Customer Relationship Management system built with modern microservices architecture.

## 🏗️ Architecture Overview

```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│   Admin Panel   │  │  Customer App   │  │   AI Service    │
│   (Angular 18)  │  │    (Vue 3)      │  │ (Python/FastAPI)│
└─────────────────┘  └─────────────────┘  └─────────────────┘
         │                     │                     │
         └─────────────────────┼─────────────────────┘
                               │
                    ┌─────────────────┐
                    │   API Gateway   │
                    │  (Node.js/TS)   │
                    └─────────────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
    ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
    │   CRM Laravel   │ │   CRM .NET 8    │ │  Products API   │
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

## 🛠️ Tech Stack

### Backend Services
- **🎯 API Gateway**: Node.js + TypeScript + Express
- **🏢 CRM Laravel**: Laravel 11 + DDD + PostgreSQL
- **🏢 CRM .NET**: .NET 8 + Clean Architecture + Entity Framework
- **📦 Products API**: Node.js + Express + PostgreSQL
- **🤖 AI Service**: Python + FastAPI + Google Gemini

### Frontend Applications
- **👨‍💼 Admin Panel**: Angular 18 + Angular Material
- **👤 Customer App**: Vue 3 + Tailwind CSS

### Infrastructure
- **🐳 Containerization**: Docker + Docker Compose
- **🗄️ Database**: PostgreSQL 16
- **⚡ Cache**: Redis 7
- **🌐 Reverse Proxy**: Nginx
- **📊 Monitoring**: Built-in health checks

## 🚀 Quick Start

### Prerequisites
- Docker Desktop
- Node.js 20+
- .NET 8 SDK
- PHP 8.3+
- Python 3.11+

### 1. Clone and Setup
```bash
git clone <repository-url>
cd wk-crm-microservices
cp .env.example .env
```

### 2. Configure Environment
Edit `.env` file with your configuration:
```env
# Database
POSTGRES_DB=wk_main
POSTGRES_USER=wk_user
POSTGRES_PASSWORD=your_secure_password

# Redis
REDIS_PASSWORD=your_redis_password

# API Keys
GEMINI_API_KEY=your_gemini_api_key
```

### 3. Start All Services
```bash
# Start all services
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f
```

### 4. Access Applications
- **🌐 Main Gateway**: http://localhost:3000
- **👨‍💼 Admin Panel**: http://localhost:4200
- **👤 Customer App**: http://localhost:3002
- **🗄️ Database**: localhost:5432
- **⚡ Redis**: localhost:6379

## 📋 Development

### Laravel CRM API (Primary)
```bash
cd wk-crm-laravel
composer install
php artisan migrate
php artisan serve --port=8000
```

### .NET CRM API (Demo)
```bash
cd wk-crm-dotnet
dotnet restore
dotnet ef database update
dotnet run --urls=http://localhost:5000
```

### Node.js Services
```bash
# API Gateway
cd wk-gateway
npm install
npm run dev

# Products API
cd wk-products-api
npm install
npm run dev
```

### Python AI Service
```bash
cd wk-ai-service
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```

### Frontend Applications
```bash
# Angular Admin
cd wk-admin-frontend
npm install
ng serve --port 4200

# Vue Customer App
cd wk-customer-app
npm install
npm run dev
```

## 🏗️ Project Structure

```
wk-crm-microservices/
├── 🔧 infrastructure/           # Docker, Nginx, deployment
├── 📦 wk-crm-laravel/          # Laravel 11 + DDD
│   ├── app/Domain/             # Domain entities & services
│   ├── app/Application/        # Use cases & DTOs
│   └── app/Infrastructure/     # Repositories & external services
├── 📦 wk-crm-dotnet/           # .NET 8 + Clean Architecture
│   ├── src/Domain/             # Domain entities
│   ├── src/Application/        # CQRS + MediatR
│   ├── src/Infrastructure/     # Data access & external services
│   └── src/WebApi/             # REST API controllers
├── 📦 wk-products-api/         # Node.js + Express
├── 📦 wk-gateway/              # API Gateway + Auth
├── 🎨 wk-admin-frontend/       # Angular 18 admin panel
├── 🎨 wk-customer-app/         # Vue 3 customer portal
├── 🤖 wk-ai-service/           # Python + FastAPI + AI
└── 📚 docs/                    # Documentation
```

## 🔒 Security Features

- JWT Authentication & Authorization
- Role-based access control (RBAC)
- API rate limiting
- Request validation
- CORS protection
- Helmet security headers
- Database query protection
- Redis session management

## 📊 Development Principles

### Domain-Driven Design (DDD)
- **Entities**: Core business objects
- **Value Objects**: Immutable data containers
- **Aggregates**: Consistency boundaries
- **Repositories**: Data access abstraction
- **Services**: Business logic coordination

### Clean Architecture (.NET)
- **Domain Layer**: Business entities & rules
- **Application Layer**: Use cases & interfaces
- **Infrastructure Layer**: Data & external services
- **Presentation Layer**: REST API controllers

### SOLID Principles
- Single Responsibility Principle
- Open/Closed Principle
- Liskov Substitution Principle
- Interface Segregation Principle
- Dependency Inversion Principle

## 🧪 Testing

### Unit Tests
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

### Integration Tests
```bash
# Run all services
docker-compose up -d

# Run integration test suite
npm run test:integration
```

## 📈 Monitoring & Health Checks

### Health Endpoints
- **Gateway**: http://localhost:3000/health
- **CRM Laravel**: http://localhost:8000/health
- **CRM .NET**: http://localhost:5000/health
- **Products API**: http://localhost:3001/health
- **AI Service**: http://localhost:8080/health

### Logging
All services implement structured logging with Winston (Node.js), Serilog (.NET), and Python logging.

## 🚀 Deployment

### Docker Production
```bash
# Build all images
docker-compose -f docker-compose.prod.yml build

# Deploy to production
docker-compose -f docker-compose.prod.yml up -d
```

### Environment-specific Configs
- `docker-compose.yml` - Development
- `docker-compose.prod.yml` - Production
- `docker-compose.test.yml` - Testing

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Cristian MS**  
- Email: cristianms.awk@gmail.com
- GitHub: [@cristianawktec](https://github.com/cristianawktec)

## 🙏 Acknowledgments

- Laravel Framework
- .NET Foundation
- Vue.js Team
- Angular Team
- FastAPI
- Docker Community

---

**Built with ❤️ using modern microservices architecture and best practices.**