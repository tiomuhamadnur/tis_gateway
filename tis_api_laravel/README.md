# TIS Gateway - Backend API & Dashboard

A monolithic Laravel 12 application for receiving, storing, and managing failure records data from TIS Gateway (Python). Equipped with an interactive dashboard, user management CMS, and API endpoints for external integration.

## 🎯 Key Features

- **API Endpoints** - RESTful API for submitting failure records and files
- **Dashboard** - Real-time analytics and statistics with Highcharts
- **User Management** - CRUD users with role-based access control (RBAC) using Spatie Permission
- **Data Tables** - Interactive data tables with Yajra DataTables
- **Export** - Export data to Excel and PDF
- **Authentication** - Bearer token authentication for API, session-based for web
- **Docker Support** - Ready for deployment with Docker Compose

## 📋 Tech Stack

- **Backend:** Laravel 12, PHP 8.2
- **Database:** MySQL
- **Frontend:** Livewire, Alpine.js, Tailwind CSS
- **Charts:** Highcharts
- **Tables:** Yajra DataTables, jQuery
- **Export:** Laravel Excel, DomPDF
- **Testing:** Pest PHP
- **Deployment:** Docker, Docker Compose

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+ or SQLite
- Docker & Docker Compose (for production)

### Development Setup

```bash
# Clone repository
cd tis_gateway

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database
php artisan migrate

# Seed test data
php artisan db:seed

# Build frontend assets
npm run build

# Start development server
php artisan serve

# In another terminal, start Vite dev server
npm run dev
```

The application will run at `http://localhost:8000`

### Login Credentials

```
Admin User:
- Email: admin@tisgateway.com
- Password: password
- Role: Admin

Operator User:
- Email: operator@tisgateway.com  
- Password: password
- Role: Operator

Viewer User:
- Email: viewer@tisgateway.com
- Password: password
- Role: Viewer
```

## 🐳 Docker Deployment

```bash
# Build and start containers
docker-compose up -d

# Run migrations in container
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed

# View logs
docker-compose logs -f app

# Access application
# http://localhost:8000
```

### Environment Configuration

Edit `.env` for configuration:

```env
APP_ENV=development/production
APP_DEBUG=true/false
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=tis_gateway
DB_USERNAME=user
DB_PASSWORD=password
TIS_API_KEY=your_secret_api_key
```

## 📡 API Endpoints

All endpoints require `Authorization: Bearer {TIS_API_KEY}` header.

### Submit Failure Records

```
POST /api/failures
Content-Type: application/json
Authorization: Bearer {TIS_API_KEY}

{
  "rake_id": "RAKE-001",
  "records": [
    {
      "timestamp": "2024-05-08 10:30:00",
      "equipment_name": "Engine",
      "fault_name": "Overheating",
      "classification": "heavy",
      "description": "Engine temperature exceeded safe limit"
    }
  ]
}
```

### Upload File

```
POST /api/files
Content-Type: multipart/form-data
Authorization: Bearer {TIS_API_KEY}

Form Data:
- rake_id: RAKE-001
- file: [CSV or PDF file]
```

### List Failure Sessions

```
GET /api/failures?rake_id=RAKE-001&page=1&per_page=15
Authorization: Bearer {TIS_API_KEY}
```

### Get Session Details

```
GET /api/failures/{session_id}
Authorization: Bearer {TIS_API_KEY}
```

### Dashboard Statistics

```
GET /api/dashboard
Authorization: Bearer {TIS_API_KEY}
```

### Analytics Trend

```
GET /api/analytics/trend?from=2024-05-01&to=2024-05-31&group_by=day
Authorization: Bearer {TIS_API_KEY}
```

### Pareto Chart Data

```
GET /api/analytics/pareto?start_date=2024-05-01&end_date=2024-05-31
Authorization: Bearer {TIS_API_KEY}
```

### Health Check

```
GET /api/health
Authorization: Bearer {TIS_API_KEY}
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Api/FailureApiTest.php

# Run with coverage
php artisan test --coverage

# Run tests in watch mode
php artisan test --watch
```

Test Coverage:
- ✅ 7 API endpoint tests passing
- ✅ Feature tests for Livewire components
- ✅ Authentication & authorization tests

