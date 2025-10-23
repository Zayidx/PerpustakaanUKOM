<?php

namespace Database\Seeders;

use App\Models\RoleData;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            'Administrator' => [
                'deskripsi_role' => 'Hak akses penuh untuk mengelola sistem perpustakaan.',
                'icon_role' => 'bi-person-gear',
            ],
            'Guru' => [
                'deskripsi_role' => 'Mengelola informasi akademik dan data perpustakaan.',
                'icon_role' => 'bi-person-workspace',
            ],
            'Siswa' => [
                'deskripsi_role' => 'Akses data buku dan riwayat peminjaman.',
                'icon_role' => 'bi-mortarboard',
            ],
        ];

        $this->command->warn('[Seeder] Menyiapkan role default...');
        $roleIds = [];
        $totalRoles = count($roles);
        $index = 1;

        foreach ($roles as $namaRole => $attributes) {
            $role = RoleData::firstOrCreate(
                ['nama_role' => $namaRole],
                $attributes
            );

            $roleIds[$namaRole] = $role->id;
            $progress = (int) round(($index / $totalRoles) * 100);
            $this->command->info(sprintf('[Seeder] Role %s siap (%d%%)', $namaRole, $progress));
            $index++;
        }

        $this->command->warn('[Seeder] Membuat pengguna administrator awal...');
        User::updateOrCreate(
            ['email_user' => 'test@example.com'],
            [
                'nama_user' => 'Test User',
                'phone_number' => '081234567890',
                'password' => 'password',
                'role_id' => $roleIds['Administrator'] ?? null,
            ],
        );

        $this->command->warn('[Seeder] Menyiapkan data kelas dan jurusan...');
        $this->call([
            KelasSeeder::class,
            JurusanSeeder::class,
            PetugasSeeder::class,
            GuruSeeder::class,
            PengumumanSeeder::class,
            AcaraSeeder::class,
        ]);

        $this->call(DefaultUserSeeder::class);

        $this->command->warn('[Seeder] Menyiapkan data referensi buku...');
        $this->call([
            AuthorSeeder::class,
            KategoriBukuSeeder::class,
            PenerbitSeeder::class,
            BukuSeeder::class,
        ]);

        $this->command->warn('[Seeder] Mengisi data siswa contoh (50 data)...');
        $this->call(SiswaSeeder::class);
        $this->command->info('[Seeder] Seeder selesai (100%).');
    }
}
