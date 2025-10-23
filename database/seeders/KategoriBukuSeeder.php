<?php

namespace Database\Seeders;

use App\Models\KategoriBuku;
use Illuminate\Database\Seeder;

class KategoriBukuSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            [
                'nama_kategori_buku' => 'Pemrograman',
                'deskripsi_kategori_buku' => 'Buku yang membahas bahasa pemrograman dan pengembangan perangkat lunak.',
            ],
            [
                'nama_kategori_buku' => 'Jaringan Komputer',
                'deskripsi_kategori_buku' => 'Referensi mengenai arsitektur jaringan, keamanan, dan administrasi.',
            ],
            [
                'nama_kategori_buku' => 'Basis Data',
                'deskripsi_kategori_buku' => 'Materi perancangan, optimasi, dan implementasi basis data.',
            ],
            [
                'nama_kategori_buku' => 'Kecerdasan Buatan',
                'deskripsi_kategori_buku' => 'Pembahasan kecerdasan buatan, machine learning, dan data mining.',
            ],
            [
                'nama_kategori_buku' => 'Desain UI/UX',
                'deskripsi_kategori_buku' => 'Panduan merancang antarmuka dan pengalaman pengguna yang efektif.',
            ],
        ];

        foreach ($kategori as $item) {
            KategoriBuku::updateOrCreate(
                ['nama_kategori_buku' => $item['nama_kategori_buku']],
                $item
            );
        }
    }
}
