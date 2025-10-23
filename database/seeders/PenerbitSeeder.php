<?php

namespace Database\Seeders;

use App\Models\Penerbit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenerbitSeeder extends Seeder
{
    public function run(): void
    {
        $placeholderLogo = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAH+wK7nhqz3AAAAABJRU5ErkJggg=='
        );

        Storage::disk('public')->makeDirectory('admin/logo-penerbit');

        $penerbit = [
            [
                'nama_penerbit' => 'Mitra Koding',
                'deskripsi' => 'Penerbit buku teknologi dengan fokus pada pengembangan aplikasi modern.',
                'tahun_hakcipta' => 2018,
            ],
            [
                'nama_penerbit' => 'Cakra Media',
                'deskripsi' => 'Menyediakan referensi jaringan, keamanan, dan infrastruktur TI.',
                'tahun_hakcipta' => 2016,
            ],
            [
                'nama_penerbit' => 'Nusa Pengetahuan',
                'deskripsi' => 'Penerbit buku sains dan teknologi populer di kalangan akademisi.',
                'tahun_hakcipta' => 2019,
            ],
            [
                'nama_penerbit' => 'Prima Tech Press',
                'deskripsi' => 'Fokus pada buku-buku profesional seputar arsitektur perangkat lunak.',
                'tahun_hakcipta' => 2020,
            ],
            [
                'nama_penerbit' => 'Inspiring Design',
                'deskripsi' => 'Menedarkan buku desain antarmuka dan pengalaman pengguna.',
                'tahun_hakcipta' => 2021,
            ],
        ];

        foreach ($penerbit as $item) {
            $logoPath = 'admin/logo-penerbit/' . Str::slug($item['nama_penerbit']) . '.png';

            if (! Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->put($logoPath, $placeholderLogo);
            }

            Penerbit::updateOrCreate(
                ['nama_penerbit' => $item['nama_penerbit']],
                [
                    'deskripsi' => $item['deskripsi'],
                    'tahun_hakcipta' => $item['tahun_hakcipta'],
                    'logo' => $logoPath,
                ]
            );
        }
    }
}
