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
    public ?string $lastKnownStatus = null;

    public function mount(string $kode): void
    {
        $this->kode = $kode; 

        $this->loadLoan(); 
    } 

    public function refreshLoan(): void
    {
        $previousStatus = $this->lastKnownStatus;

        $this->loadLoan();

        if (! $previousStatus || ! $this->lastKnownStatus || $previousStatus === $this->lastKnownStatus) {
            return;
        }

        if ($this->lastKnownStatus === 'accepted') {
            $this->dispatch('loan-status-updated', type: 'success', message: 'Peminjaman berhasil dipindai dan disetujui.');
            return;
        }

        if ($this->lastKnownStatus === 'cancelled') {
            $this->dispatch('loan-status-updated', type: 'error', message: 'Peminjaman dibatalkan oleh petugas.');
            return;
        }

        $this->dispatch('loan-status-updated', type: 'info', message: 'Status peminjaman diperbarui.');
    }

    public function render()
    {
        return view('livewire.siswa.kode-pinjaman');
    } 

    protected function loadLoan(): void
    {
        $user = Auth::user(); 
        $siswaId = $user?->siswa?->id; 

        abort_if(! $siswaId, 403, 'Akun tidak memiliki data siswa.'); 

        $loan = Peminjaman::query() 
            ->with([ 
                'items.buku.author', 
                'items.buku.kategori', 
                'items.buku.penerbit', 
                'adminPerpus.user', 
            ])
            ->where('kode', $this->kode) 
            ->where('siswa_id', $siswaId) 
            ->first(); 

        abort_if(! $loan, 404, 'Peminjaman tidak ditemukan.'); 

        $this->loan = [ 
            'id' => $loan->id,
            'kode' => $loan->kode,
            'status' => $loan->status,
            'created_at' => $loan->created_at,
            'accepted_at' => $loan->accepted_at,
            'due_at' => $loan->due_at,
            'admin_perpus' => $loan->adminPerpus?->user?->nama_user, 
            'items' => $loan->items->map(fn ($item) => [ 
                'id' => $item->buku_id, 
                'judul' => $item->buku->nama_buku, 
                'author' => $item->buku->author?->nama_author, 
                'kategori' => $item->buku->kategori?->nama_kategori_buku, 
            ])->toArray(), 
        ];

        $this->lastKnownStatus = $this->loan['status'];

        $payload = [ 
            'code' => $loan->kode, 
            'loan_id' => $loan->id, 
            'student_id' => $loan->siswa_id, 
            'books' => $loan->items->map(fn ($item) => [ 
                'id' => $item->buku_id, 
                'title' => $item->buku->nama_buku, 
            ])->values()->all(), 
            'generated_at' => $loan->created_at?->toIso8601String(), 
        ];

        $this->qrSvg = QrCode::format('svg') 
            ->size(240) 
            ->margin(2) 
            ->generate(json_encode($payload, JSON_THROW_ON_ERROR)); 
    } 
}
