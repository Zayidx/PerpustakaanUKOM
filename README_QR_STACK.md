## QR Stack Overview

Fitur peminjaman memanfaatkan dua komponen berbeda:

1. **Backend QR generator** – paket [`simplesoftwareio/simple-qrcode`](https://github.com/SimpleSoftwareIO/simple-qrcode) digunakan untuk membuat QR code yang ditampilkan ke siswa.
2. **Frontend QR scanner** – library JavaScript [`html5-qrcode`](https://github.com/mebjas/html5-qrcode) memanfaatkan kamera perangkat guru untuk membaca QR atau input manual sebagai cadangan.

Dokumen ini menjelaskan cara kerja keduanya dan referensi kode terkait.

---

### 1. simple-qrcode (backend)

**Lokasi utama**: `app/Livewire/Siswa/KodePinjaman.php`

```php
use SimpleSoftwareIO\QrCode\Facades\QrCode;

$payload = [
    'code' => $loan->kode,
    'loan_id' => $loan->id,
    'student_id' => $loan->siswa_id,
    'books' => $loan->items->map(fn ($item) => [
        'id' => $item->buku_id,
        'title' => $item->buku->nama_buku,
    ])->values()->all(),
    'generated_at' => $loan->created_at?->toIso8601String(),
];

$this->qrSvg = QrCode::format('svg')
    ->size(240)
    ->margin(2)
    ->generate(json_encode($payload, JSON_THROW_ON_ERROR));
```

**Alur kerja**:

1. Komponen Livewire siswa (`KodePinjaman`) memuat data peminjaman dan menyiapkan payload JSON berisi kode 6 digit, ID peminjaman, ID siswa, dan daftar buku.
2. `QrCode::format('svg')` menghasilkan gambar SVG secara dinamis (tanpa menyimpan file) yang langsung disisipkan di Blade (`resources/views/livewire/siswa/kode-pinjaman.blade.php`).
3. Guru dapat memindai QR tersebut; payload JSON akan dibaca oleh scanner (lihat bagian html5-qrcode).

**Konfigurasi**:

- Paket sudah terdaftar melalui `composer.json` (`"simplesoftwareio/simple-qrcode": "^4.2"`).
- Tidak memerlukan service provider tambahan karena Laravel auto-discover.
- Opsional: ukuran, warna, logo dapat diubah dengan chaining method `color()`, `backgroundColor()`, dsb.

---

### 2. html5-qrcode (frontend scanner)

**Lokasi utama**: `resources/views/livewire/guru/scan-peminjaman.blade.php`

```html
@push('scripts')
    <script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
    <script>
        const dispatchLivewireEvent = (eventName, ...payload) => {
            window.dispatchEvent(new CustomEvent(eventName, { detail: payload }));
        };

        const loanScanner = {
            instance: null,
            containerId: 'qr-reader',
            initOnce() {
                const container = document.getElementById(this.containerId);
                // ... validasi elemen & library

                this.instance = new Html5Qrcode(this.containerId, { verbose: false });
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                this.instance.start(
                    { facingMode: 'environment' },
                    config,
                    (decodedText) => dispatchLivewireEvent('qr-scanned', decodedText),
                    () => {}
                ).catch((error) => {
                    dispatchLivewireEvent('qr-scanner-error', error?.message ?? String(error));
                });
            },
        };
    </script>
@endpush
```

**Alur kerja**:

1. Saat halaman guru dimuat, script lokal `public/assets/js/html5-qrcode.min.js` di-load sehingga tidak tergantung CDN.
2. `Html5Qrcode.start()` meminta izin kamera. Jika sukses, setiap QR yang terbaca diparsing ke callback `(decodedText) => ...`.
3. Callback mengirim event `qr-scanned` melalui `CustomEvent`. Livewire component `App\Livewire\Guru\ScanPeminjaman` mendengarkan event tersebut via attribute `#[On('qr-scanned')]`.
4. `ScanPeminjaman::handleScan()` mem-`json_decode` payload, mencari data peminjaman berdasarkan `code`, dan jika status `pending` akan mengurangi stok buku + mengubah status menjadi `accepted`.
5. Jika kamera tidak tersedia, guru bisa memasukkan **kode 6 angka** lewat form manual. Metode `processManualCode()` memvalidasi `digits:6` dan memanggil helper yang sama (`processLoanData`) untuk mengeksekusi alur konfirmasi.

**Catatan penting**:

- Browser hanya mengizinkan kamera pada `https://` atau `http://localhost`. Gunakan konfigurasi Nginx HTTPS (lihat `README_NGINX_CAMERA.md`) agar perangkat lain dapat memakai kamera.
- Pesan error dari scanner diteruskan ke Livewire lewat event `qr-scanner-error`. Komponen menampilkan alert ketika kamera tidak tersedia.
- Library `html5-qrcode` mendukung fallback file input; jika diperlukan, bisa menambahkan tombol `Html5QrcodeScanner` bawaan.

---

### 3. Ikhtisar Flow

1. **Siswa** memilih buku → `ListBuku` membuat peminjaman `pending` + kode numeric 6 digit (`generateUniqueCode()`).
2. **Siswa** membuka halaman kode → `simple-qrcode` membentuk QR + menampilkan kode manual.
3. **Guru** membuka halaman scan:
   - Kamera aktif via `html5-qrcode`. QR dipindai → payload JSON dikirim ke Livewire.
   - Jika kamera gagal, guru memasukkan kode 6 digit secara manual.
4. **ScanPeminjaman** memproses permintaan:
   - Validasi stok → decrement stok buku → set status `accepted`, `accepted_at`, `due_at`, `guru_id`.
   - Menampilkan detail peminjaman di panel kanan.
5. Guru dapat memonitor seluruh peminjaman di halaman `Manajemen Peminjaman`.

Dengan kombinasi simple-qrcode (generator) dan html5-qrcode (scanner), alur peminjaman menjadi end-to-end: siswa tinggal menunjukkan QR/kode angka, guru memprosesnya melalui kamera atau input manual.

