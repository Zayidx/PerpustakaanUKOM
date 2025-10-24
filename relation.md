# Hubungan Database dalam Aplikasi Perpustakaan Laravel

Dokumen ini menjelaskan semua hubungan database dalam sistem manajemen perpustakaan.

## Gambaran Diagram Hubungan Entitas

### Manajemen Pengguna Inti
- **role_data** (Tabel data peran)
- **users** (Tabel pengguna dengan asosiasi peran)
- **siswa** (Tabel siswa dengan asosiasi pengguna)
- **guru** (Tabel guru dengan asosiasi pengguna)
- **petugas** (Tabel staf perpustakaan dengan asosiasi pengguna)

### Manajemen Perpustakaan
- **kategori_buku** (Kategori buku)
- **authors** (Penulis buku)
- **penerbit** (Penerbit)
- **buku** (Buku dengan semua asosiasi)
- **peminjaman_data** (Catatan peminjaman)
- **peminjaman_items** (Hubungan item peminjaman)

### Fitur Tambahan
- **kelas** (Kelas)
- **jurusan** (Jurusan/Departemen)
- **kategori_pengumuman** (Kategori pengumuman)
- **pengumuman** (Pengumuman)
- **kategori_acara** (Kategori acara)
- **acara** (Acara)

## Hubungan Terperinci

### 1. Data Peran (role_data)
**Deskripsi Tabel**: Menyimpan informasi peran pengguna (Admin, Guru, Siswa, dll.)

**Kolom**:
- `id` (Kunci Utama)
- `nama_role` (Nama peran)
- `deskripsi_role` (Deskripsi peran)
- `icon_role` (Ikon peran)
- `timestamps`

**Hubungan**:
- **Satu-ke-Banyak**: `role_data` → `users` (Satu peran dapat memiliki banyak pengguna)
  - Kunci Asing: `role_data.id` → `users.role_id`

---

### 2. Pengguna (users)
**Deskripsi Tabel**: Tabel autentikasi pengguna utama

**Kolom**:
- `id` (Kunci Utama)
- `nama_user` (Nama pengguna)
- `email_user` (Email pengguna - unik)
- `phone_number` (Nomor telepon)
- `password` (Kata sandi yang di-hash)
- `remember_token` (Token untuk sesi)
- `timestamps`
- `role_id` (Kunci asing ke role_data)

**Hubungan**:
- **Banyak-ke-Satu**: `users` → `role_data` (Banyak pengguna termasuk dalam satu peran)
  - Kunci Asing: `users.role_id` → `role_data.id` (onDelete: cascade)
- **Satu-ke-Satu**: `users` → `siswa` (Satu pengguna dapat menjadi satu siswa)
  - Kunci Asing: `users.id` → `siswa.user_id` (onDelete: cascade)
- **Satu-ke-Satu**: `users` → `guru` (Satu pengguna dapat menjadi satu guru)
  - Kunci Asing: `users.id` → `guru.user_id` (onDelete: cascade)
- **Satu-ke-Satu**: `users` → `petugas` (Satu pengguna dapat menjadi satu staf)
  - Kunci Asing: `users.id` → `petugas.user_id` (onDelete: cascade)
- **Satu-ke-Banyak**: `users` → `pengumuman` (Satu pengguna dapat membuat banyak pengumuman)
  - Kunci Asing: `users.id` → `pengumuman.admin_id` (onDelete: cascade)
- **Satu-ke-Banyak**: `users` → `acara` (Satu pengguna dapat membuat banyak acara)
  - Kunci Asing: `users.id` → `acara.admin_id` (onDelete: cascade)

---

### 3. Siswa (siswa)
**Deskripsi Tabel**: Informasi spesifik siswa

**Kolom**:
- `id` (Kunci Utama)
- `user_id` (Kunci asing ke users)
- `kelas_id` (Kunci asing ke kelas - boleh kosong)
- `jurusan_id` (Kunci asing ke jurusan - boleh kosong)
- `nisn` (ID Siswa - unik)
- `nis` (Nomor siswa - unik)
- `alamat` (Alamat)
- `jenis_kelamin` (Jenis kelamin: laki-laki|perempuan)
- `foto` (Path foto)
- `timestamps`

**Hubungan**:
- **Banyak-ke-Satu**: `siswa` → `users` (Banyak siswa termasuk dalam satu pengguna)
  - Kunci Asing: `siswa.user_id` → `users.id` (onDelete: cascade)
