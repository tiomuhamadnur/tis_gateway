# API Blueprint - TIS Gateway Backend

Dokumen ini menjelaskan cetak biru (blueprint) untuk pengembangan backend API **tis_gateway**, yang akan berfungsi sebagai *consumer* data dari TIS Gateway (Python). Implementasi ini akan menggunakan framework Laravel 12, dilengkapi dengan Yajra DataTable untuk tampilan data, Livewire untuk UI interaktif, Highchart untuk visualisasi data, dan fitur export ke Excel serta PDF.

**Update Terbaru:** Aplikasi dibangun sebagai monolith dengan FE dan BE dalam satu aplikasi Laravel. Ada mode development dan production. Referensi FE: https://tweakcn.com/themes/cmluqysmw000204ji34jja0ul (tema shadcn/ui untuk UI components modern).

## 1. Arsitektur Umum

Backend API akan menerima data failure dan file report dari TIS Gateway melalui HTTP POST requests. Data akan disimpan dalam database MySQL, dan file akan disimpan di sistem penyimpanan server. Frontend akan dibangun menggunakan Livewire untuk dashboard, tampilan data, dan CMS.

**Monolith Architecture:** FE dan BE terintegrasi dalam satu aplikasi Laravel. Frontend menggunakan Blade templates dengan Livewire untuk interaktivitas, tanpa API terpisah untuk FE.

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

### 1.1. Mode Development & Production

- **Development Mode:** Menggunakan local environment, dengan debugging enabled, hot reload untuk assets, dan database lokal.
- **Production Mode:** Optimized untuk performance, dengan caching, minified assets, dan konfigurasi production database.

Konfigurasi mode melalui `.env` file (`APP_ENV=local` untuk dev, `APP_ENV=production` untuk prod).

## 2. Teknologi yang Digunakan

*   **Nama Aplikasi:** tis_gateway
*   **Framework:** Laravel 12 (PHP)
*   **Database:** MySQL
*   **Frontend Interaktif:** Livewire + Alpine.js
*   **UI Components:** shadcn/ui inspired (Tailwind CSS based, modern design)
*   **Manajemen User & Role:** Spatie Laravel Permission
*   **Tabel Data:** Yajra Datatables (terintegrasi dengan Livewire)
*   **Chart/Grafik:** Highcharts
*   **Export Data:** Laravel Excel (untuk Excel) dan DomPDF / Laravel PDF (untuk PDF)
*   **Asset Management:** Vite untuk bundling dan hot reload
*   **Styling:** Tailwind CSS dengan custom components dari referensi tema

## 3. Autentikasi

Semua request dari TIS Gateway akan diautentikasi menggunakan **Bearer Token** di header `Authorization`. Token ini akan divalidasi oleh Laravel Middleware.

```
Authorization: Bearer {TIS_API_KEY}
```

Implementasi:
*   Laravel Middleware untuk otentikasi API key.
*   API Key akan disimpan di `.env` file server backend.

## 4. CMS (Content Management System) & User Management

Aplikasi `tis_gateway` akan dilengkapi dengan CMS sederhana untuk manajemen pengguna dan role.

### 4.1. Role-Based Access Control (RBAC)

*   Menggunakan paket **Spatie Laravel Permission** untuk mengelola role dan permission pengguna.
*   Admin dapat membuat, mengedit, dan menghapus role serta permission.
*   Setiap user akan memiliki role (misalnya: Admin, Operator, Viewer) yang menentukan akses ke fitur-fitur aplikasi.

### 4.2. Manajemen Pengguna (CRUD)

*   User dengan role yang memiliki permission yang sesuai dapat mengelola daftar pengguna (CRUD).
*   Form pendaftaran/pengeditan pengguna akan mencakup nama, email, password, dan penentuan role.

## 5. Endpoints API

API akan mengimplementasikan endpoints yang telah didefinisikan dalam `README.md` bagian "Backend API Specification", serta endpoints untuk CRUD data internal.

