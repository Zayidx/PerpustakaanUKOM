# Dokumentasi Fitur Peminjaman (tanpa Pengembalian)

Panduan ini memuat alur lengkap peminjaman buku dari sisi Siswa hingga disetujui Admin Perpus (status `accepted`). Pengembalian/denda dibahas terpisah.

## 1) Skema & Migrasi

1. **Peminjaman** – `database/migrations/2025_10_28_030000_create_peminjaman_data.php`
   - Kolom: `kode` (unik, 6 digit), `siswa_id` (FK `siswa`, cascade), `admin_perpus_id` (nullable FK `admin_perpus`), `status` (`pending|accepted|returned|cancelled`, default `pending`), `accepted_at`, `due_at`, `returned_at` (tidak dipakai pada alur ini), `metadata` (JSON), timestamps.
2. **Item Peminjaman** – `database/migrations/2025_10_28_040000_create_peminjaman_items_table.php`
   - Kolom: `peminjaman_id` (FK `peminjaman_data`, cascade), `buku_id` (FK `buku`, cascade), `quantity` (default 1), timestamps, unique `(peminjaman_id, buku_id)`.

Jalankan migrasi:
```bash
php artisan migrate
```

## 2) Model

- `app/Models/Peminjaman.php`: fillable `kode,siswa_id,admin_perpus_id,status,accepted_at,due_at,returned_at,metadata`; casts datetime + `metadata` array; relasi `siswa()`, `adminPerpus()`, `items()`.
- `app/Models/PeminjamanItem.php`: fillable `peminjaman_id,buku_id,quantity`; relasi `peminjaman()` dan `buku()`.

## 3) Alur Ringkas (Siswa → Admin Perpus)

1. **Siswa memilih buku** di halaman daftar buku.
2. **Siswa membuat kode peminjaman** → data tersimpan sebagai `pending`.
3. **Siswa menampilkan QR/PIN** di halaman Kode Peminjaman.
4. **Admin Perpus memindai QR atau mengetik PIN** di halaman Scan Peminjaman.
5. **Jika stok cukup**, status berubah menjadi `accepted`, stok buku berkurang, `due_at` otomatis +7 hari.

## 4) Siswa – Daftar & Keranjang Buku

Komponen: `app/Livewire/Siswa/ListBuku.php`  
View: `resources/views/livewire/siswa/list-buku.blade.php`

- **Filter & pagination**: pencarian judul/penulis/penerbit, filter kategori, paginate 12 buku.
- **Seleksi buku** (`toggleSelection`):
  - Validasi buku ada, stok > 0, dan belum ada pinjaman `pending|accepted` oleh siswa yang sama (`bookHasActiveLoan`).
  - Keranjang disimpan ke session `loan_cart` agar tetap ada saat reload.
- **Detail & keranjang**: modal detail buku (`showDetail`) dan modal *Keranjang Peminjaman* yang dapat dikosongkan.
- **Pembuatan kode** (`generateLoanCode`):
  - Lock stok buku `lockForUpdate` dalam transaksi DB.
  - Tolak jika ada buku hilang atau stok habis (ValidationException).
  - Buat `peminjaman_data` status `pending` dengan kode unik 6 digit (`generateUniqueCode`) + metadata `book_ids` dan `generated_by`.
  - Buat `peminjaman_items` untuk setiap buku (quantity 1).
  - Bersihkan keranjang lalu redirect ke route `siswa.kode-peminjaman` membawa kode.

## 5) Siswa – Kode Peminjaman

Komponen: `app/Livewire/Siswa/KodePinjaman.php`  
View: `resources/views/livewire/siswa/kode-pinjaman.blade.php`

- **Load data**: memastikan peminjaman milik siswa yang login; memuat buku, kategori, penerbit, dan admin yang akan mengesahkan (jika sudah diisi).
- **QR & PIN**: membuat SVG via `SimpleSoftwareIO\QrCode` berisi payload:
  ```json
  { "code": "<PIN 6 digit>", "loan_id": <id>, "student_id": <siswa_id>, "books": [{ "id": 1, "title": "..." }], "generated_at": "<ISO time>" }
  ```
