<?php

namespace Database\Seeders;

use App\Models\RoleData;
use App\Models\SuperAdmin;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    private const TOTAL_SUPER_ADMINS = 10;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $roleAdmin = RoleData::firstOrCreate(
            ['nama_role' => 'SuperAdmin'],
            [
                'deskripsi_role' => 'Mengelola data buku, peminjaman, dan anggota.',
                'icon_role' => 'bi-briefcase',
            ],
        );

        for ($i = 0; $i < self::TOTAL_SUPER_ADMINS; $i++) {
            $gender = $faker->randomElement(['laki-laki', 'perempuan']);

            $user = User::create([
                'nama_user' => $faker->name($gender === 'laki-laki' ? 'male' : 'female'),
                'email_user' => $faker->unique()->safeEmail(),
                'phone_number' => $faker->unique()->numerify('08##########'),
                'password' => Hash::make('password'),
                'role_id' => $roleAdmin->id,
            ]);

            SuperAdmin::create([
                'user_id' => $user->id,
                'nip' => $faker->unique()->numerify('##########'),
                'alamat' => $faker->address(),
                'jenis_kelamin' => $gender,
                'foto' => null,
            ]);
        }
    }
}
