<?php

namespace Tests\Feature;

use App\Livewire\Guru\ScanPeminjaman;
use App\Livewire\Siswa\ListBuku;
use App\Models\Author;
use App\Models\Buku;
use App\Models\Guru;
use App\Models\KategoriBuku;
use App\Models\Peminjaman;
use App\Models\PeminjamanItem;
use App\Models\Penerbit;
use App\Models\RoleData;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class LoanFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_generate_loan_code(): void
    {
        $siswaRole = RoleData::create([
            'nama_role' => 'Siswa',
            'deskripsi_role' => 'Role siswa',
            'icon_role' => 'user',
        ]);

        $user = User::factory()->create([
            'role_id' => $siswaRole->id,
        ]);

        Siswa::create([
            'user_id' => $user->id,
            'nisn' => '1234567890',
            'nis' => '2024001',
            'alamat' => 'Jl. Testing',
            'jenis_kelamin' => 'laki-laki',
        ]);

        $author = Author::create([
            'nama_author' => 'Author A',
        ]);

        $kategori = KategoriBuku::create([
            'nama_kategori_buku' => 'Fiksi',
            'deskripsi_kategori_buku' => 'Kategori fiksi',
        ]);

        $penerbit = Penerbit::create([
            'nama_penerbit' => 'Penerbit A',
            'deskripsi' => 'Deskripsi',
            'logo' => 'logo.png',
            'tahun_hakcipta' => '2024',
        ]);

        $bookOne = Buku::create([
            'nama_buku' => 'Buku Pertama',
            'author_id' => $author->id,
            'kategori_id' => $kategori->id,
            'penerbit_id' => $penerbit->id,
            'deskripsi' => 'Deskripsi satu',
            'tanggal_terbit' => '2024-01-01',
            'stok' => 3,
        ]);

        $bookTwo = Buku::create([
            'nama_buku' => 'Buku Kedua',
            'author_id' => $author->id,
            'kategori_id' => $kategori->id,
            'penerbit_id' => $penerbit->id,
            'deskripsi' => 'Deskripsi dua',
            'tanggal_terbit' => '2024-01-02',
            'stok' => 2,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(ListBuku::class)
            ->set('selectedBooks', [$bookOne->id, $bookTwo->id])
            ->call('generateLoanCode');

        $loan = Peminjaman::with('items')->first();

        $this->assertNotNull($loan);
        $this->assertSame('pending', $loan->status);
        $this->assertCount(2, $loan->items);
        $component->assertRedirect(route('siswa.kode-peminjaman', ['kode' => $loan->kode]));

        $bookOne->refresh();
        $bookTwo->refresh();

        $this->assertSame(2, $bookOne->stok);
        $this->assertSame(1, $bookTwo->stok);
    }

    public function test_guru_scan_accepts_pending_loan(): void
    {
        Carbon::setTestNow('2024-10-01 08:00:00');

        $siswaRole = RoleData::create([
            'nama_role' => 'Siswa',
            'deskripsi_role' => 'Role siswa',
            'icon_role' => 'user',
        ]);

        $guruRole = RoleData::create([
            'nama_role' => 'Guru',
            'deskripsi_role' => 'Role guru',
            'icon_role' => 'chalkboard',
        ]);

        $siswaUser = User::factory()->create(['role_id' => $siswaRole->id]);
        $guruUser = User::factory()->create(['role_id' => $guruRole->id]);

        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'nisn' => '1234567891',
            'nis' => '2024002',
            'alamat' => 'Jl. Loan',
            'jenis_kelamin' => 'laki-laki',
        ]);

        $author = Author::create(['nama_author' => 'Author B']);
        $kategori = KategoriBuku::create([
            'nama_kategori_buku' => 'Non Fiksi',
            'deskripsi_kategori_buku' => 'Kategori non fiksi',
        ]);
        $penerbit = Penerbit::create([
            'nama_penerbit' => 'Penerbit B',
            'deskripsi' => 'Desk',
            'logo' => 'logo.png',
            'tahun_hakcipta' => '2023',
        ]);

        $book = Buku::create([
            'nama_buku' => 'Buku Unik',
            'author_id' => $author->id,
            'kategori_id' => $kategori->id,
            'penerbit_id' => $penerbit->id,
            'deskripsi' => 'Deskripsi',
            'tanggal_terbit' => '2024-02-01',
            'stok' => 1,
        ]);

        $loan = Peminjaman::create([
            'kode' => 'PINJ-'.Str::upper(Str::random(8)),
            'siswa_id' => $siswa->id,
            'status' => 'pending',
        ]);

        PeminjamanItem::create([
            'peminjaman_id' => $loan->id,
            'buku_id' => $book->id,
            'quantity' => 1,
        ]);

        Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '1987654321',
            'mata_pelajaran' => 'Matematika',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $this->actingAs($guruUser);

        $payload = json_encode([
            'code' => $loan->kode,
            'loan_id' => $loan->id,
            'student_id' => $siswa->id,
        ], JSON_THROW_ON_ERROR);

        Livewire::test(ScanPeminjaman::class)
            ->dispatch('qr-scanned', ['payload' => $payload])
            ->assertSet('loan.status', 'accepted');

        $loan->refresh();

        $this->assertSame('accepted', $loan->status);
        $this->assertNotNull($loan->accepted_at);
        $this->assertNotNull($loan->due_at);
        $this->assertEquals('2024-10-08', $loan->due_at->toDateString());

        $book->refresh();
        $this->assertSame(1, $book->stok);

        Carbon::setTestNow();
    }

    public function test_student_cannot_generate_loan_when_stock_empty(): void
    {
        $role = RoleData::create([
            'nama_role' => 'Siswa',
            'deskripsi_role' => 'Role siswa',
            'icon_role' => 'user',
        ]);

        $user = User::factory()->create(['role_id' => $role->id]);

        Siswa::create([
            'user_id' => $user->id,
            'nisn' => '1234567892',
            'nis' => '2024003',
            'alamat' => 'Jl. Kosong',
            'jenis_kelamin' => 'laki-laki',
        ]);

        $author = Author::create(['nama_author' => 'Author C']);
        $kategori = KategoriBuku::create([
            'nama_kategori_buku' => 'Fiksi Ilmiah',
            'deskripsi_kategori_buku' => 'Kategori fiksi ilmiah',
        ]);
        $penerbit = Penerbit::create([
            'nama_penerbit' => 'Penerbit C',
            'deskripsi' => 'Deskripsi',
            'logo' => 'logo.png',
            'tahun_hakcipta' => '2024',
        ]);

        $book = Buku::create([
            'nama_buku' => 'Buku Langka',
            'author_id' => $author->id,
            'kategori_id' => $kategori->id,
            'penerbit_id' => $penerbit->id,
            'deskripsi' => 'Deskripsi',
            'tanggal_terbit' => '2024-03-01',
            'stok' => 0,
        ]);

        $this->actingAs($user);

        Livewire::test(ListBuku::class)
            ->call('toggleSelection', $book->id)
            ->assertHasErrors(['selection']);

        Livewire::test(ListBuku::class)
            ->set('selectedBooks', [$book->id])
            ->call('generateLoanCode')
            ->assertHasErrors(['selection']);
    }
}