- **Status update**: halaman melakukan `wire:poll.5s` saat status `pending`; `refreshLoan()` mendeteksi perubahan status dan memicu alert (Swal) melalui event `loan-status-updated`.
- **Tampilan**: badge status (`Menunggu` / `Sedang Dipinjam`), QR + PIN, detail waktu dibuat/diterima/jatuh tempo, daftar buku.

## 6) Siswa – Riwayat Peminjaman

Komponen: `app/Livewire/Siswa/ListPeminjaman.php`  
View: `resources/views/livewire/siswa/list-peminjaman.blade.php`

- Filter status (`all|pending|accepted|returned|cancelled`), urut terbaru, paginate 10.
- Tabel menampilkan kode, status, tanggal buat/due, dan daftar buku.
- Tombol menuju tampilan QR: `Lihat Kode Peminjaman` hanya untuk status `pending`; bila sudah `accepted` diarahkan ke kode pengembalian (diluar cakupan dokumen ini).

## 7) Admin Perpus – Proses Peminjaman

Komponen: `app/Livewire/AdminPerpus/ScanPeminjaman.php`  
View: `resources/views/livewire/admin-perpus/scan-peminjaman.blade.php`  
Library scanner: `public/assets/js/html5-qrcode.min.js` (kamera perlu HTTPS atau `http://localhost`).

- **Input**:
  - Event `qr-scanned` (kamera) → `handleScan()` decode JSON, mengambil `code`/`loan_id`.
  - Form manual 6 digit → `processManualCode()` (`digits:6`).
- **Validasi awal**: akun harus punya data `admin_perpus`; cek peminjaman berdasarkan `code` (dan `loan_id` bila ada).
- **Proses status `pending`** (`processLoanData`):
  - Transaksi DB: ambil item dan stok buku dengan `lockForUpdate`.
  - Jika stok kurang, lempar ValidationException dan tampilkan error.
  - Kurangi stok sesuai quantity, set `status` `accepted`, isi `admin_perpus_id`, `accepted_at = now()`, `due_at = now()->addWeek()`.
- **UI hasil**: panel detail menampilkan kode, nama siswa + kelas, accepted_at/due_at, serta daftar judul buku. Notifikasi keberhasilan/gagal dikirim via event `notify`.

## 8) Routing & Akses

- `routes/siswa.php`:
  - `GET /siswa/buku` → `Siswa\ListBuku`
  - `GET /siswa/peminjaman` → `Siswa\ListPeminjaman`
  - `GET /siswa/peminjaman/kode/{kode}` → `Siswa\KodePinjaman`
- `routes/adminperpus.php`:
  - `GET /admin-perpus/scan-peminjaman` → `AdminPerpus\ScanPeminjaman`
  - `GET /admin-perpus/peminjaman` → `AdminPerpus\ManajemenPeminjaman` (dipakai untuk monitoring dan pembatalan/return; tidak dijelaskan di sini).

Semua route dilindungi middleware `auth` + `role` masing-masing.

## 9) Status & Validasi Penting

- Status relevan untuk alur ini: `pending` (baru dibuat siswa) dan `accepted` (sudah diproses Admin Perpus). Status `returned/cancelled` hanya dicatat sebagai konteks.
- Siswa tidak bisa memilih buku yang habis stok atau masih terikat peminjaman `pending|accepted`.
- Stok buku **tidak berubah** saat siswa membuat permintaan; stok baru berkurang setelah Admin Perpus menyetujui.
- `due_at` dihitung otomatis +7 hari sejak pemrosesan; dapat diubah nanti via manajemen jika diperlukan.

## 10) Pengujian

File: `tests/Feature/LoanFlowTest.php`
- `test_student_can_generate_loan_code`: memastikan permintaan `pending` tercipta dan redirect ke halaman kode tanpa mengubah stok.
- `test_admin_perpus_scan_accepts_pending_loan`: scanner QR mengubah status ke `accepted`, mengisi `accepted_at/due_at` (+7 hari), dan mengurangi stok.
- `test_admin_perpus_can_process_manual_code_when_scanner_not_available`: jalur input manual berperilaku sama dengan scan.
- `test_student_cannot_generate_loan_when_stock_empty`: penolakan peminjaman saat stok 0.

Jalankan:
```bash
php artisan test --filter=LoanFlowTest
```