## 📊 Database Schema

### Tables

- **users** - User accounts with encryption
- **roles** - Spatie roles for RBAC
- **permissions** - Spatie permissions
- **failure_sessions** - Session data from TIS Gateway
- **failure_records** - Individual failure records
- **uploaded_files** - Metadata of uploaded files
- **rakes** - Master data for trains

## 🔐 Security Features

- ✅ API Key authentication for external endpoints
- ✅ Session-based authentication for web UI
- ✅ Role-Based Access Control (RBAC)
- ✅ Password hashing with bcrypt
- ✅ CSRF protection
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade escaping)

## 📁 Project Structure

```
tis_gateway/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # API endpoints
│   │   │   └── Web/          # Web controllers
│   │   └── Middleware/       # Custom middleware
│   ├── Models/               # Eloquent models
│   └── Exports/              # Export classes
├── config/                   # Configuration files
├── database/
│   ├── migrations/           # Database migrations
│   ├── seeders/              # Database seeders
│   └── factories/            # Model factories
├── resources/
│   ├── views/                # Blade templates
│   │   ├── components/       # Livewire components
│   │   └── layouts/          # Layout templates
│   ├── css/                  # Tailwind CSS
│   └── js/                   # JavaScript/Alpine
├── routes/
│   ├── api.php               # API routes
│   ├── web.php               # Web routes
│   └── auth.php              # Auth routes
├── storage/                  # File uploads
├── tests/
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
├── Dockerfile                # Docker configuration
├── docker-compose.yml        # Docker Compose
└── README.md                 # This file
```

## 🎨 UI Components

### Livewire Components

- `Dashboard` - Main analytics dashboard dengan statistics
- `UserManagement` - CRUD untuk user management
- `FailureTable` - Data table untuk failure records

### Frontend Stack

- **Tailwind CSS** - Utility-first CSS framework
- **Alpine.js** - Lightweight JavaScript framework
- **Livewire** - Full-stack reactive framework
- **Highcharts** - Interactive charts library
- **DataTables** - Advanced table plugin

## 🔄 Workflow

### Submit Data Flow

```
TIS Gateway (Python)
    ↓
POST /api/failures
    ↓
Validate API Key
    ↓
Store in Database
    ↓
Response: session_id + status
    ↓
Dashboard Updates
```

### Web Flow

```
User Login
    ↓
Dashboard (Analytics)
    ↓
User Management / Failure Records / Export
    ↓
Role-based Access Control
```

## 📈 Performance

- Database indexing pada frequently queried fields
- Eager loading untuk relationships
- Pagination untuk large datasets
- Asset minification dengan Vite
- Database query optimization

## 🚨 Common Issues & Solutions

### Port 8000 already in use
```bash
php artisan serve --port=8001
```

### Database migration error
```bash
php artisan migrate:fresh --seed  # Reset and seed
```

### Asset not loading
```bash
npm run build  # Rebuild assets
```

### Docker permission error
```bash
sudo chown -R $USER:$USER .
```

## 📝 Logging & Monitoring

- Logs: `storage/logs/laravel.log`
- Configure via `config/logging.php`
- Environment: `LOG_LEVEL` di `.env`

## 🔗 Useful Commands

```bash
# Generate API documentation
php artisan scribe:generate

# Clear cache
php artisan cache:clear
php artisan config:clear

# Database commands
php artisan migrate:rollback
php artisan seed:refresh

# Generate models & migrations
php artisan make:model ModelName -m

# View routes
php artisan route:list
```

## 📞 Support & Issues

- Jika menemukan bugs, buat issue di repository
- Untuk feature requests, diskusikan terlebih dahulu
- Dokumentasi: Lihat `BLUEPRINT.md` untuk spesifikasi detail

## 📄 License

Proprietary - PT Terusan Inovasi Solusi (TIS)

## 👥 Team

- **Backend:** Laravel/PHP Developer
- **Frontend:** Livewire/Alpine Developer  
- **DevOps:** Docker/Infrastructure

---

**Last Updated:** May 8, 2026  
**Version:** 1.0.0-beta  
**Status:** In Active Development
