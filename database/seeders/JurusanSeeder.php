<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusanList = [
            ['nama_jurusan' => 'Ilmu Pengetahuan Alam', 'deskripsi' => 'Fokus pada sains, matematika, dan penelitian laboratorium.'],
            ['nama_jurusan' => 'Ilmu Pengetahuan Sosial', 'deskripsi' => 'Fokus pada ekonomi, sejarah, sosiologi, dan geografi.'],
            ['nama_jurusan' => 'Bahasa dan Budaya', 'deskripsi' => 'Fokus pada linguistik, sastra, dan studi budaya.'],
            ['nama_jurusan' => 'Teknik Komputer dan Jaringan', 'deskripsi' => 'Fokus pada jaringan komputer, perangkat keras, dan pemrograman dasar.'],
            ['nama_jurusan' => 'Multimedia', 'deskripsi' => 'Fokus pada desain grafis, animasi, dan produksi media.'],
        ];

        foreach ($jurusanList as $jurusan) {
            Jurusan::updateOrCreate(
                ['nama_jurusan' => $jurusan['nama_jurusan']],
                $jurusan
            );
        }
    }
}
