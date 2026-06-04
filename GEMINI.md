# GEMINI.md - Backend Toko Keluarga

This document provides instructional context and development guidelines for the `backend-tokokeluarga` project.

## Project Overview
`backend-tokokeluarga` is a comprehensive inventory and operational management system designed for "Toko Keluarga". It serves as both a web-based administrative dashboard and a RESTful API provider for a companion mobile application.

- **Primary Technologies:** Laravel 13, PHP 8.3+, Livewire 4, Tailwind CSS 4.
- **Key Frameworks/Libraries:** 
    - **Sanctum:** For secure mobile API authentication.
    - **Spatie Laravel Permission:** For role and permission management.
    - **Spatie Laravel Activitylog:** For auditing system actions.
    - **Cloudinary PHP:** For handling image uploads (receipts/notices).
    - **Livewire:** For dynamic, reactive web interfaces.

## Architecture
The project follows a standard Laravel directory structure with some specific patterns:
- **Models:** Located in `app/Models/`. Core entities include `Barang`, `Kategori`, `Supplier`, `PenerimaanBarang`, and `DetailPenerimaan`.
- **Controllers:** 
    - `app/Http/Controllers/Web/`: Logic for the Livewire-based dashboard.
    - `app/Http/Controllers/Api/`: RESTful endpoints for the mobile application.
- **Services:** `app/Services/` contains external integration logic (Cloudinary, WhatsApp).
- **Observers:** `app/Observers/` handles side effects like stock updates or notifications upon model changes.
- **Views:** Blade templates and Livewire components are found in `resources/views/`.

## Building and Running

### Prerequisites
- PHP 8.3 or higher
- Composer
- Node.js & NPM
- SQLite or MySQL

### Development Commands
- **Initial Setup:**
  ```bash
  composer setup
  ```
- **Running the Development Environment:**
  ```bash
  npm run dev
  ```
- **Database Migrations:**
  ```bash
  php artisan migrate --seed
  ```
- **Testing:**
  ```bash
  php artisan test
  ```
- **Linting (PHP):**
  ```bash
  php artisan pint
  ```

### Permissions & Web Server Setup
If you are running the project using a web server like Nginx (with PHP-FPM) or Apache, you must ensure the `storage` and `bootstrap/cache` directories are writable by the web server user (usually `www-data`).

Run these commands to fix "Permission denied" errors (e.g., during Excel export or logging):
```bash
# 1. Set ownership to current user and web server group
sudo chown -R $USER:www-data storage bootstrap/cache

# 2. Grant write permissions to group
sudo chmod -R 775 storage bootstrap/cache

# 3. Ensure new files inherit group permissions (SetGID)
sudo chmod -R g+s storage bootstrap/cache

# 4. Add yourself to the www-data group to avoid future conflicts
sudo usermod -a -G www-data $USER
```
*Note: You may need to log out and log back in for the group change to take effect.*

## Development Conventions
- **Code Style:** Follow PSR-12 and Laravel's coding standards. Use `php artisan pint` to automatically format code.
- **Surgical Updates:** When modifying existing logic, ensure that both Web and API controllers are considered if the change affects shared business logic.
- **API Response:** API controllers should return consistent JSON structures.
- **Soft Deletes:** Most core models use `SoftDeletes`. Ensure queries account for this where necessary.