- **Banyak-ke-Satu**: `siswa` → `kelas` (Banyak siswa termasuk dalam satu kelas)
  - Kunci Asing: `siswa.kelas_id` → `kelas.id` (onDelete: null)
- **Banyak-ke-Satu**: `siswa` → `jurusan` (Banyak siswa termasuk dalam satu jurusan)
  - Kunci Asing: `siswa.jurusan_id` → `jurusan.id` (onDelete: null)
- **Satu-ke-Banyak**: `siswa` → `peminjaman_data` (Satu siswa dapat memiliki banyak peminjaman)
  - Kunci Asing: `siswa.id` → `peminjaman_data.siswa_id` (onDelete: cascade)

---

### 4. Guru (guru)
**Deskripsi Tabel**: Informasi spesifik guru

**Kolom**:
- `id` (Kunci Utama)
- `user_id` (Kunci asing ke users)
- `nip` (Nomor ID guru)
- `mata_pelajaran` (Mata pelajaran yang diajar)
- `jenis_kelamin` (Jenis kelamin: Laki-laki|Perempuan)
- `alamat` (Alamat)
- `foto` (Path foto)
- `timestamps`

**Hubungan**:
- **Banyak-ke-Satu**: `guru` → `users` (Banyak guru termasuk dalam satu pengguna)
  - Kunci Asing: `guru.user_id` → `users.id` (onDelete: cascade)
- **Satu-ke-Banyak**: `guru` → `peminjaman_data` (Satu guru dapat menyetujui banyak peminjaman)
  - Kunci Asing: `guru.id` → `peminjaman_data.guru_id` (onDelete: null)

---

### 5. Staf Perpustakaan (petugas)
**Deskripsi Tabel**: Informasi staf perpustakaan

**Kolom**:
- `id` (Kunci Utama)
- `user_id` (Kunci asing ke users)
- `nip` (Nomor ID staf - unik)
- `alamat` (Alamat)
- `jenis_kelamin` (Jenis kelamin: laki-laki|perempuan)
- `foto` (Path foto)
- `timestamps`

**Hubungan**:
- **Banyak-ke-Satu**: `petugas` → `users` (Banyak staf termasuk dalam satu pengguna)
  - Kunci Asing: `petugas.user_id` → `users.id` (onDelete: cascade)

---

### 6. Kelas (kelas)
**Deskripsi Tabel**: Informasi kelas akademik

**Kolom**:
- `id` (Kunci Utama)
- `nama_kelas` (Nama kelas - unik)
- `tingkat` (Tingkat)
- `timestamps`

**Hubungan**:
- **Satu-ke-Banyak**: `kelas` → `siswa` (Satu kelas dapat memiliki banyak siswa)
  - Kunci Asing: `kelas.id` → `siswa.kelas_id`

---

### 7. Jurusan (jurusan)
**Deskripsi Tabel**: Informasi jurusan/departemen akademik

**Kolom**:
- `id` (Kunci Utama)
- `nama_jurusan` (Nama jurusan - unik)
- `deskripsi` (Deskripsi)
- `timestamps`

**Hubungan**:
- **Satu-ke-Banyak**: `jurusan` → `siswa` (Satu jurusan dapat memiliki banyak siswa)
  - Kunci Asing: `jurusan.id` → `siswa.jurusan_id`

---

### 8. Kategori Buku (kategori_buku)
**Deskripsi Tabel**: Klasifikasi kategori buku

**Kolom**:
- `id` (Kunci Utama)
- `nama_kategori_buku` (Nama kategori)
- `deskripsi_kategori_buku` (Deskripsi kategori)
- `timestamps`

**Hubungan**:
- **Satu-ke-Banyak**: `kategori_buku` → `buku` (Satu kategori dapat memiliki banyak buku)
  - Kunci Asing: `kategori_buku.id` → `buku.kategori_id` (onDelete: cascade)

---

### 9. Penulis (authors)
**Deskripsi Tabel**: Informasi penulis buku

**Kolom**:
- `id` (Kunci Utama)
- `nama_author` (Nama penulis)
- `email_author` (Email penulis)
- `no_telp` (Nomor telepon)
- `alamat` (Alamat)
- `foto` (Path foto)
- `timestamps`

**Hubungan**:
- **Satu-ke-Banyak**: `authors` → `buku` (Satu penulis dapat menulis banyak buku)
  - Kunci Asing: `authors.id` → `buku.author_id` (onDelete: cascade)

