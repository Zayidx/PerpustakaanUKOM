<?php

namespace App\Livewire\Guru;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

class ScanPeminjaman extends Component
{
    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Scan Peminjaman')]
    public ?array $loan = null;

    public ?string $lastPayload = null;

    public ?string $errorMessage = null;

    #[On('qr-scanned')]
    public function handleScan(mixed $event): void
    {
        $this->reset(['errorMessage', 'loan', 'lastPayload']);

        $payload = is_string($event) ? $event : ($event['payload'] ?? null);
        $data = $payload ? json_decode($payload, true) : null;

        if (! is_array($data) || empty($data['code'])) {
            $this->errorMessage = 'QR code tidak valid.';
            return;
        }

        $user = Auth::user();
        $guru = $user?->guru;

        if (! $guru) {
            $this->errorMessage = 'Akun guru belum memiliki data guru.';
            return;
        }

        $loan = Peminjaman::query()
            ->with(['items.buku', 'siswa.user', 'siswa.kelas'])
            ->where('kode', $data['code'])
            ->first();

        if (! $loan) {
            $this->errorMessage = 'Data peminjaman tidak ditemukan.';
            return;
        }

        if (isset($data['loan_id']) && (int) $data['loan_id'] !== $loan->id) {
            $this->errorMessage = 'Kode peminjaman tidak cocok.';
            return;
        }

        if ($loan->status === 'pending') {
            DB::transaction(function () use ($loan, $guru) {
                $loan->update([
                    'status' => 'accepted',
                    'guru_id' => $guru->id,
                    'accepted_at' => now(),
                    'due_at' => now()->addWeek(),
                ]);
            });

            $loan->refresh();
        }

        $this->loan = [
            'id' => $loan->id,
            'kode' => $loan->kode,
            'status' => $loan->status,
            'accepted_at' => $loan->accepted_at,
            'due_at' => $loan->due_at,
            'returned_at' => $loan->returned_at,
            'student_name' => $loan->siswa?->user?->nama_user,
            'student_class' => optional($loan->siswa?->kelas)->nama_kelas ?? '-',
            'books' => $loan->items->map(fn ($item) => [
                'id' => $item->buku_id,
                'title' => $item->buku->nama_buku,
            ])->values()->all(),
        ];

        $this->lastPayload = $payload;
    }

    #[On('qr-scanner-error')]
    public function handleScannerError(mixed $event = null): void
    {
        $message = is_string($event) ? $event : ($event['message'] ?? null);
        $this->errorMessage = $message ?: null;
    }

    public function render()
    {
        return view('livewire.guru.scan-peminjaman');
    }
}
