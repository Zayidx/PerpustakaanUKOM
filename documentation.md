# Dokumentasi Manajemen Siswa

Panduan ini menjelaskan bagaimana modul CRUD *Manajemen Siswa* dibangun menggunakan Laravel 12 + Livewire.

## 1. Skema Data

1. **Migrasi `users`** – tambahkan kolom `phone_number`, `role_id`, dan `rememberToken()`.
2. **Migrasi `siswa`** – tabel baru berisi:
   - `user_id` (FK ke `users`),
   - `nisn`, `nis` (unik), `nip` (opsional),
   - `alamat`, `jenis_kelamin`, `foto`.
3. **Seeder** – `DatabaseSeeder` membuat peran *Administrator/Guru/Siswa*, user admin, dan memanggil `SiswaSeeder` (50 siswa fiktif menggunakan faker bahasa Indonesia).

Jalankan:
```bash
php artisan migrate --seed
```

## 2. Komponen Livewire

File: `app/Livewire/Admin/ManajemenSiswa.php`
- Trait `WithFileUploads` dan `WithPagination` mengatur upload & tabel.
- Properti publik menangani state form (nama, email, NISN, foto, dll.).
- `$messages` berisi pesan validasi berbahasa Indonesia.
- `rules()` memvalidasi input, termasuk password opsional saat edit.
- `store()`:
  1. Normalisasi input,
  2. Ambil `role_id` siswa,
  3. Kelola unggah foto (hapus foto lama jika ada),
  4. Gunakan `DB::transaction` untuk menyimpan user & siswa.
- `edit($id)` memuat data ke form.
- `delete($id)` menghapus siswa beserta user & file foto.
- `listSiswa` mengembalikan data dengan relasi user + pagination.

## 3. Blade View Livewire

File: `resources/views/livewire/admin/manajemen-siswa.blade.php`
- Card berisi tombol *Tambah Siswa* yang membuka modal.
- Form modal (`wire:submit.prevent="store"`) memuat seluruh field termasuk upload foto dengan preview.
- Tabel menampilkan daftar siswa, lengkap dengan tombol `Edit` dan `Hapus` (swal konfirmasi default Livewire `wire:confirm`).
- Script `close-modal` menutup modal setelah aksi sukses.

## 4. Routing

Tambahkan pada `routes/admin.php`:
```php
Route::get('/admin/manajemen-siswa', \App\Livewire\Admin\ManajemenSiswa::class)
    ->name('admin.manajemen-siswa');
```

## 5. Integrasi Tema & Navigasi

- Sidebar admin menggunakan `wire:navigate` agar perpindahan antar halaman tanpa reload dan tetap menyimpan state dark/light mode.
- `public/assets/static/js/components/dark.js` mengekspos `setTheme()` dan membersihkan kelas lama sebelum menambah baru.
- `dashboard-layouts.blade.php` mendengarkan event `livewire:navigated` untuk menerapkan kembali tema setelah navigasi.

## 6. Tips Penggunaan

1. Pastikan `public/storage` tersambung ke `storage/app/public`:
   ```bash
   php artisan storage:link
   ```
2. Siapkan direktori upload sementara Livewire:
   ```bash
   mkdir -p storage/framework/livewire-tmp
   mkdir -p storage/app/public/livewire-tmp
   chmod -R 775 storage/framework/livewire-tmp storage/app/public/livewire-tmp
   ```
3. Jika menggunakan cache/queue database, buat tabelnya terlebih dahulu:
   ```bash
   php artisan cache:table
   php artisan queue:table
   php artisan migrate
   ```
4. Batas ukuran foto dapat diubah pada rule `max` di komponen.

## 7. Alur CRUD

1. Klik *Tambah Siswa* → modal kosong.
2. Isi data & upload foto → klik *Simpan*. Livewire memvalidasi, menyimpan user+siswa, lalu menutup modal.
3. Klik *Edit* → modal terisi data. Ubah kolom (password opsional), klik *Simpan*.
4. Klik *Hapus* → konfirmasi → data beserta foto dihapus.
5. Pagination mengatur tampilan 5/10/25 siswa per halaman.

Selamat menggunakan modul manajemen siswa!
