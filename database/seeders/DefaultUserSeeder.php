<?php

namespace Database\Seeders;

use App\Models\AdminPerpus;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\RoleData;
use App\Models\Siswa;
use App\Models\SuperAdmin;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $roleSuperAdmin = RoleData::firstOrCreate(
            ['nama_role' => 'SuperAdmin'],
            [
                'deskripsi_role' => 'Hak akses penuh untuk mengelola sistem perpustakaan.',
                'icon_role' => 'bi-person-gear',
            ],
        );

        $roleAdminPerpus = RoleData::firstOrCreate(
            ['nama_role' => 'AdminPerpus'],
            [
                'deskripsi_role' => 'Mengelola transaksi peminjaman perpustakaan.',
                'icon_role' => 'bi-person-workspace',
            ],
        );

        $roleSiswa = RoleData::firstOrCreate(
            ['nama_role' => 'Siswa'],
            [
                'deskripsi_role' => 'Akses data buku dan riwayat peminjaman.',
                'icon_role' => 'bi-mortarboard',
            ],
        );

        $superAdminUser = User::updateOrCreate(
            ['email_user' => 'superadmin@gmail.com'],
            [
                'nama_user' => 'Super Admin Perpustakaan',
                'phone_number' => $faker->numerify('08##########'),
                'password' => 'superadmin123',
                'role_id' => $roleSuperAdmin->id,
            ]
        );

        $adminPerpusUser = User::updateOrCreate(
            ['email_user' => 'adminperpus@gmail.com'],
            [
                'nama_user' => 'Admin Perpus Utama',
                'phone_number' => $faker->numerify('08##########'),
                'password' => 'adminperpus123',
                'role_id' => $roleAdminPerpus->id,
            ]
        );

        AdminPerpus::updateOrCreate(
            ['user_id' => $adminPerpusUser->id],
            [
                'nip' => '1987654321',
                'mata_pelajaran' => 'Operasional Perpustakaan',
                'jenis_kelamin' => 'perempuan',
                'alamat' => $faker->address(),
                'foto' => null,
            ]
        );

        SuperAdmin::updateOrCreate(
            ['user_id' => $superAdminUser->id],
            [
                'alamat' => $faker->address(),
                'jenis_kelamin' => 'laki-laki',
                'foto' => null,
            ]
        );

        $kelasId = Kelas::query()->inRandomOrder()->value('id') ?? Kelas::query()->first()?->id;
        $jurusanId = Jurusan::query()->inRandomOrder()->value('id') ?? Jurusan::query()->first()?->id;

        $siswaUser = User::updateOrCreate(
            ['email_user' => 'siswa@gmail.com'],
            [
                'nama_user' => 'Siswa Teladan',
                'phone_number' => $faker->numerify('08##########'),
                'password' => 'siswa123',
                'role_id' => $roleSiswa->id,
            ]
        );

        if ($kelasId && $jurusanId) {
            Siswa::updateOrCreate(
                ['user_id' => $siswaUser->id],
                [
                    'kelas_id' => $kelasId,
                    'jurusan_id' => $jurusanId,
                    'nisn' => '009999999999',
                    'nis' => '99999999',
                    'alamat' => $faker->address(),
                    'jenis_kelamin' => 'laki-laki',
                    'foto' => null,
                ]
            );
        }
    }
}
