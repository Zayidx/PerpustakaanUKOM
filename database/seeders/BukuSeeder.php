<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\Penerbit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        $coverDepanFile = 'BungkamSuara.jpg';
        $coverBelakangFile = 'DompetAyahSepatuIbu.jpeg';

        $coverDirectory = 'admin/cover-buku';
        $disk = Storage::disk('public');
        $disk->makeDirectory($coverDirectory);

        $seedImageDirectory = database_path('seeders/images');
        $coverDepanPath = $this->ensureCoverIsAvailable($disk, $seedImageDirectory, $coverDirectory, $coverDepanFile);
        $coverBelakangPath = $this->ensureCoverIsAvailable($disk, $seedImageDirectory, $coverDirectory, $coverBelakangFile);

        $buku = [
            [
                'nama_buku' => 'Laravel 12 untuk Pemula',
                'author' => 'Farid Indrawan',
                'kategori' => 'Pemrograman',
                'penerbit' => 'Mitra Koding',
                'deskripsi' => 'Panduan praktis membangun aplikasi web modern menggunakan Laravel 12.',
                'tanggal_terbit' => '2024-01-15',
            ],
            [
                'nama_buku' => 'Arsitektur Jaringan Sekolah',
                'author' => 'Arief Wicaksono',
                'kategori' => 'Jaringan Komputer',
                'penerbit' => 'Cakra Media',
                'deskripsi' => 'Konsep dan praktik terbaik merancang jaringan komputer skala menengah.',
                'tanggal_terbit' => '2023-08-22',
            ],
            [
                'nama_buku' => 'Optimasi Basis Data Relasional',
                'author' => 'Nadia Putri',
                'kategori' => 'Basis Data',
                'penerbit' => 'Nusa Pengetahuan',
                'deskripsi' => 'Strategi optimasi query, indexing, dan desain skema untuk sistem basis data.',
                'tanggal_terbit' => '2022-11-05',
            ],
            [
                'nama_buku' => 'Machine Learning Sederhana',
                'author' => 'Dewi Lestari',
                'kategori' => 'Kecerdasan Buatan',
                'penerbit' => 'Prima Tech Press',
                'deskripsi' => 'Pengenalan machine learning dengan studi kasus dan implementasi Python.',
                'tanggal_terbit' => '2021-06-18',
            ],
            [
                'nama_buku' => 'Praktik Desain UI/UX',
                'author' => 'Raka Pratama',
                'kategori' => 'Desain UI/UX',
                'penerbit' => 'Inspiring Design',
                'deskripsi' => 'Langkah-langkah merancang antarmuka dan pengalaman pengguna yang menarik.',
                'tanggal_terbit' => '2023-03-10',
            ],
        ];

        foreach ($buku as $item) {
            $authorId = Author::where('nama_author', $item['author'])->value('id');
            $kategoriId = KategoriBuku::where('nama_kategori_buku', $item['kategori'])->value('id');
            $penerbitId = Penerbit::where('nama_penerbit', $item['penerbit'])->value('id');

            if (! $authorId || ! $kategoriId || ! $penerbitId) {
                continue;
            }

            Buku::updateOrCreate(
                ['nama_buku' => $item['nama_buku']],
                [
                    'author_id' => $authorId,
                    'kategori_id' => $kategoriId,
                    'penerbit_id' => $penerbitId,
                    'deskripsi' => $item['deskripsi'],
                    'tanggal_terbit' => Carbon::parse($item['tanggal_terbit']),
                    'cover_depan' => $coverDepanPath,
                    'cover_belakang' => $coverBelakangPath,
                    'stok' => 10,
                ]
            );
        }
    }

    private function ensureCoverIsAvailable($disk, string $sourceDir, string $targetDir, string $filename): ?string
    {
        $sourcePath = $sourceDir.'/'.$filename;
        $targetPath = $targetDir.'/'.$filename;

        if (! is_file($sourcePath)) {
            return null;
        }

        if (! $disk->exists($targetPath)) {
            $disk->put($targetPath, file_get_contents($sourcePath));
        }

        return $targetPath;
    }
}
