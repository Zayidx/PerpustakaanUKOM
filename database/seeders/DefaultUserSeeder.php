<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\RoleData;
use App\Models\Siswa;
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

        $roleAdmin = RoleData::firstOrCreate(
            ['nama_role' => 'Administrator'],
            [
                'deskripsi_role' => 'Hak akses penuh untuk mengelola sistem perpustakaan.',
                'icon_role' => 'bi-person-gear',
            ],
        );

        $roleGuru = RoleData::firstOrCreate(
            ['nama_role' => 'Guru'],
            [
                'deskripsi_role' => 'Mengelola informasi akademik dan data perpustakaan.',
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

        User::updateOrCreate(
            ['email_user' => 'admin@gmail.com'],
            [
                'nama_user' => 'Admin Perpustakaan',
                'phone_number' => $faker->numerify('08##########'),
                'password' => 'admin123',
                'role_id' => $roleAdmin->id,
            ]
        );

        $guruUser = User::updateOrCreate(
            ['email_user' => 'guru@gmail.com'],
            [
                'nama_user' => 'Guru Konseling',
                'phone_number' => $faker->numerify('08##########'),
                'password' => 'guru123',
                'role_id' => $roleGuru->id,
            ]
        );

        Guru::updateOrCreate(
            ['user_id' => $guruUser->id],
            [
                'nip' => '1987654321',
                'mata_pelajaran' => 'Bimbingan Konseling',
                'jenis_kelamin' => 'perempuan',
                'alamat' => $faker->address(),
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
