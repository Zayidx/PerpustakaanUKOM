# ScannerDocumentation

## Step-by-step Scanner (Admin Perpus)

1.  **Load halaman scan**
    
    -   Peminjaman: `resources/views/livewire/admin-perpus/scan-peminjaman.blade.php`
    -   Pengembalian: `resources/views/livewire/admin-perpus/scan-pengembalian.blade.php`
    -   Memuat `public/assets/js/html5-qrcode.min.js` untuk akses kamera.
2.  **Inisialisasi kamera**
    
    -   Cari kamera belakang lebih dulu (`deviceId` label `back|rear|environment` → `facingMode: environment`).
    -   Fallback kamera depan (`disableFlip` dimatikan untuk user-facing).
    -   Jika gagal, tampilkan pesan error dan izinkan klik untuk retry.
3.  **Mulai scanning**
    
    -   Instance `Html5Qrcode` memanggil callback dengan teks QR.
    -   Hasil scan mengirim event `qr-scanned` ke Livewire (payload teks QR).
4.  **Form manual (fallback)**
    
    -   Input kode 6 digit → `processManualCode()` pada komponen Livewire admin.
5.  **Validasi QR (server-side)**
    
    -   Komponen: `app/Livewire/AdminPerpus/ScanPeminjaman.php` dan `ScanPengembalian.php`.
    -   Validasi: signature HMAC, usia QR (≤30 hari), `action` sesuai halaman, kecocokan `loan_id`/`student_id`, scope `admin_perpus_id` (miliki admin login atau masih null), status loan sesuai (pending untuk peminjaman; accepted untuk pengembalian).
6.  **Proses bisnis**
    
    -   Peminjaman (status pending): lock stok, cek stok cukup, kurangi stok, set `accepted_at`, `due_at = now()+7 hari`, isi `admin_perpus_id`, ubah status ke `accepted`.
    -   Pengembalian (status accepted): hitung denda, opsional konfirmasi “sudah dibayar”/“belum dibayar”, kembalikan stok, catat `PeminjamanPenalty` bila ada denda, set status `returned`.
7.  **Feedback ke UI**
    
    -   Event Livewire `notify` menampilkan toast/notifikasi di admin.
    -   Di siswa, polling 2 detik memperbarui status sehingga QR/kode disembunyikan dan pesan “QR tidak diperlukan” muncul otomatis setelah diproses.
8.  **File Rujukan**
    
    -   Komponen admin: `app/Livewire/AdminPerpus/ScanPeminjaman.php`, `app/Livewire/AdminPerpus/ScanPengembalian.php`
    -   View admin (scanner + JS): `resources/views/livewire/admin-perpus/scan-peminjaman.blade.php`, `resources/views/livewire/admin-perpus/scan-pengembalian.blade.php`
    -   Library kamera: `public/assets/js/html5-qrcode.min.js`
    -   Helper signature: `app/Support/QrPayloadSignature.php`