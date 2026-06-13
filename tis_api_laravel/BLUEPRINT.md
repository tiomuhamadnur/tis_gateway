# API Blueprint - TIS Gateway Backend

This document outlines the blueprint for backend API development of **tis_gateway**, which will serve as a data consumer for TIS Gateway (Python). This implementation will use Laravel 12 framework, equipped with Yajra DataTable for data presentation, Livewire for interactive UI, Highchart for data visualization, and export features for Excel and PDF.

**Latest Update:** The application is built as a monolith with FE and BE in a single Laravel application. There are development and production modes. FE Reference: https://tweakcn.com/themes/cmluqysmw000204ji34jja0ul (shadcn/ui theme for modern UI components).

## 1. General Architecture

The backend API will receive failure data and report files from TIS Gateway via HTTP POST requests. Data will be stored in a MySQL database, and files will be stored in the server storage system. The frontend will be built using Livewire for dashboard, data presentation, and CMS.

**Monolith Architecture:** FE and BE are integrated in a single Laravel application. Frontend uses Blade templates with Livewire for interactivity, without a separate API for FE.

```
TIS Gateway (Python)
        │
        │  HTTP POST (JSON / multipart)
        │  Authorization: Bearer {TIS_API_KEY}
        ▼
Monolith App (Laravel 12)
        │
        ├── Database (MySQL)
        ├── File Storage (CSV / PDF)
        ├── API Endpoints (untuk external consumers)
        └── UI / Dashboard + CMS (Livewire, Yajra Datatables, Highcharts, shadcn/ui inspired)
```

### 1.1. Development & Production Mode

- **Development Mode:** Uses local environment, with debugging enabled, hot reload for assets, and local database.
- **Production Mode:** Optimized for performance, with caching, minified assets, and production database configuration.

Mode configuration through `.env` file (`APP_ENV=local` for dev, `APP_ENV=production` for prod).

## 2. Technologies Used

*   **Application Name:** tis_gateway
*   **Framework:** Laravel 12 (PHP)
*   **Database:** MySQL
*   **Interactive Frontend:** Livewire + Alpine.js
*   **UI Components:** shadcn/ui inspired (Tailwind CSS based, modern design)
*   **User & Role Management:** Spatie Laravel Permission
*   **Data Table:** Yajra Datatables (integrated with Livewire)
*   **Chart/Graphics:** Highcharts
*   **Data Export:** Laravel Excel (for Excel) and DomPDF / Laravel PDF (for PDF)
*   **Asset Management:** Vite for bundling and hot reload
*   **Styling:** Tailwind CSS with custom components from theme reference

## 3. Authentication

All requests from TIS Gateway will be authenticated using **Bearer Token** in the `Authorization` header. This token will be validated by Laravel Middleware.

```
Authorization: Bearer {TIS_API_KEY}
```

Implementation:
*   Laravel Middleware for API key authentication.
*   API Key will be stored in the server backend `.env` file.

## 4. CMS (Content Management System) & User Management

The **tis_gateway** application will be equipped with a simple CMS for user and role management.

### 4.1. Role-Based Access Control (RBAC)

*   Uses the **Spatie Laravel Permission** package to manage user roles and permissions.
*   Admins can create, edit, and delete roles and permissions.
*   Each user will have a role (e.g., Admin, Operator, Viewer) that determines access to application features.

### 4.2. User Management (CRUD)

*   Users with roles that have appropriate permissions can manage the user list (CRUD).
*   User registration/editing form will include name, email, password, and role assignment.

## 5. API Endpoints

The API will implement endpoints as defined in the `README.md` "Backend API Specification" section, as well as endpoints for internal CRUD operations.

### 5.1. `POST /v1/failures` — Receive Failure Records

*   **Description:** Receives failure records data in JSON format.
*   **Authentication:** Bearer Token.
*   **Request Body:** As per `README.md`.
*   **Response:** `201 Created` with `session_id`, `received`, `status`.

### 5.2. `POST /v1/files` — Receive File Upload (CSV / PDF)

*   **Description:** Receives CSV or PDF file uploads.
*   **Authentication:** Bearer Token.
*   **Request Fields:** `rake_id`, `file` (multipart/form-data).
*   **Response:** `201 Created` with `file_id`, `filename`, `status`.

### 5.3. `GET /v1/failures` — List Download Sessions & CRUD

*   **Description:** Displays list of download sessions with filtering and pagination. CRUD functions for this data available via web interface.
*   **Query Parameters:** `rake_id`, `from`, `to`, `page`, `per_page`.
*   **Response:** `200 OK` with session data and pagination metadata.
*   **Frontend Implementation:** Will use Livewire and Yajra Datatables for efficient and interactive data table presentation. Each data row in the table will have options for "View Detail", "Edit", and "Delete" (according to role permissions).

