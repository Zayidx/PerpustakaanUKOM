## QR Stack Overview (Step by Step)

Fitur peminjaman & pengembalian berbasis QR memanfaatkan dua lapisan:

1. **Generator backend (`simplesoftwareio/simple-qrcode`)** – membuat QR di sisi siswa, baik untuk permintaan pinjam maupun tiket pengembalian.
2. **Scanner frontend (`html5-qrcode`)** – memanfaatkan kamera perangkat guru untuk membaca QR, dengan fallback input PIN enam digit.

Dokumen ini merinci peran masing‑masing, lokasi kode, dan alur integrasinya.

---

### Langkah 1 – Generator QR (simple-qrcode)

| Tujuan | Membuat QR code beserta PIN cadangan untuk siswa |
|--------|--------------------------------------------------|
| Lokasi utama | `app/Livewire/Siswa/KodePinjaman.php` <br> `app/Livewire/Siswa/ListPeminjaman.php` (tiket pengembalian) |
| Paket | [`simplesoftwareio/simple-qrcode`](https://github.com/SimpleSoftwareIO/simple-qrcode) |

Contoh pembuatan QR permintaan pinjam:

```php
$payload = [
    'code' => $loan->kode,          // PIN enam digit
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

Untuk tiket pengembalian (`ListPeminjaman`), payload ditambah flag `action => 'return'` dan informasi keterlambatan. Semua QR langsung disisipkan sebagai SVG di Blade tanpa file fisik.

**Catatan konfigurasi**
- Paket sudah terdaftar di `composer.json`; tidak perlu registrasi manual.
- Ukuran/warna dapat diatur via chaining (`size()`, `color()`, dll).
- PIN enam digit tetap dicetak sebagai teks di bawah QR agar guru bisa mengetik manual.

---

### Langkah 2 – Scanner QR (html5-qrcode)

| Tujuan | Membaca QR via kamera guru + fallback PIN manual |
|--------|--------------------------------------------------|
| Lokasi utama | `resources/views/livewire/guru/scan-peminjaman.blade.php` <br> `resources/views/livewire/guru/scan-pengembalian.blade.php` |
| Library | [`html5-qrcode`](https://github.com/mebjas/html5-qrcode) – disimpan lokal pada `public/assets/js/html5-qrcode.min.js` |

Ringkasan implementasi:

```html
@push('scripts')
    <script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
    <script>
        const loanScanner = {
            instance: null,
            containerId: 'qr-reader',
            initOnce() {
                const container = document.getElementById(this.containerId);
                // validasi elemen & load library

                this.instance = new Html5Qrcode(this.containerId, { verbose: false });
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                this.instance.start(
                    { facingMode: 'environment' },
                    config,
                    (decodedText) => window.dispatchEvent(
                        new CustomEvent('qr-scanned', { detail: [decodedText] })
                    ),
                    () => {}
                ).catch((error) => {
                    window.dispatchEvent(
                        new CustomEvent('qr-scanner-error', { detail: [error?.message ?? String(error)] })
                    );
                });
            },
        };
    </script>
@endpush
```

**Alur kerja scanner**
1. Saat halaman guru dibuka, skrip lokal dimuat (tidak tergantung CDN).
2. `Html5Qrcode.start()` meminta izin kamera (wajib `https://` atau `http://localhost` sebagaimana dijelaskan di `README_NGINX_CAMERA.md`).
3. Setiap QR yang terbaca mengirim event `qr-scanned`. Komponen Livewire `ScanPeminjaman` / `ScanPengembalian` mendengar event tersebut via `#[On('qr-scanned')]`.
4. Payload QR di-`json_decode`, diverifikasi, lalu diproses (update stok, ubah status, catat denda, dll).
5. Bila kamera gagal, guru dapat mengetik PIN enam digit. Method `processManualCode()` memvalidasi `digits:6` dan memanggil alur yang sama (`processLoanData`).

**Catatan tambahan**
- Event `qr-scanner-error` mengisi alert UI ketika kamera ditolak / tidak ada.
- `ScanPengembalian` menahan proses jika ada denda: modal + tombol “Sudah/Belum dibayar” harus ditekan sebelum stok dikembalikan dan penalty dicatat.
- Scanner juga dapat diganti dengan input file (fitur bawaan html5-qrcode) bila ingin menambahkan fallback lain.

---

### Langkah 3 – Alur lengkap

1. **Siswa memilih buku** di `ListBuku`. Metode `generateLoanCode()` membuat peminjaman `pending` dan PIN 6 digit.
2. **QR ditampilkan** di:
   - `KodePinjaman` (permintaan pinjam).
   - `ListPeminjaman` → tombol “Tampilkan” (tiket pengembalian). QR menyertakan `action => 'return'` dan info keterlambatan.
3. **Guru memproses** di dashboard:
   - `ScanPeminjaman`: memindai QR atau memasukkan PIN → stok otomatis dicek dan status berubah ke `accepted`.
   - `ScanPengembalian`: memindai QR/pin; jika telat, guru wajib menekan tombol “Sudah/Belum dibayar” sebelum `completeReturn()` mengembalikan stok & mencatat denda.
4. **Riwayat terlihat** di:
   - Panel detail `ScanPengembalian` (status + info denda).
   - Panel detail `Manajemen Peminjaman` (ringkasan peminjaman + list penalty yang pernah dicatat).
5. **Monitoring lanjutan** dilakukan di halaman `Manajemen Peminjaman` (filter, detail, denda, status).

Dengan pemisahan generator–scanner ini, siswa cukup menunjukkan QR/PIN, sementara guru memiliki dua cara (scan atau input) untuk memproses pinjam/pengembalian tanpa mengetik data lain. Helper tambahan (perhitungan keterlambatan, pencatatan penalty, dsb.) memastikan pengalaman tetap konsisten di kedua jenis transaksi.