---

### 10. Penerbit (penerbit)
**Deskripsi Tabel**: Informasi penerbit buku

**Kolom**:
- `id` (Kunci Utama)
- `nama_penerbit` (Nama penerbit)
- `deskripsi` (Deskripsi)
- `logo` (Path logo)
- `tahun_hakcipta` (Tahun hak cipta)
- `timestamps`

**Hubungan**:
- **Satu-ke-Banyak**: `penerbit` → `buku` (Satu penerbit dapat menerbitkan banyak buku)
  - Kunci Asing: `penerbit.id` → `buku.penerbit_id` (onDelete: null)

---

### 11. Buku (buku)
**Deskripsi Tabel**: Informasi inventaris buku

**Kolom**:
- `id` (Kunci Utama)
- `nama_buku` (Judul buku)
- `author_id` (Kunci asing ke authors)
- `kategori_id` (Kunci asing ke kategori_buku)
- `penerbit_id` (Kunci asing ke penerbit - boleh kosong)
- `deskripsi` (Deskripsi buku)
- `tanggal_terbit` (Tanggal terbit)
- `cover_depan` (Path cover depan)
- `cover_belakang` (Path cover belakang)
- `stok` (Jumlah stok)
- `timestamps`

**Hubungan**:
- **Banyak-ke-Satu**: `buku` → `authors` (Banyak buku memiliki satu penulis)
  - Kunci Asing: `buku.author_id` → `authors.id` (onDelete: cascade)
- **Banyak-ke-Satu**: `buku` → `kategori_buku` (Banyak buku termasuk dalam satu kategori)
  - Kunci Asing: `buku.kategori_id` → `kategori_buku.id` (onDelete: cascade)
- **Banyak-ke-Satu**: `buku` → `penerbit` (Banyak buku memiliki satu penerbit)
  - Kunci Asing: `buku.penerbit_id` → `penerbit.id` (onDelete: null)
- **Banyak-ke-Banyak** (melalui peminjaman_items): `buku` ↔ `peminjaman_data`
  - Koneksi: `buku.id` ↔ `peminjaman_items.buku_id` ↔ `peminjaman_data.id`

---

### 12. Catatan Peminjaman (peminjaman_data)
**Deskripsi Tabel**: Catatan transaksi peminjaman buku

**Kolom**:
- `id` (Kunci Utama)
- `kode` (Kode peminjaman unik)
- `siswa_id` (Kunci asing ke siswa)
- `guru_id` (Kunci asing ke guru - boleh kosong)
- `status` (Status: pending|accepted|returned|cancelled)
- `accepted_at` (Timestamp penerimaan)
- `due_at` (Tanggal jatuh tempo)
- `returned_at` (Timestamp pengembalian)
- `metadata` (Metadata JSON)
- `timestamps`

**Hubungan**:
- **Banyak-ke-Satu**: `peminjaman_data` → `siswa` (Banyak peminjaman termasuk dalam satu siswa)
  - Kunci Asing: `peminjaman_data.siswa_id` → `siswa.id` (onDelete: cascade)
- **Banyak-ke-Satu**: `peminjaman_data` → `guru` (Banyak peminjaman disetujui oleh satu guru)
  - Kunci Asing: `peminjaman_data.guru_id` → `guru.id` (onDelete: null)
- **Satu-ke-Banyak**: `peminjaman_data` → `peminjaman_items` (Satu peminjaman dapat memiliki banyak item)
  - Kunci Asing: `peminjaman_data.id` → `peminjaman_items.peminjaman_id` (onDelete: cascade)

---

### 13. Item Peminjaman (peminjaman_items)
**Deskripsi Tabel**: Item buku individual dalam suatu peminjaman

**Kolom**:
- `id` (Kunci Utama)
- `peminjaman_id` (Kunci asing ke peminjaman_data)
- `buku_id` (Kunci asing ke buku)
- `quantity` (Jumlah buku yang dipinjam)
- `timestamps`
- Batasan unik: (`peminjaman_id`, `buku_id`)

**Hubungan**:
- **Banyak-ke-Satu**: `peminjaman_items` → `peminjaman_data` (Banyak item termasuk dalam satu peminjaman)
  - Kunci Asing: `peminjaman_items.peminjaman_id` → `peminjaman_data.id` (onDelete: cascade)
