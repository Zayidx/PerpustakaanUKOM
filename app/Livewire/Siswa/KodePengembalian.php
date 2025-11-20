<?php

namespace App\Livewire\Siswa;

use App\Models\Peminjaman;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KodePengembalian extends Component
{
    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Kode Pengembalian')]
    public string $kode;

    public ?array $loan = null;

    public ?string $qrSvg = null;

    public ?array $lateInfo = null;
    public ?string $lastKnownStatus = null;

    public function mount(string $kode): void
    {
        $this->kode = $kode;

        $this->loadLoan();
    }

    public function render()
    {
        return view('livewire.siswa.kode-pengembalian');
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
        abort_if(! in_array($loan->status, ['accepted', 'returned'], true), 404, 'Kode pengembalian hanya tersedia untuk peminjaman yang sedang dipinjam.');

        $lateInfo = $this->calculateLateInfo($loan);
        $this->lateInfo = $lateInfo;

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

        $this->qrSvg = QrCode::format('svg')
            ->size(240)
            ->margin(2)
            ->generate(json_encode($payload, JSON_THROW_ON_ERROR));

        $this->loan = [
            'id' => $loan->id,
            'kode' => $loan->kode,
            'status' => $loan->status,
            'created_at' => $loan->created_at,
            'accepted_at' => $loan->accepted_at,
            'due_at' => $loan->due_at,
            'admin_perpus' => $loan->adminPerpus?->user?->nama_user,
            'late_days' => $lateInfo['late_days'],
            'late_fee' => $lateInfo['late_fee'],
            'items' => $loan->items->map(fn ($item) => [
                'id' => $item->buku_id,
                'judul' => $item->buku->nama_buku,
                'author' => $item->buku->author?->nama_author,
                'kategori' => $item->buku->kategori?->nama_kategori_buku,
            ])->toArray(),
        ];

        $this->lastKnownStatus = $this->loan['status'];
    }

    public function refreshLoan(): void
    {
        $previousStatus = $this->lastKnownStatus;

        $this->loadLoan();

        if (! $previousStatus || ! $this->lastKnownStatus || $previousStatus === $this->lastKnownStatus) {
            return;
        }

        if ($this->lastKnownStatus === 'returned') {
            $this->dispatch('loan-status-updated', type: 'success', message: 'Pengembalian buku selesai diproses.');
        }
    }

    protected function calculateLateInfo(Peminjaman $loan): array
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
