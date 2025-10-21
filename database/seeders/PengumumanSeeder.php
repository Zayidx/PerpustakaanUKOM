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
        $adminUser = User::query()
            ->whereHas('role', fn ($query) => $query->whereIn('nama_role', ['Admin', 'Administrator']))
            ->first();

        if (! $adminUser) {
            $this->command?->warn('PengumumanSeeder: tidak menemukan pengguna dengan role Admin/Administrator. Melewatkan seeder pengumuman.');
            return;
        }

        $categories = collect([
            ['nama' => 'Layanan & Fasilitas', 'deskripsi' => 'Informasi layanan dan fasilitas terbaru perpustakaan.'],
            ['nama' => 'Kegiatan & Acara', 'deskripsi' => 'Agenda kegiatan yang diadakan oleh perpustakaan.'],
            ['nama' => 'Pengumuman Umum', 'deskripsi' => 'Informasi penting lain seputar perpustakaan.'],
        ])->mapWithKeys(function ($data) {
            $kategori = KategoriPengumuman::firstOrCreate(
                ['nama' => $data['nama']],
                ['deskripsi' => $data['deskripsi']]
            );

            return [$data['nama'] => $kategori->id];
        });

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
        ];

        DB::transaction(function () use ($announcements, $categories, $adminUser, $image) {
            foreach ($announcements as $data) {
                $kategoriId = $categories->get($data['kategori']);

                Pengumuman::updateOrCreate(
                    ['slug' => Str::slug($data['judul'])],
                    [
                        'judul' => $data['judul'],
                        'kategori_pengumuman_id' => $kategoriId,
                        'admin_id' => $adminUser->id,
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
