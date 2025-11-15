<?php

namespace App\Livewire\Guru;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\PeminjamanPenalty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Carbon;

class ScanPengembalian extends Component
{
    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Scan Pengembalian')]
    public ?array $loan = null;

    public ?string $errorMessage = null;

    public ?string $lastPayload = null;

    public string $manualCode = '';

    public ?array $lateInfo = null;

    public ?array $pendingReturn = null;

    public ?array $scanNotification = null;

    protected array $messages = [
        'manualCode.required' => 'Kode pengembalian wajib diisi.',
        'manualCode.digits' => 'Kode pengembalian harus terdiri dari 6 angka.',
    ];

    #[On('qr-scanned')]
    public function handleScan(mixed $event): void
    {
        $this->reset(['errorMessage', 'loan', 'lastPayload', 'lateInfo', 'pendingReturn']);
        $this->clearScanNotification();

        $payload = is_string($event) ? $event : ($event['payload'] ?? null);
        $data = $payload ? json_decode($payload, true) : null;

        if (! is_array($data) || empty($data['code'])) {
            $this->errorMessage = 'QR code tidak valid.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        if (isset($data['action']) && $data['action'] !== 'return') {
            $this->errorMessage = 'QR ini bukan untuk pengembalian.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $this->processLoanData($data, $payload);
    }

    #[On('qr-scanner-error')]
    public function handleScannerError(mixed $event = null): void
    {
        $message = null;

        if (is_string($event)) {
            $message = $event;
        } elseif (is_array($event)) {
            $message = $event['message'] ?? ($event[0] ?? null);
        }

        $this->errorMessage = $message ?: null;
        if ($this->errorMessage) {
            $this->notifyScanResult('error', $this->errorMessage);
        }
    }

    public function processManualCode(): void
    {
        $this->reset(['errorMessage', 'loan', 'lastPayload', 'lateInfo', 'pendingReturn']);
        $this->clearScanNotification();
        $this->resetErrorBag();

        $validated = $this->validate([
            'manualCode' => ['required', 'digits:6'],
        ]);

        $code = trim($validated['manualCode']);

        $this->processLoanData([
            'code' => $code,
            'action' => 'return',
        ]);
    }

    private function processLoanData(array $data, ?string $payload = null): void
    {
        $user = Auth::user();
        $guru = $user?->guru;

        if (! $guru) {
            $this->errorMessage = 'Akun guru belum memiliki data guru.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $loan = Peminjaman::query()
            ->with(['items.buku', 'siswa.user', 'siswa.kelas'])
            ->where('kode', $data['code'])
            ->first();

        if (! $loan) {
            $this->errorMessage = 'Data peminjaman tidak ditemukan.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        if ($loan->status !== 'accepted') {
            $this->errorMessage = 'Peminjaman ini tidak dalam status dipinjam.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $lateInfo = $this->calculateLateInfo($loan);
        $this->lateInfo = $lateInfo;

        if ($lateInfo['late_fee'] > 0 && ($this->pendingReturn['loan_id'] ?? null) !== $loan->id) {
            $this->pendingReturn = [
                'loan_id' => $loan->id,
                'payload' => $payload,
                'late_days' => $lateInfo['late_days'],
                'late_fee' => $lateInfo['late_fee'],
            ];

            $this->loan = $this->formatLoan($loan, $lateInfo);
            $this->dispatch('show-late-modal');
            $this->notifyScanResult(
                'warning',
                'Konfirmasi pembayaran denda sebelum menyelesaikan pengembalian.'
            );
            return;
        }

        $this->completeReturn($loan, $lateInfo, $payload);
    }

    private function formatLoan(Peminjaman $loan, ?array $lateInfo = null): array
    {
        $loan->loadMissing(['items.buku', 'siswa.user', 'siswa.kelas']);

        return [
            'id' => $loan->id,
            'kode' => $loan->kode,
            'status' => $loan->status,
            'returned_at' => $loan->returned_at,
            'student_name' => $loan->siswa?->user?->nama_user,
            'student_class' => optional($loan->siswa?->kelas)->nama_kelas ?? '-',
            'late_days' => $lateInfo['late_days'] ?? 0,
            'late_fee' => $lateInfo['late_fee'] ?? 0,
            'books' => $loan->items->map(fn ($item) => [
                'id' => $item->buku_id,
                'title' => $item->buku->nama_buku,
            ])->values()->all(),
        ];
    }

    public function render()
    {
        return view('livewire.guru.scan-pengembalian');
    }

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

    public function confirmLateFee(): void
    {
        if (! $this->pendingReturn) {
            return;
        }

        $loan = Peminjaman::with(['items.buku', 'siswa.user', 'siswa.kelas'])
            ->find($this->pendingReturn['loan_id']);

        if (! $loan) {
            $this->cancelLateFee();
            $this->errorMessage = 'Data peminjaman tidak ditemukan.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $lateInfo = $this->calculateLateInfo($loan);
        $this->completeReturn($loan, $lateInfo, $this->pendingReturn['payload'] ?? null);
        $this->pendingReturn = null;
        $this->dispatch('hide-late-modal');
    }

    public function cancelLateFee(): void
    {
        $this->pendingReturn = null;
        $this->dispatch('hide-late-modal');
        $this->errorMessage = 'Pengembalian dibatalkan. Pastikan denda dibayar terlebih dahulu.';
        $this->notifyScanResult('error', $this->errorMessage);
    }

    private function completeReturn(Peminjaman $loan, array $lateInfo, ?string $payload = null): void
    {
        try {
            DB::transaction(function () use ($loan, $lateInfo) {
                $items = $loan->items()->with('buku')->get();
                $bookIds = $items->pluck('buku_id')->all();

                $books = Buku::query()
                    ->whereIn('id', $bookIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $book = $books->get($item->buku_id);

                    if ($book) {
                        $book->increment('stok', $item->quantity);
                    }
                }

                $loan->update([
                    'status' => 'returned',
                    'returned_at' => now(),
                ]);

                if ($lateInfo['late_fee'] > 0) {
                    PeminjamanPenalty::create([
                        'peminjaman_id' => $loan->id,
                        'guru_id' => Auth::user()?->guru?->id,
                        'late_days' => $lateInfo['late_days'],
                        'amount' => $lateInfo['late_fee'],
                        'paid_at' => now(),
                    ]);
                }
            });

            $loan->refresh();
        } catch (\Throwable $exception) {
            report($exception);
            $this->errorMessage = 'Terjadi kesalahan saat memproses pengembalian.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $this->lateInfo = $lateInfo;
        $this->loan = $this->formatLoan($loan, $lateInfo);
        $this->lastPayload = $payload;
        $this->pendingReturn = null;
        $this->errorMessage = null;
        $this->pendingReturn = null;
        $this->notifyScanResult('success', 'Pengembalian berhasil diselesaikan.');
    }

    #[On('return-clear-scan-notification')]
    public function clearScanNotification(): void
    {
        $this->scanNotification = null;
    }

    private function notifyScanResult(string $type, ?string $message): void
    {
        if (! $message) {
            return;
        }

        $this->scanNotification = [
            'type' => $type,
            'message' => $message,
        ];

        $this->dispatch('return-show-scan-modal');
    }
}