### 5.1. `POST /v1/failures` — Terima Failure Records

*   **Deskripsi:** Menerima data failure records dalam format JSON.
*   **Otentikasi:** Bearer Token.
*   **Request Body:** Sesuai `README.md`.
*   **Response:** `201 Created` dengan `session_id`, `received`, `status`.

### 5.2. `POST /v1/files` — Terima Upload File (CSV / PDF)

*   **Deskripsi:** Menerima upload file CSV atau PDF.
*   **Otentikasi:** Bearer Token.
*   **Request Fields:** `rake_id`, `file` (multipart/form-data).
*   **Response:** `201 Created` dengan `file_id`, `filename`, `status`.

### 5.3. `GET /v1/failures` — List Sesi Download & CRUD

*   **Deskripsi:** Menampilkan daftar sesi download dengan filter dan pagination. Tersedia fungsi CRUD untuk data ini melalui antarmuka web.
*   **Query Parameters:** `rake_id`, `from`, `to`, `page`, `per_page`.
*   **Response:** `200 OK` dengan data sesi dan metadata pagination.
*   **Implementasi Frontend:** Akan menggunakan Livewire dan Yajra Datatables untuk penyajian tabel data yang efisien dan interaktif. Setiap baris data di tabel akan memiliki opsi untuk "View Detail", "Edit", dan "Delete" (sesuai permission role).

### 5.4. `GET /v1/failures/{session_id}` — Detail Sesi + Records

*   **Deskripsi:** Menampilkan detail satu sesi download beserta failure records yang terkait.
*   **Response:** `200 OK` dengan detail sesi dan array records.
*   **Implementasi Frontend:** Livewire component untuk menampilkan detail, dengan kemampuan untuk mengedit atau menghapus record individual (jika diizinkan oleh permission).

### 5.5. `GET /v1/dashboard` — Ringkasan Statistik

*   **Deskripsi:** Menyediakan data agregat untuk dashboard.
*   **Response:** `200 OK` dengan total sesi, total records, data per kereta, per equipment, per klasifikasi, dan recent heavy faults.
*   **Implementasi Frontend:** Livewire component yang mengintegrasikan Highcharts untuk visualisasi data.

### 5.6. `GET /v1/analytics/trend` — Tren Fault per Periode

*   **Deskripsi:** Menyediakan data tren fault berdasarkan periode.
*   **Query Parameters:** `rake_id`, `from`, `to`, `group_by` (day / week / month).
*   **Response:** `200 OK` dengan data tren.
*   **Implementasi Frontend:** Highcharts untuk menampilkan grafik tren.

### 5.7. `GET /v1/analytics/pareto` — Pareto Chart Faults

*   **Deskripsi:** Endpoint untuk mendapatkan data yang digunakan untuk membangun Pareto Chart.
*   **Query Parameters:**
    *   `start_date`, `end_date`: Filter berdasarkan rentang tanggal.
    *   `start_time`, `end_time`: Filter berdasarkan rentang waktu dalam sehari.
    *   `failure_type`: Filter berdasarkan jenis failure (misalnya `equipment_name` atau `fault_name`).
    *   `rake_id`: Filter berdasarkan ID kereta.
*   **Response:** `200 OK` dengan data frekuensi fault dan persentase kumulatif yang diperlukan untuk Highcharts Pareto.
*   **Implementasi Frontend:** Livewire component yang mengintegrasikan Highcharts untuk menampilkan Pareto Chart interaktif dengan filter dinamis.

### 5.8. `GET /v1/health` — Health Check

*   **Deskripsi:** Endpoint sederhana untuk memeriksa status API.
*   **Response:** `200 OK` dengan `status: "ok"` dan `version`.

## 6. Struktur Database (MySQL)

Struktur tabel akan mengikuti spesifikasi di `README.md`, dengan penyesuaian untuk Laravel Eloquent ORM.
Tambahan tabel untuk user management dan role/permission dari Spatie.

