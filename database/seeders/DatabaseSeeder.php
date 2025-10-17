<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
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

        #Memasukkan data awal ke tabel role_data dan users
        $this->command->warn('[Seeder][1/2] Mengisi data role...');
        $roleId = DB::table('role_data')->insertGetId([
            'nama_role' => 'Administrator',
            'deskripsi_role' => 'Hak akses penuh untuk mengelola sistem perpustakaan.',
            'icon_role' => 'bi-person-gear',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->command->info('[Seeder][1/2] role_data selesai (50%).');

        $this->command->warn('[Seeder][2/2] Membuat pengguna awal...');
        User::factory()->create([
            'nama_user' => 'Test User',
            'email_user' => 'test@example.com',
            'role_id' => $roleId,
        ]);
        $this->command->info('[Seeder][2/2] Pengguna awal selesai (100%).');
    }
}
