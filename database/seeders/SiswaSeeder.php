<?php

namespace Database\Seeders;

use App\Models\RoleData;
use App\Models\Siswa;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    private const TOTAL_SISWA = 50;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $roleSiswa = RoleData::firstOrCreate(
            ['nama_role' => 'Siswa'],
            [
                'deskripsi_role' => 'Akses data buku dan riwayat peminjaman.',
                'icon_role' => 'bi-mortarboard',
            ],
        );

        for ($i = 0; $i < self::TOTAL_SISWA; $i++) {
            $gender = $faker->randomElement(['laki-laki', 'perempuan']);

            $user = User::create([
                'nama_user' => $faker->name($gender === 'laki-laki' ? 'male' : 'female'),
                'email_user' => $faker->unique()->safeEmail(),
                'phone_number' => $faker->unique()->numerify('08##########'),
                'password' => Hash::make('password'),
                'role_id' => $roleSiswa->id,
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'nisn' => $faker->unique()->numerify('00##########'),
                'nis' => $faker->unique()->numerify('########'),
                'alamat' => $faker->address(),
                'jenis_kelamin' => $gender,
                'foto' => null,
            ]);
        }
    }
}
