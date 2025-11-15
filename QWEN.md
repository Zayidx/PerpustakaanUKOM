# QWEN.md - Perpustakaan Laravel Application

## Project Overview

This is a Laravel 12 web application named "perpustakaan" (Indonesian for "library"), which appears to be a school management system with a focus on student management. The application uses Laravel with Livewire for dynamic user interfaces and follows modern PHP development practices.

The application has multiple user roles (Administrator/Guru/Siswa) and includes features for managing students, with a specific CRUD module for student management as documented in the project documentation. The application uses SQLite as the default database and implements Tailwind CSS for styling.

## Key Technologies and Dependencies

- **Laravel Framework 12**: The core PHP framework
- **Livewire 3.6**: For building dynamic, reactive UI components
- **PHP 8.2+**: Required PHP version
- **Bootstrap 5 + custom CSS**: Styling via CDN plus `public/css/app.css`
- **Static assets in `public/`**: CSS/JS served directly without a bundler
- **SQLite**: Default database (configurable)
- **Simple QR Code**: For generating QR codes

## Project Architecture

The application follows Laravel's standard directory structure:
- `app/`: Contains core application code including models, controllers, and Livewire components
- `routes/`: Contains route definitions (admin.php, guru.php, siswa.php, web.php, console.php)
- `resources/`: Contains Blade views (frontend assets live in `public/`)
- `database/`: Contains migrations, factories, and seeders
- `config/`: Laravel configuration files
- `public/`: Web-accessible files and assets

The application implements role-based access control with routes separated by user roles (admin, guru, siswa).

## Building and Running

### Initial Setup

1. **Install dependencies**:
   ```bash
   composer install
   ```

2. **Set up environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database setup** (with seeding):
   ```bash
   php artisan migrate --seed
   ```

4. **Create storage link** (for file uploads):
   ```bash
   php artisan storage:link
   ```

### Development Commands

- **Run the application in development mode**:
  ```bash
  composer run dev
  # or run php artisan serve manually
  ```
  Start queue workers or log viewers in separate terminals if needed:
  ```bash
  php artisan queue:listen --tries=1
  php artisan pail --timeout=0
  ```

### Testing

- **Run tests**:
  ```bash
  composer run test
  # Or directly:
  php artisan test
  ```

### Additional Setup Script

The application provides a convenient setup script:
```bash
composer run setup
```
This command:
- Installs Composer dependencies
- Creates .env file if it doesn't exist
- Generates application key
- Runs migrations

## Development Conventions

- The project uses Livewire for building dynamic user interfaces with minimal JavaScript
- Custom styling lives in `public/css/app.css`; edit the static file directly
- Database migrations and seeders are used for schema management and test data
- The application supports dark/light mode with JavaScript components to manage theme switching
- File uploads are handled through Livewire's WithFileUploads trait
- Pagination is implemented using Livewire's WithPagination trait
- The application uses Laravel's built-in authentication with role-based access control

## Special Features

- **Student Management**: Complete CRUD functionality for managing students with file upload support
- **Role-Based Access**: Different route files for admin, teacher (guru), and student access
- **File Uploads**: Support for uploading student photos with preview functionality
- **QR Code Generation**: Using the simplesoftwareio/simple-qrcode package
- **Dark/Light Mode**: Dynamic theme switching with JavaScript support
- **Database Transactions**: Used in critical operations to ensure data consistency

## Key Files and Directories

- `app/Livewire/Admin/ManajemenSiswa.php`: Core Livewire component for student management
- `resources/views/livewire/admin/manajemen-siswa.blade.php`: View for the student management interface
- `routes/admin.php`, `routes/guru.php`, `routes/siswa.php`: Role-specific routing
- `database/migrations/`: Database schema definitions
- `database/seeders/`: Data seeding logic
- `public/css/app.css`, `public/assets/**`: Frontend styles/scripts served without Vite
- `documentation.md`: Detailed documentation of the student management module in Indonesian
