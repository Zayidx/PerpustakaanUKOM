<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\RoleData;
use App\Models\Siswa;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SiswaSeeder extends Seeder
{
    private const TOTAL_SISWA = 50;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $kelasCollection = Kelas::all();
        if ($kelasCollection->isEmpty()) {
            $this->call(KelasSeeder::class);
            $kelasCollection = Kelas::all();
        }

        $jurusanCollection = Jurusan::all();
        if ($jurusanCollection->isEmpty()) {
            $this->call(JurusanSeeder::class);
            $jurusanCollection = Jurusan::all();
        }

        $roleSiswa = RoleData::firstOrCreate(
            ['nama_role' => 'Siswa'],
            [
                'deskripsi_role' => 'Akses data buku dan riwayat peminjaman.',
                'icon_role' => 'bi-mortarboard',
            ],
        );

        for ($i = 0; $i < self::TOTAL_SISWA; $i++) {
            $gender = $faker->randomElement(['laki-laki', 'perempuan']);
            $kelas = $kelasCollection->random();
            $jurusan = $jurusanCollection->random();

            $user = User::create([
                'nama_user' => $faker->name($gender === 'laki-laki' ? 'male' : 'female'),
                'email_user' => $this->generateUniqueValue(
                    fn () => Str::lower($faker->userName()).'.'.Str::lower(Str::random(6)).'@example.org',
                    fn ($value) => User::where('email_user', $value)->exists(),
                ),
                'phone_number' => $this->generateUniqueValue(
                    fn () => '08'.$faker->numerify('##########'),
                    fn ($value) => User::where('phone_number', $value)->exists(),
                ),
                'password' => Hash::make('password'),
                'role_id' => $roleSiswa->id,
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'nisn' => $this->generateUniqueValue(
                    fn () => $faker->numerify('00##########'),
                    fn ($value) => Siswa::where('nisn', $value)->exists(),
                ),
                'nis' => $this->generateUniqueValue(
                    fn () => $faker->numerify('########'),
                    fn ($value) => Siswa::where('nis', $value)->exists(),
                ),
                'alamat' => $faker->address(),
                'jenis_kelamin' => $gender,
                'kelas_id' => $kelas->id,
                'jurusan_id' => $jurusan->id,
                'foto' => null,
            ]);
        }
    }

    private function generateUniqueValue(callable $generator, callable $existsChecker, int $maxAttempts = 100): string
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $value = (string) $generator();

            if (! $existsChecker($value)) {
                return $value;
            }
        }

        throw new \RuntimeException('Gagal menghasilkan data unik setelah '.$maxAttempts.' percobaan.');
    }
}
