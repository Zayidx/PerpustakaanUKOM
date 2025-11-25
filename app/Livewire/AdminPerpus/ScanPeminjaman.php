<?php

namespace App\Livewire\AdminPerpus;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Support\QrPayloadSignature;

class ScanPeminjaman extends Component
{
    private const QR_MAX_AGE_MINUTES = 43200; // 30 hari

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Scan Peminjaman')]
    public ?array $loan = null;

    public ?string $lastPayload = null;

    public ?string $errorMessage = null;

    public string $manualCode = '';

    protected array $messages = [
        'manualCode.required' => 'Kode peminjaman wajib diisi.',
        'manualCode.digits' => 'Kode peminjaman harus terdiri dari 6 angka.',
    ];

    #[On('qr-scanned')]
    public function handleScan(mixed $event): void
    {
        $this->reset(['errorMessage', 'loan', 'lastPayload']);

        $payload = is_string($event) ? $event : ($event['payload'] ?? null); 
        $data = $payload ? json_decode($payload, true) : null; 

        if (! is_array($data) || empty($data['code'])) { 
            $this->errorMessage = 'QR code tidak valid.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        if (! QrPayloadSignature::isValid($data, self::QR_MAX_AGE_MINUTES)) {
            $this->errorMessage = 'QR code tidak dikenali atau sudah kedaluwarsa.';
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
        $this->reset(['errorMessage', 'loan', 'lastPayload']);
        $this->resetErrorBag();

        $validated = $this->validate([
            'manualCode' => ['required', 'digits:6'],
        ]);

        $code = trim($validated['manualCode']);

        $this->processLoanData(['code' => $code]); 
    } 

    private function processLoanData(array $data, ?string $payload = null): void
    {
        $user = Auth::user(); 
        $adminPerpus = $user?->adminPerpus; 

        if (! $adminPerpus) { 
            $this->errorMessage = 'Akun Admin Perpus belum memiliki data Admin Perpus.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        if (isset($data['action']) && $data['action'] !== 'borrow') {
            $this->errorMessage = 'QR ini bukan untuk peminjaman.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $loan = Peminjaman::query()
            ->with(['items.buku', 'siswa.user', 'siswa.kelas'])
            ->where('kode', $data['code'])
            ->where(function ($query) use ($adminPerpus) {
                $query->whereNull('admin_perpus_id')
                    ->orWhere('admin_perpus_id', $adminPerpus->id);
            })
            ->first();

        if (! $loan) {
            $this->errorMessage = 'Data peminjaman tidak ditemukan.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        if (isset($data['loan_id']) && (int) $data['loan_id'] !== $loan->id) { 
            $this->errorMessage = 'Kode peminjaman tidak cocok.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        if (isset($data['student_id']) && (int) $data['student_id'] !== (int) $loan->siswa_id) {
            $this->errorMessage = 'QR peminjaman tidak sesuai pemilik.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $initialStatus = $loan->status;

        if ($loan->status === 'pending') { 
            try {
                DB::transaction(function () use ($loan, $adminPerpus) { 
                    $items = $loan->items()->with('buku')->get(); 
                    $bookIds = $items->pluck('buku_id')->all(); 

                    $books = Buku::query()
                        ->whereIn('id', $bookIds)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id'); 

                    $insufficientStock = []; 

                    foreach ($items as $item) { 
                        $book = $books->get($item->buku_id);

                        if (! $book || $book->stok < $item->quantity) {
                            $insufficientStock[] = $item->buku?->nama_buku ?? 'ID '.$item->buku_id;
                        }
                    }

                    if (! empty($insufficientStock)) { 
                        throw ValidationException::withMessages([
                            'stock' => 'Stok buku berikut tidak mencukupi: '.implode(', ', $insufficientStock),
                        ]);
                    }

                    foreach ($items as $item) { 
                        $book = $books->get($item->buku_id);

                        if ($book) {
                            $book->decrement('stok', $item->quantity);
                        }
                    }

                    $loan->update([ 
                        'status' => 'accepted',
                        'admin_perpus_id' => $adminPerpus->id,
                        'accepted_at' => now(),
                        'due_at' => now()->addWeek(),
                    ]);
                });

                $loan->refresh(); 
            } catch (ValidationException $exception) {
                $this->errorMessage = $exception->errors()['stock'][0] ?? 'Stok buku tidak mencukupi untuk memproses peminjaman.';
                $this->loan = $this->formatLoan($loan); 
                $this->lastPayload = $payload; 
                $this->notifyScanResult('error', $this->errorMessage);
                return;
            }
        }

        $this->loan = $this->formatLoan($loan); 
        $this->lastPayload = $payload; 

        $message = $initialStatus === 'pending'
            ? 'Peminjaman berhasil dikonfirmasi dan stok buku diperbarui.'
            : 'Data peminjaman berhasil ditampilkan.';

        $this->notifyScanResult('success', $message);
    }

    private function formatLoan(Peminjaman $loan): array
    {
        $loan->loadMissing(['items.buku', 'siswa.user', 'siswa.kelas']); 

        return [
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
    } 

    public function render()
    {
        return view('livewire.admin-perpus.scan-peminjaman');
    } 

    private function notifyScanResult(string $type, ?string $message): void
    {
        if (! $message) {
            return;
        }

        $this->dispatch('notify', type: $type, message: $message);
    }
}
