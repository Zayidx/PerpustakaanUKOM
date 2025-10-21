<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelasList = [
            ['nama_kelas' => 'X-1', 'tingkat' => 'X'],
            ['nama_kelas' => 'X-2', 'tingkat' => 'X'],
            ['nama_kelas' => 'XI-1', 'tingkat' => 'XI'],
            ['nama_kelas' => 'XI-2', 'tingkat' => 'XI'],
            ['nama_kelas' => 'XII-1', 'tingkat' => 'XII'],
            ['nama_kelas' => 'XII-2', 'tingkat' => 'XII'],
        ];

        foreach ($kelasList as $kelas) {
            Kelas::updateOrCreate(
                ['nama_kelas' => $kelas['nama_kelas']],
                $kelas
            );
        }
    }
}
