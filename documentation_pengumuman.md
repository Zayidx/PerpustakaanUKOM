# Dokumentasi Sistem Pengumuman

Panduan ini menjelaskan alur lengkap fitur Pengumuman (landing dan dashboard SuperAdmin) di proyek ini: skema database, seeder, komponen Livewire, serta cara kerja fungsi-fungsinya.

## 1) Skema & Migrasi

1. **Kategori Pengumuman** – `database/migrations/2025_10_21_042421_create_kategori_pengumuman_table.php`
   - Kolom: `nama` (unik), `deskripsi` (nullable), timestamps.
   - Relasi: `kategori_pengumuman.id` direferensikan oleh tabel `pengumuman`.
2. **Pengumuman** – `database/migrations/2025_10_21_042443_create_pengumuman_table.php`
   - Kolom utama: `judul`, `slug` (unik), `kategori_pengumuman_id` (FK cascade on delete), `admin_id` (FK ke `users`), `thumbnail_url` (nullable), `thumbnail_caption` (nullable), `konten` (longText), `status` (enum `draft|published`, default `draft`), `published_at` (nullable), timestamps.
   - Relasi: belongsTo kategori dan admin (user).

Jalankan migrasi:
```bash
php artisan migrate
```

## 2) Seeder

File: `database/seeders/PengumumanSeeder.php`

- Menyiapkan 5 kategori default dengan `firstOrCreate()`.
- Memastikan minimal 15 pengguna dengan role `SuperAdmin` untuk variasi penulis (akan membuat user dummy jika kurang).
- Membuat 15 data pengumuman published dengan:
  - Penentuan kategori berdasar nama.
  - Penentuan admin secara rotasi.
  - Pengisian `thumbnail_url`, `thumbnail_caption`, konten Markdown, `published_at` mundur beberapa hari.
- Dipanggil dari `DatabaseSeeder`.

Jalankan hanya seeder ini (opsional):
```bash
php artisan db:seed --class=PengumumanSeeder
```

## 3) Model

- `app/Models/KategoriPengumuman.php`: fillable `nama`, `deskripsi`; relasi `hasMany pengumuman`.
- `app/Models/Pengumuman.php`: fillable seluruh kolom inti; `casts` `published_at` datetime; relasi `kategori()` dan `admin()`; accessor `konten_html` merender Markdown aman via `Str::markdown`.

## 4) Komponen Dashboard (SuperAdmin)

### a. Manajemen Pengumuman
File: `app/Livewire/SuperAdmin/ManajemenPengumuman.php`  
View: `resources/views/livewire/super-admin/manajemen-pengumuman.blade.php`

- **State & QueryString**: `search`, `statusFilter`, `perPage`, `sort` (order judul/publikasi/pembuat).
- **Render**: query `pengumuman` dengan relasi `kategori, admin.role`, filter status + pencarian judul/konten, urutan dinamis (join `users` bila sort penulis), pagination.
- **create()**: reset form, set admin saat ini (`assignCurrentAdmin`), emit `initialize-editor` untuk EasyMDE.
- **edit($id)**: muat data by id, set admin saat ini, emit konten ke editor.
- **save()**:
  1. Validasi input (judul, kategori, admin, status, URL thumbnail, konten).
  2. `generateSlug()` memastikan slug unik (loop tambahan jika bentrok).
  3. `published_at` diisi sekarang atau mempertahankan nilai lama jika status `published`.
  4. `updateOrCreate` berdasarkan `pengumumanId`.
  5. Flash sukses, tutup modal (`dispatch close-modal`), reset form.
- **delete($id)**: hapus pengumuman dan flash sukses.
- **Editor**: Blade memuat EasyMDE via CDN; event `initialize-editor` mengisi konten; event `close-modal` menutup modal setelah simpan.

### b. Manajemen Kategori Pengumuman
File: `app/Livewire/SuperAdmin/ManajemenKategoriPengumuman.php`  
View: `resources/views/livewire/super-admin/manajemen-kategori-pengumuman.blade.php`

