<?php

namespace Tests\Feature;

use App\Livewire\Guru\ManajemenPeminjaman;
use App\Livewire\Guru\ScanPeminjaman;
use App\Livewire\Guru\ScanPengembalian;
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

        $this->assertSame(3, $bookOne->stok);
        $this->assertSame(2, $bookTwo->stok);
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
            'kode' => '654321',
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
        $this->assertSame(0, $book->stok);

        Carbon::setTestNow();
    }

    public function test_guru_can_process_manual_code_when_scanner_not_available(): void
    {
        Carbon::setTestNow('2024-10-02 10:00:00');

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
            'nisn' => '1234567899',
            'nis' => '2024999',
            'alamat' => 'Jl. Manual',
            'jenis_kelamin' => 'perempuan',
        ]);

        Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '1111222233',
            'mata_pelajaran' => 'Kimia',
            'jenis_kelamin' => 'Perempuan',
        ]);

        $author = Author::create(['nama_author' => 'Author Manual']);
        $kategori = KategoriBuku::create([
            'nama_kategori_buku' => 'Biologi',
            'deskripsi_kategori_buku' => 'Kategori biologi',
        ]);
        $penerbit = Penerbit::create([
            'nama_penerbit' => 'Penerbit Manual',
            'deskripsi' => 'Manual',
            'logo' => 'logo.png',
            'tahun_hakcipta' => '2024',
        ]);

        $book = Buku::create([
            'nama_buku' => 'Buku Manual',
            'author_id' => $author->id,
            'kategori_id' => $kategori->id,
            'penerbit_id' => $penerbit->id,
            'deskripsi' => 'Deskripsi',
            'tanggal_terbit' => '2024-02-10',
            'stok' => 2,
        ]);

        $loan = Peminjaman::create([
            'kode' => '123456',
            'siswa_id' => $siswa->id,
            'status' => 'pending',
        ]);

        PeminjamanItem::create([
            'peminjaman_id' => $loan->id,
            'buku_id' => $book->id,
            'quantity' => 1,
        ]);

        $this->actingAs($guruUser);

        Livewire::test(ScanPeminjaman::class)
            ->set('manualCode', '123456')
            ->call('processManualCode')
            ->assertSet('loan.status', 'accepted');

        $loan->refresh();
        $book->refresh();

        $this->assertSame('accepted', $loan->status);
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

    public function test_guru_can_mark_loan_as_returned_via_management(): void
    {
        Carbon::setTestNow('2024-10-05 09:00:00');

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
            'nisn' => '1234500000',
            'nis' => '2024090',
            'alamat' => 'Jl. Murid',
            'jenis_kelamin' => 'perempuan',
        ]);

        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '1977000001',
            'mata_pelajaran' => 'Bahasa Indonesia',
            'jenis_kelamin' => 'Perempuan',
        ]);

        $author = Author::create(['nama_author' => 'Author D']);
        $kategori = KategoriBuku::create([
            'nama_kategori_buku' => 'Sejarah',
            'deskripsi_kategori_buku' => 'Kategori sejarah',
        ]);
        $penerbit = Penerbit::create([
            'nama_penerbit' => 'Penerbit D',
            'deskripsi' => 'Deskripsi',
            'logo' => 'logo.png',
            'tahun_hakcipta' => '2022',
        ]);

        $book = Buku::create([
            'nama_buku' => 'Buku Sejarah',
            'author_id' => $author->id,
            'kategori_id' => $kategori->id,
            'penerbit_id' => $penerbit->id,
            'deskripsi' => 'Deskripsi',
            'tanggal_terbit' => '2023-04-01',
            'stok' => 0,
        ]);

        $loan = Peminjaman::create([
            'kode' => '234567',
            'siswa_id' => $siswa->id,
            'guru_id' => $guru->id,
            'status' => 'accepted',
            'accepted_at' => now(),
            'due_at' => now()->addDays(3),
        ]);

        PeminjamanItem::create([
            'peminjaman_id' => $loan->id,
            'buku_id' => $book->id,
            'quantity' => 1,
        ]);

        $this->actingAs($guruUser);

        Livewire::test(ManajemenPeminjaman::class)
            ->call('markAsReturned', $loan->id)
            ->assertHasNoErrors();

        $loan->refresh();
        $book->refresh();

        $this->assertSame('returned', $loan->status);
        $this->assertNotNull($loan->returned_at);
        $this->assertSame(1, $book->stok);

        Carbon::setTestNow();
    }

    public function test_guru_can_cancel_pending_loan_via_management(): void
    {
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
            'nisn' => '1234500001',
            'nis' => '2024091',
            'alamat' => 'Jl. Pending',
            'jenis_kelamin' => 'laki-laki',
        ]);

        Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '1977000002',
            'mata_pelajaran' => 'IPA',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $author = Author::create(['nama_author' => 'Author E']);
        $kategori = KategoriBuku::create([
            'nama_kategori_buku' => 'Teknologi',
            'deskripsi_kategori_buku' => 'Kategori teknologi',
        ]);
        $penerbit = Penerbit::create([
            'nama_penerbit' => 'Penerbit E',
            'deskripsi' => 'Desk',
            'logo' => 'logo.png',
            'tahun_hakcipta' => '2021',
        ]);

        $book = Buku::create([
            'nama_buku' => 'Buku Teknologi',
            'author_id' => $author->id,
            'kategori_id' => $kategori->id,
            'penerbit_id' => $penerbit->id,
            'deskripsi' => 'Deskripsi',
            'tanggal_terbit' => '2024-01-15',
            'stok' => 3,
        ]);

        $loan = Peminjaman::create([
            'kode' => '345678',
            'siswa_id' => $siswa->id,
            'status' => 'pending',
        ]);

        PeminjamanItem::create([
            'peminjaman_id' => $loan->id,
            'buku_id' => $book->id,
            'quantity' => 1,
        ]);

        $this->actingAs($guruUser);

        Livewire::test(ManajemenPeminjaman::class)
            ->call('cancelLoan', $loan->id)
            ->assertHasNoErrors();

        $loan->refresh();
        $book->refresh();

        $this->assertSame('cancelled', $loan->status);
        $this->assertSame(3, $book->stok);
    }

    public function test_guru_can_process_return_via_manual_component(): void
    {
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
            'nisn' => '1234500002',
            'nis' => '2024092',
            'alamat' => 'Jl. Pengembalian',
            'jenis_kelamin' => 'perempuan',
        ]);

        Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '1977000003',
            'mata_pelajaran' => 'Sejarah',
            'jenis_kelamin' => 'Perempuan',
        ]);

        $author = Author::create(['nama_author' => 'Author F']);
        $kategori = KategoriBuku::create([
            'nama_kategori_buku' => 'Seni',
            'deskripsi_kategori_buku' => 'Kategori seni',
        ]);
        $penerbit = Penerbit::create([
            'nama_penerbit' => 'Penerbit F',
            'deskripsi' => 'Desk',
            'logo' => 'logo.png',
            'tahun_hakcipta' => '2020',
        ]);

        $book = Buku::create([
            'nama_buku' => 'Buku Seni',
            'author_id' => $author->id,
            'kategori_id' => $kategori->id,
            'penerbit_id' => $penerbit->id,
            'deskripsi' => 'Deskripsi',
            'tanggal_terbit' => '2022-06-01',
            'stok' => 0,
        ]);

        $loan = Peminjaman::create([
            'kode' => '456789',
            'siswa_id' => $siswa->id,
            'status' => 'accepted',
            'due_at' => now()->subDays(2),
        ]);

        PeminjamanItem::create([
            'peminjaman_id' => $loan->id,
            'buku_id' => $book->id,
            'quantity' => 1,
        ]);

        $this->actingAs($guruUser);

        $component = Livewire::test(ScanPengembalian::class)
            ->set('manualCode', '456789')
            ->call('processManualCode')
            ->assertSet('pendingReturn.late_fee', 2000)
            ->assertSet('loan.status', 'accepted');

        $component->call('confirmLateFee')
            ->assertSet('loan.status', 'returned');

        $loan->refresh();
        $book->refresh();

        $this->assertSame('returned', $loan->status);
        $this->assertSame(1, $book->stok);
        $this->assertNotNull($loan->returned_at);
    }

    public function test_guru_can_process_return_via_scan_payload(): void
    {
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
            'nisn' => '1234500003',
            'nis' => '2024093',
            'alamat' => 'Jl. Scan',
            'jenis_kelamin' => 'laki-laki',
        ]);

        Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '1977000004',
            'mata_pelajaran' => 'Fisika',
            'jenis_kelamin' => 'Laki-laki',
        ]);

        $author = Author::create(['nama_author' => 'Author G']);
        $kategori = KategoriBuku::create([
            'nama_kategori_buku' => 'Komik',
            'deskripsi_kategori_buku' => 'Kategori komik',
        ]);
        $penerbit = Penerbit::create([
            'nama_penerbit' => 'Penerbit G',
            'deskripsi' => 'Desk',
            'logo' => 'logo.png',
            'tahun_hakcipta' => '2019',
        ]);

        $book = Buku::create([
            'nama_buku' => 'Buku Komik',
            'author_id' => $author->id,
            'kategori_id' => $kategori->id,
            'penerbit_id' => $penerbit->id,
            'deskripsi' => 'Deskripsi',
            'tanggal_terbit' => '2021-11-01',
            'stok' => 0,
        ]);

        $loan = Peminjaman::create([
            'kode' => '567890',
            'siswa_id' => $siswa->id,
            'status' => 'accepted',
            'due_at' => now()->subDay(),
        ]);

        PeminjamanItem::create([
            'peminjaman_id' => $loan->id,
            'buku_id' => $book->id,
            'quantity' => 1,
        ]);

        $this->actingAs($guruUser);

        $payload = json_encode([
            'code' => $loan->kode,
            'loan_id' => $loan->id,
            'student_id' => $siswa->id,
            'action' => 'return',
        ], JSON_THROW_ON_ERROR);

        $component = Livewire::test(ScanPengembalian::class)
            ->dispatch('qr-scanned', ['payload' => $payload])
            ->assertSet('pendingReturn.late_days', 1)
            ->assertSet('loan.status', 'accepted');

        $component->call('confirmLateFee')
            ->assertSet('loan.status', 'returned');

        $loan->refresh();
        $book->refresh();

        $this->assertSame('returned', $loan->status);
        $this->assertSame(1, $book->stok);
    }
}
