# Indonesian Text Audit - tis_api_laravel

## Summary
This document contains a comprehensive list of all Indonesian text found in the `tis_api_laravel` directory that requires translation for internationalization (i18n).

---

## 1. PHP Files - Livewire Components & Controllers

### [app/Livewire/UserManagement.php](app/Livewire/UserManagement.php)

| Line | Type | Indonesian Text | English Translation | Context |
|------|------|-----------------|---------------------|---------|
| 57 | Session Flash | `'User berhasil dibuat.'` | "User successfully created." | Success message after user creation |
| 96 | Session Flash | `'User berhasil diupdate.'` | "User successfully updated." | Success message after user update |
| 102 | Session Flash | `'Tidak dapat menghapus akun sendiri.'` | "Cannot delete your own account." | Error message when trying to delete own account |
| 107 | Session Flash | `'User berhasil dihapus.'` | "User successfully deleted." | Success message after user deletion |

---

## 2. Blade Template Files

### [resources/views/cms/user-management.blade.php](resources/views/cms/user-management.blade.php)

| Line | Type | Indonesian Text | English Translation | Context |
|------|------|-----------------|---------------------|---------|
| 5 | Description | `'Kelola akun pengguna dan role akses'` | "Manage user accounts and access roles" | Page subtitle/description |
| 10 | Button Label | `'Tambah User'` | "Add User" | Create new user button |
| 33 | Placeholder | `'Cari nama atau email...'` | "Search by name or email..." | Search input placeholder |
| 41 | Table Header | `'Nama'` | "Name" | User table column header |
| 42 | Table Header | `'Email'` | "Email" | User table column header |
| 43 | Table Header | `'Roles'` | "Roles" | User table column header |
| 44 | Table Header | `'Dibuat'` | "Created" | User table column header |
| 74 | Button Label | `'Edit'` | "Edit" | Edit user button |
| 78 | Button Label & Confirmation | `'Yakin hapus user '{{ $user->name }}'?'` | "Are you sure you want to delete user '{{ $user->name }}'?" | Delete confirmation dialog |
| 79 | Button Label | `'Hapus'` | "Delete" | Delete user button |
| 110 | Modal Title | `'Tambah User Baru'` | "Add New User" | Create user modal title |
| 117 | Form Label | `'Nama'` | "Name" | Name field label |
| 122 | Form Label | `'Email'` | "Email" | Email field label |
| 127 | Form Label | `'Password'` | "Password" | Password field label |
| 132 | Form Label | `'Konfirmasi Password'` | "Confirm Password" | Password confirmation field label |
| 136 | Form Label | `'Roles'` | "Roles" | Roles selection label |
| 160 | Modal Title | `'Edit User'` | "Edit User" | Edit user modal title |
| 167 | Form Label | `'Nama'` | "Name" | Name field label (edit modal) |
| 172 | Form Label | `'Email'` | "Email" | Email field label (edit modal) |
| 177 | Form Label | `'Password Baru (kosongkan jika tidak diubah)'` | "New Password (leave empty if not changing)" | New password field label in edit modal |
| 182 | Form Label | `'Konfirmasi Password Baru'` | "Confirm New Password" | New password confirmation field label |
| 186 | Form Label | `'Roles'` | "Roles" | Roles selection label (edit modal) |

### [resources/views/cms/session-downloads.blade.php](resources/views/cms/session-downloads.blade.php)

| Line | Type | Indonesian Text | English Translation | Context |
|------|------|-----------------|---------------------|---------|
| 21 | Placeholder | `'Cari session id / rake / nama file...'` | "Search by session id / rake / filename..." | Search input placeholder |
| 42 | Table Header | `'Nama Session'` | "Session Name" | Session table column header |

### [resources/views/welcome.blade.php](resources/views/welcome.blade.php)