### 5.4. `GET /v1/failures/{session_id}` — Session Detail + Records

*   **Description:** Displays detail of one download session along with related failure records.
*   **Response:** `200 OK` with session detail and records array.
*   **Frontend Implementation:** Livewire component to display detail, with ability to edit or delete individual records (if allowed by permissions).

### 5.5. `GET /v1/dashboard` — Statistics Summary

*   **Description:** Provides aggregate data for dashboard.
*   **Response:** `200 OK` with total sessions, total records, data per train, per equipment, per classification, and recent heavy faults.
*   **Frontend Implementation:** Livewire component that integrates Highcharts for data visualization.

### 5.6. `GET /v1/analytics/trend` — Fault Trend per Period

*   **Description:** Provides fault trend data based on period.
*   **Query Parameters:** `rake_id`, `from`, `to`, `group_by` (day / week / month).
*   **Response:** `200 OK` with trend data.
*   **Frontend Implementation:** Highcharts for displaying trend graph.

### 5.7. `GET /v1/analytics/pareto` — Pareto Chart Faults

*   **Description:** Endpoint to get data for building Pareto Chart.
*   **Query Parameters:**
    *   `start_date`, `end_date`: Filter by date range.
    *   `start_time`, `end_time`: Filter by time range within a day.
    *   `failure_type`: Filter by failure type (e.g. `equipment_name` or `fault_name`).
    *   `rake_id`: Filter by train ID.
*   **Response:** `200 OK` with fault frequency data and cumulative percentage needed for Highcharts Pareto.
*   **Frontend Implementation:** Livewire component that integrates Highcharts to display interactive Pareto Chart with dynamic filtering.

### 5.8. `GET /v1/health` — Health Check

*   **Description:** Simple endpoint to check API status.
*   **Response:** `200 OK` with `status: "ok"` and `version`.

## 6. Database Structure (MySQL)

The table structure will follow the specification in `README.md`, with adjustments for Laravel Eloquent ORM.
Additional tables for user management and roles/permissions from Spatie.

*   **`users` Table:** To store user information (name, email, password, etc).
*   **`roles` Table:** From Spatie, to define roles.
*   **`permissions` Table:** From Spatie, to define permissions.
*   **`model_has_roles`, `role_has_permissions` Table:** Pivot tables from Spatie.
*   **`sessions` Table:** Will be used to store session information.
*   **`failure_records` Table:** Will store each failure record.
*   **`uploaded_files` Table:** Will store metadata of uploaded CSV/PDF files.
*   **`rakes` Table:** Master data table for trains.

Relationships between tables will be managed using Eloquent Relationships.

## 7. Validation

All inputs received by the API and CMS forms will be strictly validated using Laravel Validation rules, according to the specification in `README.md` and CMS requirements.

## 8. Data Export (Frontend/Reporting)

For export features from dashboard/report views:

*   **Export Excel:** Will be implemented using Laravel Excel package. Users can select data to export from table or report views.
*   **Export PDF:** Will be implemented using DomPDF or other Laravel PDF package. This will be used to generate PDF reports similar to TIS Gateway Python output.

## 9. Development Workflow

*   **Migrations:** Laravel Migrations for database schema management (including Spatie tables).
*   **Seeder:** Laravel Seeders for master data such as `rakes`, `equipment_map`, and default user/role/permission data.
*   **Tests:** Unit and Feature tests using PHPUnit.
*   **Code Quality:** Use tools like `PHP_CodeSniffer` or `Laravel Pint` to maintain code quality and styling consistency.
*   **Deployment:** Docker or Nginx/Apache + PHP-FPM.

### 9.1. Deployment with Docker Container

The application will be distributed using Docker Container for ease of deployment and environment consistency.

*   **Dockerfile:** A `Dockerfile` will be created to build the Laravel application image. This will include PHP, Nginx (or Apache), Composer dependencies, and required configuration installation.
*   **Docker Compose:** A `docker-compose.yml` file will be used to define and run multi-container Docker application, including:
    *   **`app` service:** For Laravel application (based on `Dockerfile`).
    *   **`nginx` service:** As web server (if not combined with PHP-FPM in `app` container).
    *   **`db` service:** For MySQL database.
    *   **`redis` service:** (Optional) For caching or queue.
*   **Configuration Management (.env):** All sensitive configuration and environment variables (such as database credentials, API keys, mail settings, etc.) will be stored in `.env` file. During Docker deployment, these variables will be injected into the container via environment variables, ensuring the application can be configured without rebuilding the image. Important `.env` variables example:
    *   `APP_NAME=tis_gateway`
    *   `APP_ENV=production`
    *   `APP_KEY=base64:your_app_key`
    *   `DB_CONNECTION=mysql`
    *   `DB_HOST=db` (nama service di docker-compose)
    *   `DB_PORT=3306`
    *   `DB_DATABASE=tis_gateway_db`
    *   `DB_USERNAME=user`
    *   `DB_PASSWORD=password`
    *   `TIS_API_KEY=your_secret_api_key`
