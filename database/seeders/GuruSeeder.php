<?php

namespace Database\Seeders;

use App\Models\RoleData;
use App\Models\Guru;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    private const TOTAL_GURU = 30; 

  
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $roleGuru = RoleData::firstOrCreate(
            ['nama_role' => 'Guru'],
            [
                'deskripsi_role' => 'Akses data siswa dan mengajar mata pelajaran.',
                'icon_role' => 'bi-person-badge',
            ],
        );

        // Daftar mata pelajaran
        $mapelList = [
            'Matematika',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'Fisika',
            'Kimia',
            'Biologi',
            'Sejarah',
            'Geografi',
            'Seni Budaya',
            'Pendidikan Jasmani'
        ];

        for ($i = 0; $i < self::TOTAL_GURU; $i++) {
            $gender = $faker->randomElement(['Laki-laki', 'Perempuan']);

            $user = User::create([
                'nama_user' => $faker->name($gender === 'Laki-laki' ? 'male' : 'female'),
                'email_user' => $faker->unique()->safeEmail(),
                'phone_number' => $faker->unique()->numerify('08##########'),
                'password' => Hash::make('password'),
                'role_id' => $roleGuru->id,
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nip' => $faker->unique()->numerify('00##########'),
                'jenis_kelamin' => $gender,
                'mata_pelajaran' => $faker->randomElement($mapelList),
                'foto' => null,
                'alamat' => $faker->address(),
            ]);
        }
    }
}
