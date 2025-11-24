# Perpustakaan Digital – Laravel 12 + Livewire 3

Aplikasi manajemen perpustakaan berbasis web dengan peminjaman/pengembalian berbasis QR, peran terpisah (Super Admin, Admin Perpus, Siswa), dan dashboard interaktif tanpa build pipeline frontend.

## Isi Singkat
- Ringkasan fitur
- Teknologi & prasyarat
- Instalasi cepat
- Akun default setelah seeding
- Catatan penggunaan QR scanner
- Perintah umum
- Referensi dokumentasi internal

## Fitur Utama
- **Manajemen koleksi**: Buku, kategori, penulis, penerbit, stok, dan unggah sampul.
- **Peminjaman & pengembalian**: QR/PIN 6 digit; Admin Perpus dapat scan atau input manual; stok otomatis berkurang saat disetujui.
- **Pengumuman & acara**: CRUD dengan konten Markdown (CommonMark) yang dirender aman di view publik.
- **Role & dashboard**: Super Admin (pengaturan global), Admin Perpus (operasional pinjam/return), Siswa (cari buku, ajukan pinjam, lacak status).
- **Livewire-first UI**: Navigasi cepat, pagination/filter, dan notifikasi Swal tanpa reload penuh.
- **Tanpa npm/vite**: Aset disajikan dari `public/assets` sehingga setup lebih ringan.

## Teknologi & Prasyarat
- PHP 8.2+ dan Composer
- MySQL/MariaDB
- Ekstensi PHP: mbstring, openssl, pdo, gd/imagemagick (untuk QR/gambar)
- Server lokal dengan HTTPS atau `http://localhost` (dibutuhkan untuk izin kamera)

## Instalasi Cepat
1) Clone repo dan pasang dependensi
```bash
git clone <repo-url> perpustakaan
cd perpustakaan
composer install
```
2) Salin `.env` lalu atur koneksi database dan `APP_URL`
```bash
cp .env.example .env
# sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL
```
3) Generate key dan jalankan migrasi + seeder
```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```
4) Jalankan server pengembangan
```bash
php artisan serve
```
Aplikasi siap di `http://localhost:8000` (gunakan HTTPS bila ingin menguji kamera di produksi).

## Akun Default (setelah `--seed`)
- Super Admin: `superadmin@gmail.com` / `superadmin123`
- Admin Perpus: `adminperpus@gmail.com` / `adminperpus123`
- Siswa: `siswa@gmail.com` / `siswa123`

## Catatan QR Scanner (Desktop & Mobile)
- Halaman scanner: `/admin-perpus/scan-peminjaman` dan `/admin-perpus/scan-pengembalian`.
- Izinkan akses kamera di browser; HTTPS atau `http://localhost` wajib untuk izin kamera.
- Scanner otomatis memilih kamera belakang/eksternal jika tersedia; fallback ke kamera default.
- Jika kamera bermasalah, gunakan input manual kode 6 digit di halaman yang sama.

## Perintah Umum
- Jalankan test: `php artisan test`
- Bersihkan cache konfigurasi: `php artisan config:clear`
- Sinkronisasi storage publik: `php artisan storage:link`

## Struktur Singkat
- `app/Livewire/*` – logika halaman dashboard (peminjaman, buku, pengumuman, dsb).
- `resources/views/livewire/*` – tampilan Livewire, termasuk scanner QR.
- `database/migrations` – skema tabel utama (buku, peminjaman, pengumuman, user/role).
- `database/seeders` – data awal role, pengguna contoh, referensi buku, dan siswa.

## Dokumentasi Tambahan
- `documentation.md` – detail modul Manajemen Siswa.
- `documentation_peminjaman.md` – alur peminjaman (QR/PIN) dari siswa ke admin.
- `documentation_pengumuman.md` – modul pengumuman & konten Markdown.

Selamat menggunakan, dan silakan sesuaikan lebih lanjut sesuai kebutuhan perpustakaan Anda!
