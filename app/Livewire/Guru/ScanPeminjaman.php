<?php

namespace App\Livewire\Guru;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Buku;
use App\Models\Peminjaman;

class ScanPeminjaman extends Component
{
    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Scan Peminjaman')]
    public ?array $loan = null;

    public ?string $lastPayload = null;

    public ?string $errorMessage = null;

    public string $manualCode = '';

    public ?array $scanNotification = null;
    
    protected array $messages = [
        'manualCode.required' => 'Kode peminjaman wajib diisi.',
        'manualCode.digits' => 'Kode peminjaman harus terdiri dari 6 angka.',
    ];

    #[On('qr-scanned')]
    public function handleScan(mixed $event): void
    {
        $this->reset(['errorMessage', 'loan', 'lastPayload']); // Reset state sebelum memproses scan baru
        $this->clearScanNotification();

        $payload = is_string($event) ? $event : ($event['payload'] ?? null); // Ambil payload dari event
        $data = $payload ? json_decode($payload, true) : null; // Decode payload JSON

        if (! is_array($data) || empty($data['code'])) { // Validasi format data QR
            $this->errorMessage = 'QR code tidak valid.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $this->processLoanData($data, $payload); // Proses data peminjaman
    } // Tangani event scan QR code untuk peminjaman

    #[On('qr-scanner-error')]
    public function handleScannerError(mixed $event = null): void
    {
        $message = null;

        if (is_string($event)) {
            $message = $event;
        } elseif (is_array($event)) {
            $message = $event['message'] ?? ($event[0] ?? null);
        }

        $this->errorMessage = $message ?: null; // Tetapkan pesan error atau null jika tidak ada
        if ($this->errorMessage) {
            $this->notifyScanResult('error', $this->errorMessage);
        }
    } // Tangani event error scanner QR

    public function processManualCode(): void
    {
        $this->reset(['errorMessage', 'loan', 'lastPayload']); // Reset state sebelum memproses form
        $this->clearScanNotification();
        $this->resetErrorBag();

        $validated = $this->validate([
            'manualCode' => ['required', 'digits:6'],
        ]);

        $code = trim($validated['manualCode']);

        $this->processLoanData(['code' => $code]); // Proses kode manual
    } // Proses kode peminjaman secara manual

    private function processLoanData(array $data, ?string $payload = null): void
    {
        $user = Auth::user(); // Ambil user yang sedang login
        $guru = $user?->guru; // Ambil data guru terkait

        if (! $guru) { // Cek apakah user memiliki data guru
            $this->errorMessage = 'Akun guru belum memiliki data guru.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $loan = Peminjaman::query() // Cari peminjaman berdasarkan kode
            ->with(['items.buku', 'siswa.user', 'siswa.kelas'])
            ->where('kode', $data['code'])
            ->first();

        if (! $loan) {
            $this->errorMessage = 'Data peminjaman tidak ditemukan.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        if (isset($data['loan_id']) && (int) $data['loan_id'] !== $loan->id) { // Validasi id peminjaman cocok
            $this->errorMessage = 'Kode peminjaman tidak cocok.';
            $this->notifyScanResult('error', $this->errorMessage);
            return;
        }

        $initialStatus = $loan->status;

        if ($loan->status === 'pending') { // Jika status masih pending, update ke accepted
            try {
                DB::transaction(function () use ($loan, $guru) { // Jalankan dalam transaksi database
                    $items = $loan->items()->with('buku')->get(); // Ambil item peminjaman terbaru
                    $bookIds = $items->pluck('buku_id')->all(); // Kumpulkan ID buku

                    $books = Buku::query()
                        ->whereIn('id', $bookIds)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id'); // Kunci buku terkait untuk mencegah race condition

                    $insufficientStock = []; // Daftar buku yang stoknya tidak cukup

                    foreach ($items as $item) { // Validasi stok buku yang dipinjam
                        $book = $books->get($item->buku_id);

                        if (! $book || $book->stok < $item->quantity) {
                            $insufficientStock[] = $item->buku?->nama_buku ?? 'ID '.$item->buku_id;
                        }
                    }

                    if (! empty($insufficientStock)) { // Jika ada stok yang tidak cukup, batalkan
                        throw ValidationException::withMessages([
                            'stock' => 'Stok buku berikut tidak mencukupi: '.implode(', ', $insufficientStock),
                        ]);
                    }

                    foreach ($items as $item) { // Kurangi stok buku yang dipinjam
                        $book = $books->get($item->buku_id);

                        if ($book) {
                            $book->decrement('stok', $item->quantity);
                        }
                    }

                    $loan->update([ // Update status peminjaman
                        'status' => 'accepted',
                        'guru_id' => $guru->id,
                        'accepted_at' => now(),
                        'due_at' => now()->addWeek(),
                    ]);
                });

                $loan->refresh(); // Refresh data dari database
            } catch (ValidationException $exception) {
                $this->errorMessage = $exception->errors()['stock'][0] ?? 'Stok buku tidak mencukupi untuk memproses peminjaman.';
                $this->loan = $this->formatLoan($loan); // Tampilkan data peminjaman saat ini
                $this->lastPayload = $payload; // Simpan payload terakhir
                $this->notifyScanResult('error', $this->errorMessage);
                return;
            }
        }

        $this->loan = $this->formatLoan($loan); // Format data peminjaman untuk ditampilkan
        $this->lastPayload = $payload; // Simpan payload terakhir untuk referensi

        $message = $initialStatus === 'pending'
            ? 'Peminjaman berhasil dikonfirmasi dan stok buku diperbarui.'
            : 'Data peminjaman berhasil ditampilkan.';

        $this->notifyScanResult('success', $message);
    }

    private function formatLoan(Peminjaman $loan): array
    {
        $loan->loadMissing(['items.buku', 'siswa.user', 'siswa.kelas']); // Pastikan relasi yang dibutuhkan dimuat

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
    } // Format data peminjaman untuk ditampilkan

    public function render()
    {
        return view('livewire.guru.scan-peminjaman');
    } // Render tampilan komponen

    #[On('loan-clear-scan-notification')]
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

        $this->dispatch('loan-show-scan-modal');
    }
}