*   **`users` Table:** Untuk menyimpan informasi pengguna (nama, email, password, dll).
*   **`roles` Table:** Dari Spatie, untuk mendefinisikan role.
*   **`permissions` Table:** Dari Spatie, untuk mendefinisikan permission.
*   **`model_has_roles`, `role_has_permissions` Table:** Tabel pivot dari Spatie.
*   **`sessions` Table:** Akan digunakan untuk menyimpan informasi sesi.
*   **`failure_records` Table:** Akan menyimpan setiap record failure.
*   **`uploaded_files` Table:** Akan menyimpan metadata file CSV/PDF yang diupload.
*   **`rakes` Table:** Tabel master data untuk kereta.

Relasi antar tabel akan dikelola menggunakan Eloquent Relationships.

## 7. Validasi

Semua input yang diterima oleh API dan form CMS akan divalidasi secara ketat menggunakan Laravel Validation rules, sesuai dengan spesifikasi di `README.md` dan kebutuhan CMS.

## 8. Export Data (Frontend/Reporting)

Untuk fitur export dari tampilan dashboard/report:

*   **Export Excel:** Akan diimplementasikan menggunakan paket Laravel Excel. Pengguna dapat memilih data yang ingin diekspor dari tampilan tabel atau laporan.
*   **Export PDF:** Akan diimplementasikan menggunakan DomPDF atau paket Laravel PDF lainnya. Ini akan digunakan untuk menghasilkan laporan PDF yang mirip dengan output dari TIS Gateway Python.

## 9. Development Workflow

*   **Migrations:** Laravel Migrations untuk pengelolaan skema database (termasuk tabel Spatie).
*   **Seeder:** Laravel Seeders untuk data master seperti `rakes`, `equipment_map`, dan data default user/role/permission.
*   **Tests:** Unit dan Feature tests menggunakan PHPUnit.
*   **Code Quality:** Penggunaan tool seperti `PHP_CodeSniffer` atau `Laravel Pint` untuk menjaga kualitas kode dan konsistensi styling.
*   **Deployment:** Docker atau Nginx/Apache + PHP-FPM.

### 9.1. Deployment dengan Docker Container

Aplikasi akan didistribusikan menggunakan Docker Container untuk kemudahan deployment dan konsistensi lingkungan.

*   **Dockerfile:** Akan dibuat `Dockerfile` untuk membangun image aplikasi Laravel. Ini akan mencakup instalasi PHP, Nginx (atau Apache), Composer dependencies, dan konfigurasi yang diperlukan.
*   **Docker Compose:** File `docker-compose.yml` akan digunakan untuk mendefinisikan dan menjalankan multi-container Docker application, termasuk:
    *   **`app` service:** Untuk aplikasi Laravel (berdasarkan `Dockerfile`).
    *   **`nginx` service:** Sebagai web server (jika tidak digabungkan dengan PHP-FPM di container `app`).
    *   **`db` service:** Untuk database MySQL.
    *   **`redis` service:** (Opsional) Untuk caching atau queue.
*   **Manajemen Konfigurasi (.env):** Semua konfigurasi sensitif dan lingkungan (seperti database credentials, API keys, mail settings, dll.) akan disimpan dalam file `.env`. Saat deployment dengan Docker, variabel-variabel ini akan disuntikkan ke dalam container melalui environment variables, memastikan aplikasi dapat dikonfigurasi tanpa harus membangun ulang image. Contoh variabel `.env` yang penting:
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
*   **Persistent Storage:** Data yang perlu dipertahankan (seperti database, uploaded files) akan menggunakan Docker volumes untuk memastikan data tidak hilang ketika container di-restart atau dihapus.

## 10. Progress & Tracking

Dokumen ini akan diupdate secara berkala untuk melacak progress implementasi, gaps, dan next steps.

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