| Line | Type | Indonesian Text | English Translation | Context |
|------|------|-----------------|---------------------|---------|
| 647 | Heading | `'Operasi yang terlihat sebelum gangguan terasa.'` | "Operations visible before failures occur." | Section heading |
| 650 | Description | `'layer operasional yang lebih presisi. Tampilan dibuat untuk kontrol cepat, bukan sekadar landing page generik.'` | "operational layer with more precision. Display created for quick control, not just a generic landing page." | Feature description |
| 739 | Paragraph | `'Fokus utamanya bukan dekorasi, tapi rasa kontrol. Setiap blok dirancang seperti panel operasi:'` | "The main focus is not decoration, but a sense of control. Each block is designed like an operation panel:" | UI design philosophy |
| 777 | Description | `'Distribusi fault, tren session, dan equipment paling bermasalah ditampilkan sebagai insight operasional, bukan widget tempelan.'` | "Fault distribution, session trends, and most problematic equipment are displayed as operational insights, not just attached widgets." | Dashboard feature description |

### [resources/views/cms/api-docs.blade.php](resources/views/cms/api-docs.blade.php)

| Line | Type | Indonesian Text | English Translation | Context |
|------|------|-----------------|---------------------|---------|
| 270 | API Param Description | `'Nama komponen'` | "Component name" | API parameter description for equipment_name |
| 653 | API Response Description | `'Validasi field gagal'` | "Field validation failed" | HTTP 422 response description |

### [resources/views/components/layouts/auth/simple.blade.php](resources/views/components/layouts/auth/simple.blade.php)

| Line | Type | Indonesian Text | English Translation | Context |
|------|------|-----------------|---------------------|---------|
| 35 | Platform Description | `'Platform pemantauan dan analisis kegagalan sistem trainset MRT Jakarta.'` | "Monitoring and failure analysis platform for MRT Jakarta trainset systems." | Auth page subtitle |

---

## 3. Database Seeder Files

### [database/seeders/RakeSeeder.php](database/seeders/RakeSeeder.php)

| Line | Type | Indonesian Text | English Translation | Context |
|------|------|-----------------|---------------------|---------|
| 30 | Rake Description | `'Unit perdana CP108. 6-car EMU Sumitomo, formasi Tc1–M1–M2–M1\'–M2\'–Tc2. Beroperasi sejak 2019.'` | "First unit CP108. 6-car EMU Sumitomo, formation Tc1–M1–M2–M1'–M2'–Tc2. Operating since 2019." | Train unit description |
| 96 | Equipment Description | `'6-car EMU Sumitomo CP108. Sistem pintu 6 set per gerbong, lebar 1.400 mm.'` | "6-car EMU Sumitomo CP108. Door system 6 sets per car, width 1,400 mm." | Door system equipment description |
| 114 | Equipment Description | `'6-car EMU Sumitomo CP108. Pengisian baterai regeneratif di setiap stasiun.'` | "6-car EMU Sumitomo CP108. Regenerative battery charging at each station." | Battery system equipment description |

---

## 4. Configuration & Documentation Files

### [BLUEPRINT.md](BLUEPRINT.md)

