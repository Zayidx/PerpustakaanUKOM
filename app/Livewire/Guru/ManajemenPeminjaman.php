<?php

namespace App\Livewire\Guru;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class ManajemenPeminjaman extends Component
{
    use WithPagination;

    #[Layout('components.layouts.dashboard-layouts')]
    #[Title('Manajemen Peminjaman')]
    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $statusFilter = 'all';

    public ?int $selectedLoanId = null;

    public ?array $selectedLoan = null;

    public ?string $alertMessage = null;

    public string $alertType = 'success';

    /** @var array<string, int> */
    public array $stats = [
        'pending' => 0,
        'accepted' => 0,
        'returned' => 0,
        'cancelled' => 0,
        'overdue' => 0,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        abort_if(! Auth::user()?->guru, 403, 'Akun guru belum memiliki data guru.');

        $this->stats = $this->buildStats();
        $this->selectedLoanId = Peminjaman::latest('created_at')->value('id');
        $this->loadSelectedLoan();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearAlert(): void
    {
        $this->alertMessage = null;
    }

    public function refreshBoard(): void
    {
        $this->stats = $this->buildStats();
        $this->loadSelectedLoan();
    }

    public function showLoan(int $loanId): void
    {
        $this->selectedLoanId = $loanId;
        $this->loadSelectedLoan();
    }

    public function markAsReturned(int $loanId): void
    {
        $this->clearAlert();

        try {
            DB::transaction(function () use ($loanId) {
                $loan = Peminjaman::query()
                    ->whereKey($loanId)
                    ->lockForUpdate()
                    ->with(['items'])
                    ->first();

                if (! $loan) {
                    throw ValidationException::withMessages([
                        'loan' => 'Data peminjaman tidak ditemukan.',
                    ]);
                }

                if ($loan->status !== 'accepted') {
                    throw ValidationException::withMessages([
                        'loan' => 'Hanya peminjaman aktif yang dapat ditandai dikembalikan.',
                    ]);
                }

                $bookIds = $loan->items->pluck('buku_id')->all();

                if (! empty($bookIds)) {
                    $books = Buku::query()
                        ->whereIn('id', $bookIds)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    foreach ($loan->items as $item) {
                        $book = $books->get($item->buku_id);

                        if ($book) {
                            $book->increment('stok', $item->quantity);
                        }
                    }
                }

                $loan->update([
                    'status' => 'returned',
                    'returned_at' => now(),
                ]);
            });

            $this->notify('Peminjaman berhasil ditandai sebagai dikembalikan.');
            $this->refreshAfterMutation($loanId);
        } catch (ValidationException $exception) {
            $this->handleValidationException($exception);
        }
    }

    public function cancelLoan(int $loanId): void
    {
        $this->clearAlert();

        try {
            DB::transaction(function () use ($loanId) {
                $loan = Peminjaman::query()
                    ->whereKey($loanId)
                    ->lockForUpdate()
                    ->first();

                if (! $loan) {
                    throw ValidationException::withMessages([
                        'loan' => 'Data peminjaman tidak ditemukan.',
                    ]);
                }

                if ($loan->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'loan' => 'Hanya peminjaman berstatus menunggu yang dapat dibatalkan.',
                    ]);
                }

                $loan->update([
                    'status' => 'cancelled',
                ]);
            });

            $this->notify('Peminjaman berhasil dibatalkan.');
            $this->refreshAfterMutation($loanId);
        } catch (ValidationException $exception) {
            $this->handleValidationException($exception);
        }
    }

    public function render()
    {
        $loans = Peminjaman::query()
            ->with(['items.buku', 'siswa.user'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('kode', 'like', '%'.$this->search.'%')
                        ->orWhereHas('siswa.user', function ($userQuery) {
                            $userQuery->where('nama_user', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('siswa', function ($studentQuery) {
                            $studentQuery->where('nis', 'like', '%'.$this->search.'%')
                                ->orWhere('nisn', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                if ($this->statusFilter === 'overdue') {
                    $query->where('status', 'accepted')
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now());
                } else {
                    $query->where('status', $this->statusFilter);
                }
            })
            ->latest('created_at')
            ->paginate(10);

        return view('livewire.guru.manajemen-peminjaman', [
            'loans' => $loans,
        ]);
    } // Render tampilan daftar peminjaman beserta aksi guru

    private function buildStats(): array
    {
        $counts = Peminjaman::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $overdue = Peminjaman::query()
            ->where('status', 'accepted')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        return [
            'pending' => (int) ($counts['pending'] ?? 0),
            'accepted' => (int) ($counts['accepted'] ?? 0),
            'returned' => (int) ($counts['returned'] ?? 0),
            'cancelled' => (int) ($counts['cancelled'] ?? 0),
            'overdue' => (int) $overdue,
        ];
    }

    private function loadSelectedLoan(): void
    {
        if (! $this->selectedLoanId) {
            $this->selectedLoan = null;
            return;
        }

        $loan = Peminjaman::with(['items.buku', 'siswa.user', 'siswa.kelas', 'guru.user', 'penalties.guru.user'])
            ->find($this->selectedLoanId);

        if (! $loan) {
            $this->selectedLoan = null;
            $this->selectedLoanId = null;
            return;
        }

        $this->selectedLoan = $this->formatLoan($loan);
    }

    private function refreshAfterMutation(int $loanId): void
    {
        $this->stats = $this->buildStats();
        $this->selectedLoanId = $loanId;
        $this->loadSelectedLoan();
        $this->resetPage();
    }

    private function formatLoan(Peminjaman $loan): array
    {
        $loan->loadMissing(['items.buku', 'siswa.user', 'siswa.kelas', 'guru.user']);

        $isOverdue = $loan->status === 'accepted'
            && $loan->due_at
            && now()->greaterThan($loan->due_at);

        $lateDays = $this->calculateLateDays($loan);

        return [
            'id' => $loan->id,
            'kode' => $loan->kode,
            'status' => $loan->status,
            'created_at' => $loan->created_at,
            'accepted_at' => $loan->accepted_at,
            'due_at' => $loan->due_at,
            'returned_at' => $loan->returned_at,
            'guru' => $loan->guru?->user?->nama_user,
            'student' => [
                'name' => $loan->siswa?->user?->nama_user,
                'nis' => $loan->siswa?->nis,
                'nisn' => $loan->siswa?->nisn,
                'class' => $loan->siswa?->kelas?->nama_kelas ?? '-',
            ],
            'items' => $loan->items->map(fn ($item) => [
                'id' => $item->buku_id,
                'title' => $item->buku->nama_buku,
                'quantity' => $item->quantity,
            ])->values()->all(),
            'total_books' => (int) $loan->items->sum('quantity'),
            'is_overdue' => $isOverdue,
            'late_days' => $lateDays,
            'late_fee' => $lateDays * 1000,
            'can_mark_returned' => $loan->status === 'accepted',
            'can_cancel' => $loan->status === 'pending',
            'penalties' => $loan->penalties->map(function ($penalty) {
                return [
                    'id' => $penalty->id,
                    'amount' => $penalty->amount,
                    'late_days' => $penalty->late_days,
                    'paid_at' => $penalty->paid_at,
                    'guru' => $penalty->guru?->user?->nama_user,
                ];
            })->values()->all(),
        ];
    }

    private function notify(string $message, string $type = 'success'): void
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
    }

    private function handleValidationException(ValidationException $exception): void
    {
        $message = collect($exception->errors())->flatten()->first()
            ?? 'Terjadi kesalahan saat memproses peminjaman.';

        $this->notify($message, 'danger');
    }

    private function calculateLateDays(Peminjaman $loan): int
    {
        if ($loan->status !== 'accepted' || ! $loan->due_at) {
            return 0;
        }

        $due = $loan->due_at->copy()->startOfDay();
        $today = Carbon::now()->startOfDay();

        if ($today->greaterThan($due)) {
            return $due->diffInDays($today);
        }

        return 0;
    }
}
