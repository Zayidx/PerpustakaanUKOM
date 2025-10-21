<?php

namespace Database\Seeders;

use App\Models\KategoriPengumuman;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PengumumanSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['nama' => 'Layanan & Fasilitas', 'deskripsi' => 'Informasi layanan dan fasilitas terbaru perpustakaan.'],
            ['nama' => 'Kegiatan & Acara', 'deskripsi' => 'Agenda kegiatan yang diadakan oleh perpustakaan.'],
            ['nama' => 'Pengumuman Umum', 'deskripsi' => 'Informasi penting lain seputar perpustakaan.'],
            ['nama' => 'Kebijakan & Regulasi', 'deskripsi' => 'Pembaharuan kebijakan dan aturan perpustakaan.'],
            ['nama' => 'Perawatan Koleksi', 'deskripsi' => 'Perawatan berkala untuk menjaga koleksi tetap prima.'],
        ])->mapWithKeys(function ($data) {
            $kategori = KategoriPengumuman::firstOrCreate(
                ['nama' => $data['nama']],
                ['deskripsi' => $data['deskripsi']]
            );

            return [$data['nama'] => $kategori->id];
        });

        $adminUsers = User::query()
            ->whereHas('role', fn ($query) => $query->whereIn('nama_role', ['Admin', 'Administrator']))
            ->take(15)
            ->get();

        if ($adminUsers->isEmpty()) {
            $this->command?->warn('PengumumanSeeder: tidak menemukan pengguna dengan role Admin/Administrator. Melewatkan seeder pengumuman.');
            return;
        }

        if ($adminUsers->count() < 15) {
            $firstRoleId = $adminUsers->first()->role_id;
            for ($i = $adminUsers->count(); $i < 15; $i++) {
                $name = 'Admin Perpus ' . ($i + 1);
                $adminUsers->push(User::create([
                    'nama_user' => $name,
                    'email_user' => Str::slug($name) . '@perpus.local',
                    'phone_number' => '0812' . str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'password' => 'password',
                    'role_id' => $firstRoleId,
                ]));
            }
        }

        $image = 'https://www.nintendo.com/eu/media/images/10_share_images/games_15/nintendo_switch_4/2x1_NSwitch_Minecraft.jpg';

        $announcements = [
            [
                'judul' => 'Katalog Digital Kini Terintegrasi dengan Koleksi Cetak',
                'kategori' => 'Layanan & Fasilitas',
                'thumbnail_caption' => 'Tampilan baru katalog digital perpustakaan.',
                'konten' => <<<'MD'
Kami dengan senang hati menginformasikan bahwa katalog digital perpustakaan kini terintegrasi penuh dengan koleksi cetak. Beberapa poin penting:

- Pencarian buku menjadi lebih cepat dengan filter lokasi rak dan status ketersediaan.
- Pengguna dapat langsung mengajukan permintaan peminjaman melalui katalog.
- Riwayat pencarian akan tersimpan sehingga memudahkan referensi di kunjungan berikutnya.

Silakan kunjungi [katalog digital perpustakaan](https://perpus.example.com) untuk mencobanya. Tim kami siap membantu jika Anda membutuhkan panduan pengguna.
MD,
                'published_at' => now()->subDays(2)->setTime(9, 30),
            ],
            [
                'judul' => 'Workshop Literasi Informasi: Menjelajah Sumber Referensi Terpercaya',
                'kategori' => 'Kegiatan & Acara',
                'thumbnail_caption' => 'Workshop literasi informasi untuk pelajar tingkat akhir.',
                'konten' => <<<'MD'
Perpustakaan akan menyelenggarakan *Workshop Literasi Informasi* pada **Sabtu, 23 November 2024** pukul 09.00 - 12.00 di ruang multimedia lantai 2. Materi yang dibahas meliputi:

1. Teknik menilai kredibilitas sumber daring.
2. Cara menggunakan basis data jurnal akademik.
3. Strategi menyusun sitasi dengan aplikasi pengelola referensi.

Kuota peserta terbatas untuk 30 orang. Daftarkan diri Anda melalui tautan pendaftaran yang tersedia di meja layanan atau hubungi petugas perpustakaan.
MD,
                'published_at' => now()->subDays(4)->setTime(14, 0),
            ],
            [
                'judul' => 'Pembaruan Jam Operasional Selama Periode Ujian',
                'kategori' => 'Pengumuman Umum',
                'thumbnail_caption' => 'Jam operasional perpustakaan diperpanjang selama periode ujian semester.',
                'konten' => <<<'MD'
Untuk mendukung kebutuhan belajar selama *Ujian Akhir Semester*, perpustakaan akan memperpanjang jam operasional mulai **18 November hingga 6 Desember 2024**:

- Senin hingga Jumat: 07.30 - 20.30
- Sabtu: 09.00 - 18.00
- Minggu dan hari libur tetap tutup

Area diskusi kelompok tetap dibatasi untuk menjaga kenyamanan bersama. Mohon menjaga ketertiban dan kebersihan selama berada di perpustakaan.
MD,
                'published_at' => now()->subDays(6)->setTime(11, 15),
            ],
            [
                'judul' => 'Program Donasi Buku: Satu Buku untuk Banyak Inspirasi',
                'kategori' => 'Kegiatan & Acara',
                'thumbnail_caption' => 'Mari berbagi inspirasi melalui donasi buku berkualitas.',
                'konten' => <<<'MD'
Program donasi buku kembali digelar! Kami mengundang seluruh warga sekolah untuk berpartisipasi menyumbangkan buku bacaan inspiratif. Ketentuan donasi:

- Buku fiksi dan non-fiksi dengan kondisi baik (tidak robek/berjamur).
- Terbitan maksimal 10 tahun terakhir.
- Setiap donatur akan mendapatkan *bookmark* edisi khusus perpustakaan.

Donasi dapat diserahkan langsung di meja layanan perpustakaan hingga **30 November 2024**. Terima kasih atas dukungan Anda!
MD,
                'published_at' => now()->subDays(8)->setTime(8, 45),
            ],
            [
                'judul' => 'Penataan Ulang Zona Baca dengan Konsep Tematik',
                'kategori' => 'Layanan & Fasilitas',
                'thumbnail_caption' => 'Zona baca tematik membuat pengalaman membaca lebih nyaman.',
                'konten' => <<<'MD'
Zona baca perpustakaan kini hadir dengan konsep tematik berdasarkan genre buku. Setiap zona dilengkapi dekorasi, pencahayaan, dan rekomendasi bacaan sesuai tema. Kami juga menambahkan musik instrumental lembut pada jam tertentu agar suasana membaca semakin menyenangkan.
MD,
                'published_at' => now()->subDays(9)->setTime(10, 30),
            ],
            [
                'judul' => 'Sosialisasi Kebijakan Peminjaman Koleksi Langka',
                'kategori' => 'Kebijakan & Regulasi',
                'thumbnail_caption' => 'Tatacara baru peminjaman koleksi langka perpustakaan.',
                'konten' => <<<'MD'
Mulai bulan ini, peminjaman koleksi langka dilakukan melalui prosedur verifikasi identitas tambahan. Peminjam wajib mengisi formulir khusus, menyertakan kartu pelajar, dan mengembalikan buku dalam waktu maksimal tiga hari kerja. Keterlambatan akan dikenakan denda khusus.
MD,
                'published_at' => now()->subDays(11)->setTime(13, 45),
            ],
            [
                'judul' => 'Pelatihan Mandiri Menggunakan Database Jurnal Internasional',
                'kategori' => 'Kegiatan & Acara',
                'thumbnail_caption' => 'Pelatihan akses jurnal internasional secara mandiri.',
                'konten' => <<<'MD'
Kami meluncurkan modul pelatihan mandiri untuk membantu anggota mengakses database jurnal internasional. Modul ini mencakup cara mencari artikel berdasarkan kata kunci, memanfaatkan filter relevansi, serta mengekspor sitasi otomatis. Modul dapat diakses melalui portal e-learning perpustakaan.
MD,
                'published_at' => now()->subDays(12)->setTime(15, 20),
            ],
            [
                'judul' => 'Perawatan Koleksi: Jadwal Fumigasi Mingguan',
                'kategori' => 'Perawatan Koleksi',
                'thumbnail_caption' => 'Jadwal fumigasi untuk menjaga koleksi bebas hama.',
                'konten' => <<<'MD'
Untuk menjaga koleksi tetap awet， perpustakaan akan melakukan fumigasi ringan setiap Jumat sore pukul 15.00. Saat proses berlangsung, area penyimpanan utama akan dibatasi sementara dan koleksi tertentu tidak dapat dipinjam. Mohon pengertiannya.
MD,
                'published_at' => now()->subDays(13)->setTime(16, 0),
            ],
            [
                'judul' => 'Pengumuman Pemenang Lomba Resensi Buku 2024',
                'kategori' => 'Pengumuman Umum',
                'thumbnail_caption' => 'Selamat kepada pemenang lomba resensi buku tahun ini.',
                'konten' => <<<'MD'
Selamat kepada para pemenang lomba resensi buku 2024! Nama pemenang dapat dilihat pada papan pengumuman digital perpustakaan. Pemenang berhak mendapatkan voucher belanja buku dan sertifikat penghargaan. Terima kasih atas partisipasi semua peserta.
MD,
                'published_at' => now()->subDays(15)->setTime(9, 0),
            ],
            [
                'judul' => 'Penambahan Koleksi Komik Edukatif untuk Remaja',
                'kategori' => 'Perawatan Koleksi',
                'thumbnail_caption' => 'Komik edukatif baru siap dipinjam oleh remaja.',
                'konten' => <<<'MD'
Kami menambah lebih dari 150 judul komik edukatif yang membahas sains, sejarah, dan karakter inspiratif. Koleksi dapat ditemukan di rak literasi remaja lantai 1. Tag spesial "Edukasi Seru" membantu Anda menemukan koleksi ini dengan mudah.
MD,
                'published_at' => now()->subDays(16)->setTime(10, 15),
            ],
            [
                'judul' => 'Penyesuaian Prosedur Pengembalian Buku Selama Renovasi',
                'kategori' => 'Kebijakan & Regulasi',
                'thumbnail_caption' => 'Prosedur baru pengembalian buku saat renovasi area utama.',
                'konten' => <<<'MD'
Selama renovasi area pengembalian, anggota diminta menaruh buku di drop-box sementara yang terletak di pintu masuk barat. Petugas akan memproses pengembalian setiap jam dan mengirimkan notifikasi email setelah selesai.
MD,
                'published_at' => now()->subDays(17)->setTime(12, 0),
            ],
            [
                'judul' => 'Kelas Menulis Kreatif Bersama Penulis Lokal',
                'kategori' => 'Kegiatan & Acara',
                'thumbnail_caption' => 'Sesi menulis kreatif untuk siswa SMA.',
                'konten' => <<<'MD'
Gabung dalam kelas menulis kreatif bersama penulis lokal yang akan berbagi teknik menggali ide, membangun alur cerita, dan mengembangkan karakter. Kegiatan akan dilaksanakan pada Sabtu, 7 Desember 2024, pukul 09.00 - 12.00 di ruang kreatif.
MD,
                'published_at' => now()->subDays(18)->setTime(9, 45),
            ],
            [
                'judul' => 'Peluncuran Fitur Reminder Otomatis via WhatsApp',
                'kategori' => 'Layanan & Fasilitas',
                'thumbnail_caption' => 'Pengingat jatuh tempo melalui WhatsApp.',
                'konten' => <<<'MD'
Anggota kini dapat menerima pengingat otomatis pengembalian buku melalui WhatsApp dengan mendaftarkan nomor telepon aktif di meja layanan. Pengingat akan dikirimkan dua hari sebelum tanggal jatuh tempo.
MD,
                'published_at' => now()->subDays(19)->setTime(14, 30),
            ],
            [
                'judul' => 'Sesi Konsultasi Referensi Khusus Skripsi',
                'kategori' => 'Layanan & Fasilitas',
                'thumbnail_caption' => 'Bantuan referensi untuk mahasiswa tingkat akhir.',
                'konten' => <<<'MD'
Perpustakaan menyediakan sesi konsultasi referensi khusus skripsi setiap Selasa dan Kamis pukul 13.00 - 15.00 di meja informasi lantai 2. Bawa topik penelitian Anda dan kami bantu carikan referensi yang relevan.
MD,
                'published_at' => now()->subDays(20)->setTime(13, 30),
            ],
            [
                'judul' => 'Program Tantangan Baca 30 Hari',
                'kategori' => 'Pengumuman Umum',
                'thumbnail_caption' => 'Ajak teman untuk ikut tantangan baca 30 hari.',
                'konten' => <<<'MD'
Ajak teman, keluarga, atau kelas Anda untuk ikut tantangan baca 30 hari. Setiap peserta diminta membaca minimal satu buku per minggu dan membagikan ulasan singkat melalui portal perpustakaan. Peserta yang menyelesaikan tantangan akan mendapatkan sertifikat dan hadiah menarik.
MD,
                'published_at' => now()->subDays(23)->setTime(9, 20),
            ],
        ];

        DB::transaction(function () use ($announcements, $categories, $adminUsers, $image) {
            foreach ($announcements as $index => $data) {
                $kategoriId = $categories->get($data['kategori']);
                $adminId = $adminUsers[$index % $adminUsers->count()]->id;

                Pengumuman::updateOrCreate(
                    ['slug' => Str::slug($data['judul'])],
                    [
                        'judul' => $data['judul'],
                        'kategori_pengumuman_id' => $kategoriId,
                        'admin_id' => $adminId,
                        'thumbnail_url' => $image,
                        'thumbnail_caption' => $data['thumbnail_caption'],
                        'konten' => $data['konten'],
                        'status' => 'published',
                        'published_at' => $data['published_at'],
                    ]
                );
            }
        });
    }
}