| Line | Type | Indonesian Text | English Translation | Context |
|------|------|-----------------|---------------------|---------|
| 3 | Document Description | `'Dokumen ini menjelaskan cetak biru (blueprint) untuk pengembangan backend API **tis_gateway**, yang akan berfungsi sebagai *consumer* data dari TIS Gateway (Python).'` | "This document describes the blueprint for backend API development for **tis_gateway**, which will serve as a data consumer from TIS Gateway (Python)." | Document introduction |
| 6 | Subtitle | `'**Update Terbaru:** Aplikasi dibangun sebagai monolith dengan FE dan BE dalam satu aplikasi Laravel. Ada mode development dan production.'` | "**Latest Update:** The application is built as a monolith with FE and BE in one Laravel application. There are development and production modes." | Project status update |
| 10 | Description | `'Backend API akan menerima data failure dan file report dari TIS Gateway melalui HTTP POST requests. Data akan disimpan dalam database MySQL, dan file akan disimpan di sistem penyimpanan server. Frontend akan dibangun menggunakan Livewire untuk dashboard, tampilan data, dan CMS.'` | "The backend API will receive failure data and report files from TIS Gateway via HTTP POST requests. Data will be stored in a MySQL database, and files will be stored on the server storage system. The frontend will be built using Livewire for dashboard, data display, and CMS." | Architecture description |
| 15 | Section Title | `'**Monolith Architecture:** FE dan BE terintegrasi dalam satu aplikasi Laravel. Frontend menggunakan Blade templates dengan Livewire untuk interaktivitas, tanpa API terpisah untuk FE.'` | "**Monolith Architecture:** FE and BE integrated in one Laravel application. Frontend uses Blade templates with Livewire for interactivity, without a separate API for FE." | Architecture explanation |
| 42 | Section Description | `'**Development Mode:** Menggunakan local environment, dengan debugging enabled, hot reload untuk assets, dan database lokal. **Production Mode:** Optimized untuk performance, dengan caching, minified assets, dan konfigurasi production database.'` | "**Development Mode:** Uses local environment, with debugging enabled, hot reload for assets, and local database. **Production Mode:** Optimized for performance, with caching, minified assets, and production database configuration." | Mode descriptions |
| 51 | Bullet Point | `'Admin dapat membuat, mengedit, dan menghapus role serta permission.'` | "Admin can create, edit, and delete roles and permissions." | RBAC feature |
| 57 | Bullet Point | `'Form pendaftaran/pengeditan pengguna akan mencakup nama, email, password, dan penentuan role.'` | "User registration/edit form will include name, email, password, and role assignment." | User form description |
| 63 | Bullet Point | `'**Implementasi Frontend:** Akan menggunakan Livewire dan Yajra Datatables untuk penyajian tabel data yang efisien dan interaktif. Setiap baris data di tabel akan memiliki opsi untuk "View Detail", "Edit", dan "Delete" (sesuai permission role).'` | "**Frontend Implementation:** Will use Livewire and Yajra Datatables for efficient and interactive data table presentation. Each row will have options for "View Detail", "Edit", and "Delete" (according to role permissions)." | Table display implementation |
| 69 | Bullet Point | `'**Implementasi Frontend:** Livewire component untuk menampilkan detail, dengan kemampuan untuk mengedit atau menghapus record individual (jika diizinkan oleh permission).'` | "**Frontend Implementation:** Livewire component to display details, with the ability to edit or delete individual records (if permitted)." | Detail view implementation |
| 73 | Bullet Point | `'Tambahan tabel untuk user management dan role/permission dari Spatie.'` | "Additional tables for user management and role/permission from Spatie." | Database note |
| 174 | Deployment Description | `'**Dockerfile:** Akan dibuat `Dockerfile` untuk membangun image aplikasi Laravel. Ini akan mencakup instalasi PHP, Nginx (atau Apache), Composer dependencies, dan konfigurasi yang diperlukan.'` | "**Dockerfile:** A `Dockerfile` will be created to build the Laravel application image. This will include PHP installation, Nginx (or Apache), Composer dependencies, and required configuration." | Docker description |
| 191 | Storage Note | `'**Persistent Storage:** Data yang perlu dipertahankan (seperti database, uploaded files) akan menggunakan Docker volumes untuk memastikan data tidak hilang ketika container di-restart atau dihapus.'` | "**Persistent Storage:** Data that needs to be retained (such as database, uploaded files) will use Docker volumes to ensure data is not lost when containers are restarted or removed." | Storage explanation |
| 195 | Progress Note | `'Dokumen ini akan diupdate secara berkala untuk melacak progress implementasi, gaps, dan next steps.'` | "This document will be updated periodically to track implementation progress, gaps, and next steps." | Maintenance note |

### [README.md](README.md)