*   **Persistent Storage:** Data that needs to be retained (such as database, uploaded files) will use Docker volumes to ensure data is not lost when containers are restarted or removed.

## 10. Progress & Tracking

This document will be updated periodically to track implementation progress, gaps, and next steps.

### 10.1. Completed Tasks

- [x] Setup Laravel project (existing)
- [x] Install dependencies (Spatie Permission, Yajra DataTable, Highcharts, Laravel Excel, DomPDF)
- [x] Setup database migrations untuk tabel: users, roles, permissions, failure_sessions, failure_records, uploaded_files, rakes
- [x] Publish configs untuk semua packages
- [x] Update User model dengan HasRoles trait
- [x] Create models: Session, FailureRecord, UploadedFile, Rake
- [x] Run migrations successfully
- [x] Implement API endpoints (POST /v1/failures, POST /v1/files, GET /v1/failures, dll.)
- [x] Setup authentication middleware untuk API key
- [x] Build CMS dengan Livewire untuk user management (CRUD users, roles, permissions)
- [x] Implement dashboard dengan Highcharts untuk statistik
- [x] Integrate Yajra DataTable untuk tabel data
- [x] Implement export Excel dan PDF
- [x] Setup seeders untuk test data (Rakes, Failures, Users, Roles)
- [x] Create comprehensive feature tests untuk API endpoints (7 tests passing)
- [x] Setup Vite untuk asset bundling (jQuery, DataTables, Highcharts)
- [x] Create Docker configuration (Dockerfile, docker-compose.yml)
- [x] Configure dev/prod environments
- [x] Setup UI dengan Tailwind CSS

### 10.2. In Progress

- Improve UI components dengan shadcn/ui inspired design
- Add API documentation (Laravel Scribe atau Swagger) dengan kemampuan export Postman JSON.
- Setup rate limiting untuk API endpoints

### 10.3. Gaps & Issues

- API documentation belum lengkap
- Highcharts integration dengan Livewire masih basic, perlu advanced charts
- Authorization policies untuk CMS belum fully implemented
- Email verification belum diimplementasikan untuk user registration
- File upload validation perlu enhancement
- Real-time updates dengan Laravel Echo belum setup
- Error handling perlu improvement untuk production
- Logging perlu optimization untuk monitoring

### 10.4. Next Steps (Priority Order)

1. Add API documentation (OpenAPI/Swagger) with Postman JSON export.
2. Setup Laravel Scribe for auto-generate docs.
3. Improve dashboard UI dengan advanced Highcharts.
4. Implement authorization policies untuk admin CMS.
5. Add rate limiting middleware untuk API.
6. Setup comprehensive error handling & logging.
7. Implement email verification untuk users.
8. Add more integration tests.
9. Setup GitHub Actions CI/CD pipeline.
10. Deploy to production dengan Docker Compose.

## Testing Results

- API Tests: 7/7 passing ✅
- Feature Tests: Basic setup complete
- Test Coverage: API endpoints, basic Livewire components
- Ready for: Development & UAT

## Development Instructions

### Local Development

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Start dev server
php artisan serve
npm run dev

# Run tests
php artisan test
```

### Docker Deployment

```bash
# Build and run
docker-compose up -d

# Setup database in container
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# Access app
http://localhost:8000
```

### Credentials (Development)

- Admin: admin@tisgateway.com / password
- Operator: operator@tisgateway.com / password
- Viewer: viewer@tisgateway.com / password
- API Key: set TIS_API_KEY in .env

## 11. API Documentation

Untuk memudahkan konsumsi API oleh pihak eksternal, dokumentasi API akan disediakan dengan fitur-fitur berikut:

*   **Laravel Scribe:** Akan digunakan untuk secara otomatis menghasilkan dokumentasi API berdasarkan route dan controller. Ini akan memastikan dokumentasi selalu up-to-date dengan kode.
*   **Postman Collection Export:** Dokumentasi yang dihasilkan akan mencakup opsi untuk mengunduh koleksi Postman dalam format JSON. Ini memungkinkan *developer* untuk dengan cepat mengimpor semua endpoint API ke Postman dan mulai menguji tanpa konfigurasi manual.
*   **OpenAPI/Swagger:** Dokumentasi juga akan tersedia dalam format OpenAPI/Swagger untuk integrasi dengan *tools* lain.
*   **Akses:** Dokumentasi akan di-*host* di endpoint yang spesifik (misalnya `/docs` atau `/api/documentation`) dan mungkin memerlukan otentikasi (misalnya role Admin) untuk akses.


