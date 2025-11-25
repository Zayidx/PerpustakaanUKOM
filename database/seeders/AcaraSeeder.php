<?php

namespace Database\Seeders;

use App\Models\Acara;
use App\Models\KategoriAcara;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AcaraSeeder extends Seeder
{
    public function run(): void
    {
        $adminUsers = User::query()
            ->whereHas('role', fn ($query) => $query->whereIn('nama_role', ['SuperAdmin']))
            ->take(15)
            ->get();

        if ($adminUsers->isEmpty()) {
            $this->command?->warn('AcaraSeeder: tidak ditemukan pengguna dengan role SuperAdmin. Melewatkan seeder acara.');
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
                    'password' => Hash::make('password'),
                    'role_id' => $firstRoleId,
                ]));
            }
        }

        $kategoriDefinisi = [
            ['nama' => 'Temu Penulis', 'deskripsi' => 'Pertemuan dan diskusi bersama penulis.'],
            ['nama' => 'Workshop', 'deskripsi' => 'Pelatihan praktis untuk meningkatkan keterampilan.'],
            ['nama' => 'Bedah Buku', 'deskripsi' => 'Diskusi mendalam mengenai buku tertentu.'],
            ['nama' => 'Kelas Singkat', 'deskripsi' => 'Sesi pembelajaran ringkas di perpustakaan.'],
            ['nama' => 'Pameran', 'deskripsi' => 'Pameran koleksi khusus atau langka.'],
            ['nama' => 'Pelatihan', 'deskripsi' => 'Pelatihan internal untuk pengembangan kompetensi.'],
            ['nama' => 'Klub Buku', 'deskripsi' => 'Pertemuan rutin komunitas pembaca.'],
            ['nama' => 'Konsultasi', 'deskripsi' => 'Layanan konsultasi dan pendampingan referensi.'],
            ['nama' => 'Program', 'deskripsi' => 'Peluncuran atau pengumuman program perpustakaan.'],
            ['nama' => 'Orientasi', 'deskripsi' => 'Orientasi dan pengenalan fasilitas perpustakaan.'],
            ['nama' => 'Storytelling', 'deskripsi' => 'Kegiatan mendongeng dan literasi anak.'],
            ['nama' => 'Diskusi Panel', 'deskripsi' => 'Diskusi panel bersama narasumber ahli.'],
        ];

        $kategoriMap = collect($kategoriDefinisi)->mapWithKeys(function ($item) {
            $slug = Str::slug($item['nama']) ?: 'kategori-' . Str::random(5);

            $kategori = KategoriAcara::firstOrCreate(
                ['nama' => $item['nama']],
                [
                    'slug' => $slug,
                    'deskripsi' => $item['deskripsi'],
                ]
            );

            return [$item['nama'] => $kategori->id];
        });

        $image = 'https://www.nintendo.com/eu/media/images/10_share_images/games_15/nintendo_switch_4/2x1_NSwitch_Minecraft.jpg';
        $baseDate = now()->startOfDay();

        $events = [
            [
                'judul' => 'Jumpa Penulis: Menyusun Cerita yang Menginspirasi',
                'lokasi' => 'Aula Utama',
                'kategori' => 'Temu Penulis',
                'deskripsi' => "Temui penulis lokal favoritmu dan pelajari bagaimana proses kreatif melahirkan karya yang menyentuh banyak orang.\nPeserta dapat membawa buku untuk ditandatangani pada sesi akhir acara.",
                'days' => 3,
                'start' => [15, 0],
                'end' => [17, 0],
            ],
            [
                'judul' => 'Workshop Desain Presentasi untuk Pelajar',
                'lokasi' => 'Ruang Multimedia Lantai 2',
                'kategori' => 'Workshop',
                'deskripsi' => 'Pelajari cara membuat presentasi menarik menggunakan Canva dan Google Slides. Peserta diharapkan membawa laptop pribadi.',
                'days' => 5,
                'start' => [9, 0],
                'end' => [12, 0],
            ],
            [
                'judul' => 'Bedah Buku: Teknologi Ramah Lingkungan',
                'lokasi' => 'Area Diskusi Koleksi Sains',
                'kategori' => 'Bedah Buku',
                'deskripsi' => 'Diskusi terbuka mengenai buku-buku terbaru bertema teknologi ramah lingkungan. Peserta mendapatkan rekomendasi bacaan lanjutan.',
                'days' => 7,
                'start' => [13, 30],
                'end' => [15, 0],
            ],
            [
                'judul' => 'Kelas Cepat Sitasi dan Referensi',
                'lokasi' => 'Ruang Konsultasi Referensi',
                'kategori' => 'Kelas Singkat',
                'deskripsi' => 'Sesi intensif untuk mempelajari penggunaan Zotero dan Mendeley dalam menyusun sitasi karya ilmiah.',
                'days' => 8,
                'start' => [10, 0],
                'end' => [11, 30],
            ],
            [
                'judul' => 'Pameran Koleksi Langka: Membaca Masa Lampau',
                'lokasi' => 'Galeri Koleksi Khusus',
                'kategori' => 'Pameran',
                'deskripsi' => 'Pameran buku dan manuskrip langka yang jarang ditampilkan ke publik. Dilengkapi tur singkat setiap jamnya.',
                'days' => 10,
                'start' => [9, 30],
                'end' => [11, 30],
            ],
            [
                'judul' => 'Pelatihan Pengelolaan Referensi untuk Admin Perpus',
                'lokasi' => 'Ruang Rapat Admin Perpus',
                'kategori' => 'Pelatihan',
                'deskripsi' => 'Fokus pada pemanfaatan fitur kolaborasi di Mendeley dan Zotero serta cara membagikan pustaka digital.',
                'days' => 12,
                'start' => [13, 0],
                'end' => [15, 0],
            ],
            [
                'judul' => 'Pertemuan Klub Buku: Sains Populer',
                'lokasi' => 'Sudut Baca Sains',
                'kategori' => 'Klub Buku',
                'deskripsi' => 'Bahas buku sains populer terbaru dalam suasana santai. Peserta dipersilakan membawa kudapan ringan.',
                'days' => 13,
                'start' => [14, 0],
                'end' => [15, 30],
            ],
            [
                'judul' => 'Sesi Konsultasi Referensi Skripsi',
                'lokasi' => 'Meja Informasi Lantai 2',
                'kategori' => 'Konsultasi',
                'deskripsi' => 'Bantu mahasiswa tingkat akhir menemukan referensi pendukung skripsi. Jadwalkan sesi 20 menit per peserta.',
                'days' => 14,
                'start' => [13, 30],
                'end' => [15, 30],
            ],
            [
                'judul' => 'Peluncuran Program Tantangan Baca 30 Hari',
                'lokasi' => 'Aula Perpustakaan',
                'kategori' => 'Program',
                'deskripsi' => 'Program membaca intensif dengan target satu buku per minggu dan diskusi akhir pekan.',
                'days' => 15,
                'start' => [13, 0],
                'end' => [14, 30],
            ],
            [
                'judul' => 'Orientasi Perpustakaan untuk Siswa Baru',
                'lokasi' => 'Ruang Presentasi',
                'kategori' => 'Orientasi',
                'deskripsi' => 'Kenalkan fasilitas, layanan digital, dan cara peminjaman buku bagi siswa baru.',
                'days' => 16,
                'start' => [10, 0],
                'end' => [11, 0],
            ],
            [
                'judul' => 'Klinik Literasi Informasi untuk OSIS',
                'lokasi' => 'Ruang Rapat OSIS',
                'kategori' => 'Konsultasi',
                'deskripsi' => 'Pendampingan dalam mencari data valid untuk materi kampanye OSIS dan publikasi sekolah.',
                'days' => 17,
                'start' => [9, 0],
                'end' => [11, 0],
            ],
            [
                'judul' => 'Sesi Mendongeng untuk Anak PAUD',
                'lokasi' => 'Ruang Baca Anak',
                'kategori' => 'Storytelling',
                'deskripsi' => 'Menghadirkan pendongeng profesional untuk membangkitkan minat baca sejak dini.',
                'days' => 18,
                'start' => [8, 30],
                'end' => [9, 30],
            ],
            [
                'judul' => 'Pelatihan Penataan Koleksi Digital Sekolah',
                'lokasi' => 'Lab Komputer',
                'kategori' => 'Pelatihan',
                'deskripsi' => 'Pelatihan internal untuk Admin Perpus tentang tagging dan kurasi koleksi digital.',
                'days' => 19,
                'start' => [13, 0],
                'end' => [16, 0],
            ],
            [
                'judul' => 'Temu Komunitas Komik Edukatif',
                'lokasi' => 'Ruang Komunitas',
                'kategori' => 'Klub Buku',
                'deskripsi' => 'Pertemuan komunitas komik edukatif untuk membahas konten terbaru dan berbagi ilustrasi.',
                'days' => 20,
                'start' => [15, 0],
                'end' => [17, 0],
            ],
            [
                'judul' => 'Diskusi Panel: Literasi Digital dan Keamanan Data',
                'lokasi' => 'Auditorium Sekolah',
                'kategori' => 'Diskusi Panel',
                'deskripsi' => 'Diskusi pakar mengenai peran literasi digital dan keamanan data di era teknologi modern.',
                'days' => 21,
                'start' => [9, 30],
                'end' => [11, 30],
            ],
        ];

        foreach ($events as $index => $event) {
            $start = (clone $baseDate)->addDays($event['days'])->setTime($event['start'][0], $event['start'][1]);
            $end = (clone $baseDate)->addDays($event['days'])->setTime($event['end'][0], $event['end'][1]);

            Acara::updateOrCreate(
                ['slug' => Str::slug($event['judul'])],
                [
                    'judul' => $event['judul'],
                    'lokasi' => $event['lokasi'],
                    'kategori_acara_id' => $kategoriMap[$event['kategori']] ?? $kategoriMap->first(),
                    'poster_url' => $image,
                    'deskripsi' => $event['deskripsi'],
                    'mulai_at' => $start,
                    'selesai_at' => $end,
                    'admin_id' => $adminUsers[$index % $adminUsers->count()]->id,
                ]
            );
        }
    }
}
