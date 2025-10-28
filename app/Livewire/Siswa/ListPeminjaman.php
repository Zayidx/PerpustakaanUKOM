<?php

namespace App\Livewire\Siswa;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ListPeminjaman extends Component
{
    use WithPagination;

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Peminjaman Saya')]
    public string $statusFilter = 'all';

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingStatusFilter(): void
    {
        $this->resetPage(); // Reset pagination ke halaman pertama saat filter status berubah
    } // Reset pagination saat filter status peminjaman diubah

    public function render()
    {
        $user = Auth::user(); // Ambil user yang sedang login
        $siswaId = $user?->siswa?->id; // Ambil ID siswa terkait

        abort_if(! $siswaId, 403, 'Akun tidak memiliki data siswa.'); // Hentikan jika tidak ada data siswa

        $loans = Peminjaman::query() // Query data peminjaman
            ->with(['items.buku']) // Muat relasi item dan buku
            ->where('siswa_id', $siswaId) // Filter berdasarkan ID siswa
            ->when($this->statusFilter !== 'all', function ($query) { // Jika filter status bukan 'all'
                $query->where('status', $this->statusFilter); // Filter berdasarkan status peminjaman
            })
            ->latest('created_at') // Urutkan berdasarkan created_at terbaru
            ->paginate(10); // Pagination 10 peminjaman per halaman

        return view('livewire.siswa.list-peminjaman', [ // Render view dengan data
            'loans' => $loans, // Daftar peminjaman
        ]);
    } // Render tampilan komponen dengan daftar peminjaman
}