- **State & QueryString**: `search`, `perPage`, `sort`.
- **Render**: `kategori_pengumuman` dengan `pengumuman_count`, filter nama, sort (created/nama/jumlah pengumuman), pagination.
- **save()**: validasi nama unik (`Rule::unique ... ->ignore(kategoriId)`) + deskripsi, `updateOrCreate`, flash sukses, tutup modal.
- **delete($id)**: menolak hapus jika `pengumuman_count > 0`, jika aman langsung delete.

## 5) Routing & Akses

- Dashboard SuperAdmin: `routes/superadmin.php`
  - `GET /super-admin/pengumuman` → `SuperAdmin\ManajemenPengumuman`
  - `GET /super-admin/pengumuman/kategori` → `SuperAdmin\ManajemenKategoriPengumuman`
- Landing: `routes/web.php`
  - `GET /pengumuman` → `App\Livewire\Pengumuman` (daftar publik).
  - `GET /pengumuman/{slug}` → `App\Livewire\DetailPengumuman` (detail publik).
- Beranda: `Route::get('/')` mengambil 4 pengumuman published terbaru (`PengumumanModel::whereStatus('published')->latest('published_at')->take(4)`).

## 6) Komponen Landing

### a. Daftar Pengumuman
File: `app/Livewire/Pengumuman.php`  
View: `resources/views/livewire/pengumuman.blade.php`

- **QueryString**: `search`, `categoryId` (`kategori`), `perPage`.
- **Render**: pengumuman status `published`, filter judul/konten, filter kategori, urut `published_at` desc, pagination 6/12/18. Menyertakan relasi `kategori` dan `admin`.
- **UI**: kartu pengumuman dengan badge kategori, tanggal publikasi, cuplikan konten Markdown (`konten_html`), link detail.

### b. Detail Pengumuman
File: `app/Livewire/DetailPengumuman.php`  
View: `resources/views/livewire/detail-pengumuman.blade.php`

- **mount($slug)**: ambil pengumuman published by slug + relasi; jika tidak ditemukan → 404.
- **otherAnnouncements**: 5 pengumuman published lain, prioritas kategori sama (order by CASE), urut `published_at` desc.
- **UI**: tampilkan header, caption gambar, konten Markdown (HTML aman), share link (FB, X, WA), dan daftar pengumuman lain.

## 7) Alur Kerja CRUD Pengumuman (Dashboard)

1. SuperAdmin buka `super-admin/pengumuman`.
2. Klik **Tambah Pengumuman** → modal kosong, editor siap.
3. Isi judul, pilih kategori, status (draft/published), thumbnail (opsional), konten di EasyMDE.
4. Submit → validasi → slug unik dibuat → jika `published` maka `published_at` di-set → simpan → modal tertutup → daftar terbarui.
5. Edit baris → modal terisi → ubah data → simpan; jika status tetap `published`, `published_at` dipertahankan (bukan direset).
6. Hapus → konfirmasi Livewire → data terhapus.

Kategori:
1. Buka `super-admin/pengumuman/kategori`.
2. Tambah/Edit via modal, nama wajib unik.
3. Hapus ditolak jika kategori masih dipakai pengumuman.

## 8) Menjalankan & Pengujian

- Jalankan migrasi + seeder penuh:
  ```bash
  php artisan migrate --seed
  ```
- Atau migrasi saja, lalu seeder spesifik:
  ```bash
  php artisan migrate
  php artisan db:seed --class=PengumumanSeeder
  ```
- Pastikan link storage:
  ```bash
  php artisan storage:link
  ```
- Cek landing:
  - `/pengumuman` → list publik (search/kategori/pagination).
  - `/pengumuman/{slug}` → detail.
- Cek dashboard:
  - Login sebagai `SuperAdmin`, buka `/super-admin/pengumuman` dan `/super-admin/pengumuman/kategori`.

## 9) Catatan Teknis

