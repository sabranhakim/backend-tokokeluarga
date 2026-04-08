# Toko Keluarga - Backend System

Sistem manajemen inventaris dan operasional Toko Keluarga berbasis web (Dashboard Admin) dan API untuk aplikasi mobile.

## Fitur Utama

- **Dashboard**: Ringkasan statistik total barang, supplier, dan riwayat penerimaan barang terbaru.
- **Manajemen Stok Barang**: CRUD data barang lengkap dengan kode barang unik, kategori, satuan, harga beli/jual, serta indikator stok kritis.
- **Manajemen Kategori**: Pengelompokan barang untuk memudahkan pencarian dan pelaporan.
- **Manajemen Supplier**: Pendataan pemasok barang beserta informasi kontak.
- **Penerimaan Barang (Web & Mobile)**:
    - Input barang masuk dengan form dinamis (banyak item sekaligus).
    - Auto-generate nomor terima (format: `TRM-YYYYMMDDXXXXXX`).
    - Dukungan unggah foto bon/nota fisik menggunakan Cloudinary.
    - Sistem verifikasi status penerimaan.
- **Manajemen User & Hak Akses**:
    - Pengaturan pengguna sistem.
    - Manajemen Role & Permission menggunakan `spatie/laravel-permission`.

## Tech Stack

- **Framework**: Laravel 13 (PHP 8.3+)
- **Frontend UI**: Livewire 3 & Tailwind CSS
- **Database**: MySQL / SQLite
- **Media Storage**: Cloudinary (untuk integrasi foto bon)
- **Authentication**: Laravel Sanctum (API) & Session (Web)

## Persyaratan Sistem

- PHP >= 8.3
- Composer
- Node.js & NPM

## Cara Instalasi

1.  **Clone Repository**
    ```bash
    git clone [repository-url]
    cd backend-tokokeluarga
    ```

2.  **Instal Dependensi**
    ```bash
    composer install
    npm install && npm run build
    ```

3.  **Konfigurasi Environment**
    Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasi database serta Cloudinary.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Migrasi & Seeder**
    ```bash
    php artisan migrate --seed
    ```

5.  **Jalankan Server**
    ```bash
    php artisan serve
    ```

## Lisensi

Sistem ini dikembangkan untuk keperluan internal Toko Keluarga.
