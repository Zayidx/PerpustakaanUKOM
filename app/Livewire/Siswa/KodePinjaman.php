<?php

namespace App\Livewire\Siswa;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KodePinjaman extends Component
{
    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Kode Peminjaman')]
    public string $kode;

    public ?array $loan = null;

    public ?string $qrSvg = null;

    public function mount(string $kode): void
    {
        $this->kode = $kode; // Set kode peminjaman dari parameter URL

        $this->loadLoan(); // Muat data peminjaman berdasarkan kode
    } // Inisialisasi komponen dengan kode peminjaman

    public function render()
    {
        return view('livewire.siswa.kode-pinjaman');
    } // Render tampilan komponen

    protected function loadLoan(): void
    {
        $user = Auth::user(); // Ambil user yang sedang login
        $siswaId = $user?->siswa?->id; // Ambil ID siswa terkait

        abort_if(! $siswaId, 403, 'Akun tidak memiliki data siswa.'); // Hentikan jika tidak ada data siswa

        $loan = Peminjaman::query() // Query data peminjaman
            ->with([ // Muat relasi yang diperlukan
                'items.buku.author', // Author buku
                'items.buku.kategori', // Kategori buku
                'items.buku.penerbit', // Penerbit buku
                'guru.user', // User guru yang menyetujui
            ])
            ->where('kode', $this->kode) // Filter berdasarkan kode peminjaman
            ->where('siswa_id', $siswaId) // Filter berdasarkan ID siswa
            ->first(); // Ambil data peminjaman

        abort_if(! $loan, 404, 'Peminjaman tidak ditemukan.'); // Hentikan jika peminjaman tidak ditemukan

        $this->loan = [ // Format data peminjaman untuk tampilan
            'id' => $loan->id,
            'kode' => $loan->kode,
            'status' => $loan->status,
            'created_at' => $loan->created_at,
            'accepted_at' => $loan->accepted_at,
            'due_at' => $loan->due_at,
            'guru' => $loan->guru?->user?->nama_user, // Nama guru yang menyetujui
            'items' => $loan->items->map(fn ($item) => [ // Daftar item peminjaman
                'id' => $item->buku_id, // ID buku
                'judul' => $item->buku->nama_buku, // Judul buku
                'author' => $item->buku->author?->nama_author, // Author buku
                'kategori' => $item->buku->kategori?->nama_kategori_buku, // Kategori buku
            ])->toArray(), // Konversi ke array
        ];

        $payload = [ // Buat payload untuk QR code
            'code' => $loan->kode, // Kode peminjaman
            'loan_id' => $loan->id, // ID peminjaman
            'student_id' => $loan->siswa_id, // ID siswa
            'books' => $loan->items->map(fn ($item) => [ // Daftar buku yang dipinjam
                'id' => $item->buku_id, // ID buku
                'title' => $item->buku->nama_buku, // Judul buku
            ])->values()->all(), // Kumpulkan semua buku
            'generated_at' => $loan->created_at?->toIso8601String(), // Waktu pembuatan dalam format ISO
        ];

        $this->qrSvg = QrCode::format('svg') // Generate QR code dalam format SVG
            ->size(240) // Ukuran 240x240 pixel
            ->margin(2) // Margin 2
            ->generate(json_encode($payload, JSON_THROW_ON_ERROR)); // Encode payload ke JSON dan generate QR
    } // Muat data peminjaman dan generate QR code
}
