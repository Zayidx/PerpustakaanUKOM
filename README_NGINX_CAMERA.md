## Menjalankan Laravel + Kamera di Perangkat Lain (Nginx + HTTPS)

Halaman **Scan Peminjaman** membutuhkan akses kamera. Browser hanya mengizinkan `getUserMedia` jika aplikasi diakses melalui *secure origin* (HTTPS atau `http://localhost`). Dokumen ini menjelaskan cara men-deploy proyek ini ke Nginx dengan HTTPS lokal supaya perangkat lain (HP/laptop guru) bisa memindai QR code.

### 1. Siapkan dependensi

```bash
sudo apt update
sudo apt install nginx php-fpm libnss3-tools
```

> Pastikan versi PHP-FPM cocok (mis. `/run/php/php8.2-fpm.sock`). Sesuaikan pada konfigurasi nanti.

### 2. Buat sertifikat lokal via `mkcert`

```bash
mkcert -install
mkcert perpustakaan.local "*.perpustakaan.local"

sudo mkdir -p /etc/ssl/perpustakaan
sudo mv perpustakaan.local+1*.pem /etc/ssl/perpustakaan/
```

`mkcert` otomatis memasang CA lokal di mesin ini. Untuk perangkat lain, import file `rootCA.pem` yang dibuat mkcert agar sertifikat dipercaya (lihat langkah 6).

### 3. Tambahkan domain ke hosts

Pada server (dan setiap klien):

```bash
echo "192.168.1.10 perpustakaan.local" | sudo tee -a /etc/hosts
```

Ganti `192.168.1.10` dengan IP LAN server tempat Laravel berjalan.

### 4. Konfigurasi virtual host Nginx

`/etc/nginx/sites-available/perpustakaan.conf`

```nginx
server {
    listen 80;
    server_name perpustakaan.local;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name perpustakaan.local;

    ssl_certificate     /etc/ssl/perpustakaan/perpustakaan.local+1.pem;
    ssl_certificate_key /etc/ssl/perpustakaan/perpustakaan.local+1-key.pem;

    root /home/zayidx/Documents/perpus/perpustakaan/public;
    index index.php index.html;

    add_header X-Frame-Options SAMEORIGIN;
    add_header X-Content-Type-Options nosniff;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock; # sesuaikan versi PHP
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan dan uji:

```bash
sudo ln -s /etc/nginx/sites-available/perpustakaan.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. Sinkronkan konfigurasi Laravel

1. Set `.env`:
   ```
   APP_URL=https://perpustakaan.local
   SESSION_DOMAIN=perpustakaan.local
   ```
2. Hapus cache konfigurasi:
   ```bash
   php artisan config:clear
   php artisan route:clear
   ```
3. Pastikan `storage/` dan `bootstrap/cache/` writable oleh user `www-data`.

### 6. Import sertifikat ke perangkat lain

Perangkat yang **tidak** menjalankan mkcert belum mengenali CA lokal:

- **Windows/macOS/Linux**: salin `~/.local/share/mkcert/rootCA.pem` (Linux) atau `~/Library/Application Support/mkcert/rootCA.pem` (macOS) ke perangkat lain, lalu import ke *Trusted Root Certification Authorities*.
- **Android**: kirim file `rootCA.pem` → Settings → Security → Install certificates → CA certificate.
- **iOS**: gunakan AirDrop/email, buka pengaturan → General → About → Certificate Trust Settings → aktifkan.

Tanpa import ini browser akan menganggap sertifikat tidak valid dan tetap memblokir kamera.

### 7. Akses dari perangkat lain

1. Buka `https://perpustakaan.local` (pastikan ikon gembok hijau).
2. Browser akan memunculkan permintaan izin kamera. Pilih **Allow**.
3. Halaman **Scan Peminjaman** kini bisa memulai `Html5Qrcode` dan memindai QR/ barcode.

> Jika kamera tetap tidak muncul: pastikan URL menggunakan HTTPS, sertifikat dipercaya, dan perangkat memiliki kamera yang bisa diakses browser (Chrome/Edge/Firefox terbaru).

### 8. Operasional tambahan

- Jalankan queue atau scheduler sesuai kebutuhan:
  ```bash
  php artisan queue:listen
  php artisan schedule:work
  ```
- Gunakan `sudo journalctl -u nginx -f` untuk memantau log Nginx jika terjadi error.
- Untuk domain publik, ganti `mkcert` dengan sertifikat resmi (Let’s Encrypt) dan update `ssl_certificate` serta `hosts`.

Dengan konfigurasi ini, semua perangkat pada jaringan dapat mengakses aplikasi melalui HTTPS sehingga fitur kamera bekerja sesuai harapan.

