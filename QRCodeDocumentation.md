# QRCodeDocumentation

Bayangkan QR sebagai tiket konser digital: berisi data penumpang dan jadwal, lalu dicap penyelenggara supaya tidak bisa dipalsukan. Dokumen ini menjelaskan cara tiket itu dibuat, distempel, dan dicek dalam fitur peminjaman/pengembalian.

## Alur Generator (lebih naratif)

1. **Kumpulkan data “penumpang”**  
   - Peminjaman: `app/Livewire/Siswa/KodePinjaman.php` memuat pinjaman milik siswa.  
   - Pengembalian: `app/Livewire/Siswa/KodePengembalian.php` memuat pinjaman `accepted/returned` dan menghitung keterlambatan.

2. **Susun tiket (payload JSON)**  
   - Wajib: `code` (PIN 6 digit), `loan_id`, `student_id`, `action` (`borrow|return`), `generated_at` (ISO8601, stabil lintas zona waktu untuk hitung kadaluarsa 30 hari).  
   - Opsional: `admin_perpus_id` (berwenang/nullable), `books` (id+title), `late_days` (khusus return).  
   - Fungsi singkat per field: `code` = PIN juga untuk input manual; `loan_id`/`student_id` = pastikan QR tidak salah sasaran; `admin_perpus_id` = scope admin; `action` = pembeda peminjaman vs pengembalian; `books` = konteks tampilan; `late_days` = info awal denda.  
   - Analogi: menulis nama penumpang, nomor kursi, dan jadwal di tiket.

3. **Beri stempel keamanan (tandatangan digital)**  
   - Helper: `App\Support\QrPayloadSignature::sign($payload)` menambah field `signature`.  
   - Mekanisme: HMAC-SHA256 dengan kunci `app.key` (decode base64 bila perlu) atas JSON kanonis (field penting saja).  
   - Fungsi: admin menghitung ulang + `hash_equals`; 1 karakter berubah → signature gagal → QR ditolak; kunci beda/QR kadaluarsa juga ditolak.  
   - Analogi: cap hologram di tiket yang langsung ketahuan palsu saat diperiksa.

4. **Cetak tiket jadi QR**  
   - Library: `SimpleSoftwareIO\QrCode\Facades\QrCode`.  
   - Format: SVG, ukuran 240, margin 2.  
   - Contoh: `QrCode::format('svg')->size(240)->margin(2)->generate(json_encode($payload))`.  
   - Analogi: mengubah teks tiket menjadi kode kotak-kotak yang bisa dipindai.

5. **Serahkan ke penumpang (tampilan siswa)**  
   - Peminjaman: QR/kode tampil saat `pending`; disembunyikan saat `accepted/returned/cancelled` dan diganti pesan “QR tidak diperlukan”.  
   - Pengembalian: QR/kode tampil saat `accepted`; disembunyikan saat `returned`.  
   - Analogi: tiket hanya berguna sebelum pintu masuk; setelah itu disobek.

6. **Pantau status otomatis**  
   - Peminjaman: `wire:poll.2s.keep-alive="refreshLoan"` selalu aktif, jadi status berubah otomatis setelah admin memindai.  
   - Pengembalian: polling 2 detik saat status masih `accepted`.  
   - Analogi: layar tiket memperbarui sendiri ketika petugas memindai di gate.

7. **Berlaku terbatas waktu**  
   - Validasi di admin menolak QR lebih dari 30 hari dari `generated_at`.  
   - Analogi: tiket konser kadaluarsa setelah tanggal acara.

8. **Pemeriksaan ketat di gerbang (sisi admin)**  
   - Cek signature HMAC, usia QR, `action` sesuai halaman, kecocokan `loan_id`/`student_id`, dan scope `admin_perpus_id` (harus milik admin login atau masih null).  
   - Jika salah satu tidak cocok, scan ditolak.  
   - Analogi: petugas memeriksa hologram, nama, dan tanggal tiket sebelum mempersilakan masuk.

## Step-by-step (ringkas bergambar kata)
1) **Ambil data pinjaman** → load pinjaman siswa (peminjaman/pengembalian) dari DB.  
2) **Susun payload JSON** → isi `code`, `loan_id`, `student_id`, `action`, `generated_at`, dll.  
3) **Stempel digital** → `App\Support\QrPayloadSignature::sign($payload)` menambah `signature` HMAC-SHA256 (`app.key`).  
4) **Cetak QR** → `QrCode::format('svg')->size(240)->margin(2)->generate(json_encode($payload))` dengan `SimpleSoftwareIO\QrCode\Facades\QrCode`.  
5) **Tampilkan di halaman siswa** → hanya saat status aktif (pending untuk peminjaman, accepted untuk pengembalian); sembunyikan bila sudah diproses.  
6) **Pantau status otomatis** → Livewire `wire:poll.2s.keep-alive="refreshLoan"` menarik status terbaru tanpa refresh manual.  
7) **Scan oleh admin** → `html5-qrcode` menangkap QR, kirim event `qr-scanned` ke komponen Livewire admin.  
8) **Verifikasi & aksi** → admin cek signature/usia/scope, lalu:  
   - Peminjaman: lock stok, kurangi stok, set `accepted_at/due_at`, ubah status `accepted`.  
   - Pengembalian: hitung denda, pilih sudah/belum dibayar, kembalikan stok, set status `returned`.  
9) **Notifikasi & bersih-bersih** → siswa melihat status/pesan otomatis; QR/kode disembunyikan saat selesai, pesan “QR tidak diperlukan” tampil.

## File Penting
- Generator peminjaman: `app/Livewire/Siswa/KodePinjaman.php`
- Generator pengembalian: `app/Livewire/Siswa/KodePengembalian.php`
- Penandatangan: `app/Support/QrPayloadSignature.php`
- View siswa: `resources/views/livewire/siswa/kode-pinjaman.blade.php`, `resources/views/livewire/siswa/kode-pengembalian.blade.php`