- **Banyak-ke-Satu**: `peminjaman_items` → `buku` (Banyak item merujuk ke satu buku)
  - Kunci Asing: `peminjaman_items.buku_id` → `buku.id` (onDelete: cascade)

---

### 14. Kategori Pengumuman (kategori_pengumuman)
**Deskripsi Tabel**: Kategori untuk pengumuman

**Kolom**:
- `id` (Kunci Utama)
- `nama` (Nama kategori - unik)
- `deskripsi` (Deskripsi)
- `timestamps`

**Hubungan**:
- **Satu-ke-Banyak**: `kategori_pengumuman` → `pengumuman` (Satu kategori dapat memiliki banyak pengumuman)
  - Kunci Asing: `kategori_pengumuman.id` → `pengumuman.kategori_pengumuman_id` (onDelete: cascade)

---

### 15. Pengumuman (pengumuman)
**Deskripsi Tabel**: Pengumuman sistem

**Kolom**:
- `id` (Kunci Utama)
- `judul` (Judul)
- `slug` (Slug URL - unik)
- `kategori_pengumuman_id` (Kunci asing ke kategori_pengumuman)
- `admin_id` (Kunci asing ke users)
- `thumbnail_url` (Path thumbnail)
- `thumbnail_caption` (Keterangan thumbnail)
- `konten` (Konten dalam markdown)
- `status` (Status: draft|published)
- `published_at` (Timestamp publikasi)
- `timestamps`

**Hubungan**:
- **Banyak-ke-Satu**: `pengumuman` → `kategori_pengumuman` (Banyak pengumuman termasuk dalam satu kategori)
  - Kunci Asing: `pengumuman.kategori_pengumuman_id` → `kategori_pengumuman.id` (onDelete: cascade)
- **Banyak-ke-Satu**: `pengumuman` → `users` (Banyak pengumuman dibuat oleh satu pengguna/admin)
  - Kunci Asing: `pengumuman.admin_id` → `users.id` (onDelete: cascade)

---

### 16. Kategori Acara (kategori_acara)
**Deskripsi Tabel**: Kategori untuk acara

**Kolom**:
- `id` (Kunci Utama)
- `nama` (Nama kategori - unik)
- `slug` (Slug URL - unik)
- `deskripsi` (Deskripsi)
- `timestamps`

**Hubungan**:
- **Satu-ke-Banyak**: `kategori_acara` → `acara` (Satu kategori dapat memiliki banyak acara)
  - Kunci Asing: `kategori_acara.id` → `acara.kategori_acara_id` (onDelete: cascade)

---

### 17. Acara (acara)
**Deskripsi Tabel**: Acara sistem

**Kolom**:
- `id` (Kunci Utama)
- `judul` (Judul)
- `slug` (Slug URL - unik)
- `kategori_acara_id` (Kunci asing ke kategori_acara)
- `admin_id` (Kunci asing ke users)
- `lokasi` (Lokasi)
- `poster_url` (Path poster)
- `deskripsi` (Deskripsi)
- `mulai_at` (Timestamp mulai)
- `selesai_at` (Timestamp selesai - boleh kosong)
- `timestamps`

**Hubungan**:
- **Banyak-ke-Satu**: `acara` → `kategori_acara` (Banyak acara termasuk dalam satu kategori)
  - Kunci Asing: `acara.kategori_acara_id` → `kategori_acara.id` (onDelete: cascade)
- **Banyak-ke-Satu**: `acara` → `users` (Banyak acara dibuat oleh satu pengguna/admin)
  - Kunci Asing: `acara.admin_id` → `users.id` (onDelete: cascade)

---

## Ringkasan Hubungan Utama

### Hirarki Peran Pengguna
- Pengguna memiliki peran (role_data)
- Pengguna dapat menjadi siswa, guru, atau staf
- Setiap jenis pengguna memiliki hubungan satu-ke-satu dengan tabel users

### Hubungan Manajemen Perpustakaan
- Buku memiliki penulis, kategori, dan penerbit
- Peminjaman diinisiasi oleh siswa dan disetujui oleh guru
- Item peminjaman menghubungkan buku tertentu ke transaksi peminjaman tertentu
- Manajemen stok dipelihara pada tingkat buku

### Hubungan Manajemen Konten
- Pengumuman dan acara dikategorikan
- Baik pengumuman maupun acara dibuat oleh pengguna admin
- Semua konten mendukung status draft/published