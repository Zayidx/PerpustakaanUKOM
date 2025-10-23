<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $authors = [
            [
                'nama_author' => 'Farid Indrawan',
                'email_author' => 'farid.indrawan@example.com',
                'no_telp' => '0812-3456-7890',
                'alamat' => 'Jakarta Selatan, DKI Jakarta',
            ],
            [
                'nama_author' => 'Nadia Putri',
                'email_author' => 'nadia.putri@example.com',
                'no_telp' => '0813-9876-5432',
                'alamat' => 'Bandung, Jawa Barat',
            ],
            [
                'nama_author' => 'Arief Wicaksono',
                'email_author' => 'arief.wicaksono@example.com',
                'no_telp' => '0812-1111-2222',
                'alamat' => 'Surabaya, Jawa Timur',
            ],
            [
                'nama_author' => 'Dewi Lestari',
                'email_author' => 'dewi.lestari@example.com',
                'no_telp' => '0814-5555-6666',
                'alamat' => 'Yogyakarta, DIY',
            ],
            [
                'nama_author' => 'Raka Pratama',
                'email_author' => 'raka.pratama@example.com',
                'no_telp' => '0815-7777-8888',
                'alamat' => 'Semarang, Jawa Tengah',
            ],
        ];

        foreach ($authors as $author) {
            Author::updateOrCreate(
                ['nama_author' => $author['nama_author']],
                $author
            );
        }
    }
}
