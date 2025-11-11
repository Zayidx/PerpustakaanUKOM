<?php

namespace App\Livewire\Siswa;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ListPeminjaman extends Component
{
    use WithPagination;

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Peminjaman Saya')]
    public string $statusFilter = 'all';

    public ?array $returnTicket = null;

    public ?string $returnQrSvg = null;

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingStatusFilter(): void
    {
        $this->resetPage(); // Reset pagination ke halaman pertama saat filter status berubah
    } // Reset pagination saat filter status peminjaman diubah

    public function showReturnTicket(int $loanId): void
    {
        $user = Auth::user();
        $siswaId = $user?->siswa?->id;

        abort_if(! $siswaId, 403, 'Akun tidak memiliki data siswa.');

        $loan = Peminjaman::query()
            ->with(['items.buku.author', 'items.buku.kategori'])
            ->where('siswa_id', $siswaId)
            ->where('id', $loanId)
            ->firstOrFail();

        if ($loan->status !== 'accepted') {
            $this->addError('returnTicket', 'Kode pengembalian hanya tersedia untuk peminjaman yang sedang dipinjam.');
            return;
        }

        $lateInfo = $this->calculateLateInfo($loan);

        $payload = [
            'code' => $loan->kode,
            'loan_id' => $loan->id,
            'student_id' => $loan->siswa_id,
            'action' => 'return',
            'books' => $loan->items->map(fn ($item) => [
                'id' => $item->buku_id,
                'title' => $item->buku->nama_buku,
            ])->values()->all(),
            'generated_at' => now()->toIso8601String(),
            'late_days' => $lateInfo['late_days'],
        ];

        $this->returnQrSvg = QrCode::format('svg')
            ->size(240)
            ->margin(2)
            ->generate(json_encode($payload, JSON_THROW_ON_ERROR));

        $this->returnTicket = [
            'id' => $loan->id,
            'kode' => $loan->kode,
            'due_at' => $loan->due_at,
            'status' => $loan->status,
            'late_days' => $lateInfo['late_days'],
            'late_fee' => $lateInfo['late_fee'],
            'items' => $loan->items->map(fn ($item) => [
                'judul' => $item->buku->nama_buku,
                'author' => $item->buku->author?->nama_author,
                'kategori' => $item->buku->kategori?->nama_kategori_buku,
            ])->toArray(),
        ];
    }

    public function clearReturnTicket(): void
    {
        $this->reset(['returnTicket', 'returnQrSvg']);
        $this->resetErrorBag('returnTicket');
    }

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

    private function calculateLateInfo(Peminjaman $loan): array
    {
        $lateDays = 0;

        if ($loan->due_at) {
            $dueDate = $loan->due_at->copy()->startOfDay();
            $today = Carbon::now()->startOfDay();

            if ($today->greaterThan($dueDate)) {
                $lateDays = $dueDate->diffInDays($today);
            }
        }

        return [
            'late_days' => $lateDays,
            'late_fee' => $lateDays * 1000,
        ];
    }
}