- Konten menggunakan Markdown (Laravel `Str::markdown`), HTML non-aman dilucuti (`html_input => strip`).
- Sorting penulis di dashboard memakai join `users`; field `admin_id` mengacu ke `users.id` (bukan ke tabel admin khusus).
- `updateOrCreate` akan membuat baris baru jika `pengumumanId` hilang; pastikan state ID tetap terisi saat edit di produksi.

## 10) Glosarium Teknis

- **Slug**: versi ramah-URL dari judul (huruf kecil, spasi diganti tanda pisah). Contoh: judul "Pengumuman Penting Hari Ini" → slug `pengumuman-penting-hari-ini`. Digunakan untuk URL detail `/pengumuman/{slug}` dan bersifat unik.
- **Markdown**: sintaks ringan untuk memformat teks (bold, italic, list, heading, link) tanpa HTML mentah. Disimpan apa adanya di kolom `konten` lalu dirender menjadi HTML aman menggunakan `Str::markdown`.
- **Published/Draft**: status konten. Draft tidak tampil di landing, Published tampil di semua halaman publik. Tanggal `published_at` menentukan urutan di landing.
- **Kategori**: taksonomi pengumuman untuk filter dan penataan (FK `kategori_pengumuman_id`).
- **Thumbnail**: URL gambar pendukung; `thumbnail_caption` adalah teks keterangan di detail.

## 11) Alur Markdown & Toolbar

### a. Di Form (Dashboard)
1. Modal memuat `<textarea id="konten-editor">` (lihat `resources/views/livewire/super-admin/manajemen-pengumuman.blade.php`).
2. Saat `create()`/`edit()` dipanggil, komponen mem-emit event `initialize-editor` (lihat `app/Livewire/SuperAdmin/ManajemenPengumuman.php`).
3. Script di Blade menangkap event ini dan membuat instance **EasyMDE**:
   - `initialValue: content` mengisi konten lama.
   - `toolbar` di-set ke tombol: bold, italic, heading, quote, bullet/numbered list, link, image, preview, side-by-side, fullscreen.
4. `pengumumanEditor.codemirror.on('change', ...)` mengirim isi editor ke properti Livewire `konten` via `@this.set`.

**Kustomisasi toolbar**: ubah array `toolbar` di inisialisasi EasyMDE (tambah `code`, `strikethrough`, atau hapus tombol yang tidak perlu).

### b. Penyimpanan
1. Saat submit, nilai `konten` yang dikirim Livewire disimpan ke kolom `pengumuman.konten`.
2. Jika status `published`, `published_at` diisi (baru atau dipertahankan).

### c. Render di Landing/Detail
1. Model `Pengumuman` menyediakan accessor `konten_html` yang memanggil `Str::markdown($this->konten, ['html_input' => 'strip', 'allow_unsafe_links' => false])`. Ini:
   - Mengonversi sintaks markdown (judul `##`, list `-`, bold `**...**`, link `[teks](url)`) menjadi HTML.
   - Menolak HTML mentah demi keamanan (XSS).
2. View:
   - Daftar publik (`resources/views/livewire/pengumuman.blade.php`) memakai `Str::limit(strip_tags($announcement->konten_html), 120)` untuk cuplikan ringkas.
   - Detail (`resources/views/livewire/detail-pengumuman.blade.php`) menampilkan `{!! $announcement->konten_html !!}` untuk HTML penuh.
3. Styling konten ada di blok CSS `.announcement-content` (detail) dan card hover (list).

### d. Preview/Side-by-Side
- Tombol `preview` dan `side-by-side` bawaan EasyMDE aktif karena ada di `toolbar`. Klik untuk melihat hasil markdown sebelum simpan.

### e. Tips Pakai Markdown
- Heading: `## Judul` atau `### Subjudul`.
- Bold/Italic: `**tebal**`, `_miring_`.
- List: `- item` atau `1. item`.
- Link: `[teks](https://example.com)`.
- Gambar: `![alt](https://...)` (ganti dengan URL publik).