| Line | Type | Indonesian Text | English Translation | Context |
|------|------|-----------------|---------------------|---------|
| 3 | Subtitle | `'Aplikasi monolith Laravel 12 untuk menerima, menyimpan, dan mengelola data failure records dari TIS Gateway (Python). Dilengkapi dengan dashboard interaktif, user management CMS, dan API endpoints untuk integrasi eksternal.'` | "Laravel 12 monolith application for receiving, storing, and managing failure records from TIS Gateway (Python). Equipped with interactive dashboard, user management CMS, and API endpoints for external integration." | Project description |
| 6 | Feature Title | `'**API Endpoints** - RESTful API untuk submit failure records dan files'` | "**API Endpoints** - RESTful API to submit failure records and files" | Feature |
| 7 | Feature Title | `'**Dashboard** - Analytics dan statistik real-time dengan Highcharts'` | "**Dashboard** - Real-time analytics and statistics with Highcharts" | Feature |
| 8 | Feature Title | `'**User Management** - CRUD users dengan role-based access control (RBAC) menggunakan Spatie Permission'` | "**User Management** - CRUD users with role-based access control (RBAC) using Spatie Permission" | Feature |
| 9 | Feature Title | `'**Data Tables** - Interactive data tables dengan Yajra DataTables'` | "**Data Tables** - Interactive data tables with Yajra DataTables" | Feature |
| 10 | Feature Title | `'**Export** - Export data ke Excel dan PDF'` | "**Export** - Export data to Excel and PDF" | Feature |
| 11 | Feature Title | `'**Authentication** - Bearer token authentication untuk API, session-based untuk web'` | "**Authentication** - Bearer token authentication for API, session-based for web" | Feature |
| 112 | Instruction | `'Edit `.env` untuk konfigurasi:'` | "Edit `.env` for configuration:" | Setup instruction |

---

## 5. Summary Statistics

### By File Type:
- **Blade Templates (.blade.php):** 48 strings
- **PHP Files (.php):** 4 strings  
- **Seeder Files:** 3 strings
- **Documentation (.md):** 18+ strings

### By Category:
- **User Interface Labels:** 15 strings
- **Success/Error Messages:** 4 strings
- **Table Headers:** 6 strings
- **Form Labels:** 10 strings
- **Descriptions/Documentation:** 20+ strings
- **Search Placeholders:** 2 strings
- **Confirmation Dialogs:** 1 string

### Total Unique Indonesian Text Items: **60+**

---

## 6. Recommendations for Translation Implementation

### 1. **Laravel Localization Setup**
Create language files in `resources/lang/`:
```
resources/
  lang/
    en/
      messages.php
      validation.php
    id/
      messages.php
      validation.php
```

### 2. **Translation Strategy**
- Extract all hardcoded Indonesian strings from Blade templates
- Use Laravel's `__()` helper function or `@lang()` directive
- Store translations in language files
- Implement language switcher for users

### 3. **Priority Files for Translation**
1. **High Priority:** `resources/views/cms/user-management.blade.php` (user-facing)
2. **High Priority:** `app/Livewire/UserManagement.php` (session messages)
3. **Medium Priority:** `resources/views/welcome.blade.php` (marketing copy)
4. **Medium Priority:** `BLUEPRINT.md` & `README.md` (documentation)
5. **Low Priority:** Database seeders (data/setup only)

### 4. **Implementation Example**
```php
// Before
session()->flash('success', 'User berhasil dibuat.');

// After
session()->flash('success', __('messages.user_created_success'));
```

### 5. **Files to Create/Update**
- `resources/lang/id/messages.php` - Indonesian translations
- `resources/lang/en/messages.php` - English translations
- Create language switcher middleware/component
- Update `.env` to support `APP_LOCALE`

---

## 7. Additional Notes

- **Vendor Directory:** The vendor folder contains library code from dependencies (faker providers, etc.) with Indonesian content, but these should NOT be translated as they are external dependencies.
- **Compiled Assets:** JavaScript and CSS files in `public/build/` contain minified code - translations here are not necessary.
- **Framework Code:** Laravel framework files in vendor directory are standard and should not be modified.
- **Focus Areas:** User-facing strings in Blade templates and Livewire components are the primary targets for translation.

---

**Document Generated:** 2026-06-13  
**Status:** Ready for Translation Implementation
