<div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Menunggu Konfirmasi</p>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-semibold">{{ number_format($stats['pending'] ?? 0) }}</span>
                        <i class="bi bi-hourglass-split text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Sedang Dipinjam</p>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-semibold">{{ number_format($stats['accepted'] ?? 0) }}</span>
                        <i class="bi bi-journal-text text-primary"></i>
                    </div>
                    <small class="text-danger">
                        {{ ($stats['overdue'] ?? 0) > 0 ? ($stats['overdue'].' terlambat') : 'Tidak ada keterlambatan' }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Dikembalikan</p>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-semibold text-success">{{ number_format($stats['returned'] ?? 0) }}</span>
                        <i class="bi bi-check-circle text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Dibatalkan</p>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-semibold text-muted">{{ number_format($stats['cancelled'] ?? 0) }}</span>
                        <i class="bi bi-x-circle text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($alertMessage)
        <div class="alert alert-{{ $alertType }} alert-dismissible fade show" role="alert">
            {{ $alertMessage }}
            <button type="button" class="btn-close" aria-label="Close" wire:click="clearAlert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input
                                    type="search"
                                    class="form-control"
                                    placeholder="Cari kode, nama siswa, atau NIS"
                                    wire:model.live.debounce.400ms="search"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" wire:model.live="statusFilter">
                                <option value="all">Semua status</option>
                                <option value="pending">Menunggu</option>
                                <option value="accepted">Dipinjam</option>
                                <option value="overdue">Terlambat</option>
                                <option value="returned">Dikembalikan</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm"
                                wire:click="refreshBoard"
                                wire:loading.attr="disabled"
                            >
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Segarkan
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Peminjam</th>
                                    <th>Status</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Buku</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($loans as $loan)
                                    @php
                                        $rowSelected = $selectedLoanId === $loan->id;
                                        $statusClass = match ($loan->status) {
                                            'pending' => 'bg-warning text-dark',
                                            'accepted' => 'bg-success',
                                            'returned' => 'bg-secondary',
                                            'cancelled' => 'bg-danger',
                                            default => 'bg-light text-dark',
                                        };
                                        $isOverdueRow = $loan->status === 'accepted'
                                            && $loan->due_at
                                            && now()->greaterThan($loan->due_at);
                                    @endphp
                                    <tr class="{{ $rowSelected ? 'table-active' : '' }}" wire:key="loan-row-{{ $loan->id }}">
                                        <td class="fw-semibold">
                                            {{ $loan->kode }}
                                            <div class="text-muted small">
                                                {{ optional($loan->created_at)->translatedFormat('d M Y H:i') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">
                                                {{ $loan->siswa?->user?->nama_user ?? '-' }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $loan->siswa?->nis ?? '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($loan->due_at)
                                                <div class="{{ $isOverdueRow ? 'text-danger fw-semibold' : '' }}">
                                                    {{ optional($loan->due_at)->translatedFormat('d M Y') }}
                                                </div>
                                                <small class="text-muted">
                                                    {{ optional($loan->due_at)->diffForHumans() }}
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0 small">
                                                @foreach ($loan->items->take(3) as $item)
                                                    <li>• {{ $item->buku->nama_buku }}</li>
                                                @endforeach
                                            </ul>
                                            @if ($loan->items->count() > 3)
                                                <span class="text-muted small">
                                                    +{{ $loan->items->count() - 3 }} lainnya
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-primary"
                                                    wire:click="showLoan({{ $loan->id }})"
                                                >
                                                    Detail
                                                </button>
                                                @if ($loan->status === 'accepted')
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-success"
                                                        wire:click="markAsReturned({{ $loan->id }})"
                                                        wire:confirm="Tandai peminjaman ini telah dikembalikan?"
                                                        wire:loading.attr="disabled"
                                                        wire:target="markAsReturned"
                                                    >
                                                        Selesai
                                                    </button>
                                                @elseif ($loan->status === 'pending')
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-danger"
                                                        wire:click="cancelLoan({{ $loan->id }})"
                                                        wire:confirm="Batalkan permintaan peminjaman ini?"
                                                        wire:loading.attr="disabled"
                                                        wire:target="cancelLoan"
                                                    >
                                                        Batalkan
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Belum ada data peminjaman.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $loans->links() }}
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Detail Peminjaman</span>
                    @if ($selectedLoan)
                        @php
                            $detailBadge = match ($selectedLoan['status']) {
                                'pending' => 'bg-warning text-dark',
                                'accepted' => 'bg-success',
                                'returned' => 'bg-secondary',
                                'cancelled' => 'bg-danger',
                                default => 'bg-light text-dark',
                            };
                        @endphp
                        <span class="badge {{ $detailBadge }}">
                            {{ ucfirst($selectedLoan['status']) }}
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($selectedLoan)
                        @if (($selectedLoan['late_days'] ?? 0) > 0 && $selectedLoan['status'] === 'accepted')
                            <div class="alert alert-warning">
                                Terlambat {{ $selectedLoan['late_days'] }} hari.
                                Denda sementara: Rp{{ number_format($selectedLoan['late_fee'] ?? 0, 0, ',', '.') }}.
                                Sampaikan nominal ini ke siswa sebelum menyelesaikan pengembalian.
                            </div>
                        @endif
                        <div class="mb-3">
                            <h5 class="mb-0">{{ $selectedLoan['kode'] }}</h5>
                            <small class="text-muted">
                                Dibuat {{ optional($selectedLoan['created_at'])->translatedFormat('d F Y H:i') }}
                            </small>
                        </div>
                        <dl class="row small mb-0">
                            <dt class="col-4">Peminjam</dt>
                            <dd class="col-8 mb-2">
                                <div class="fw-semibold">{{ $selectedLoan['student']['name'] ?? '-' }}</div>
                                <div class="text-muted">NIS: {{ $selectedLoan['student']['nis'] ?? '-' }}</div>
                                <div class="text-muted">Kelas: {{ $selectedLoan['student']['class'] ?? '-' }}</div>
                            </dd>
                            <dt class="col-4">Petugas</dt>
                            <dd class="col-8 mb-2">
                                {{ $selectedLoan['guru'] ?? '-' }}
                            </dd>
                            <dt class="col-4">Jatuh Tempo</dt>
                            <dd class="col-8 mb-2">
                                @if ($selectedLoan['due_at'])
                                    <span class="{{ $selectedLoan['is_overdue'] ? 'text-danger fw-semibold' : '' }}">
                                        {{ optional($selectedLoan['due_at'])->translatedFormat('d F Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </dd>
                            <dt class="col-4">Dikembalikan</dt>
                            <dd class="col-8 mb-2">
                                {{ $selectedLoan['returned_at'] ? optional($selectedLoan['returned_at'])->translatedFormat('d F Y H:i') : '-' }}
                            </dd>
                        </dl>
                        <hr>
                        <h6 class="mb-2">Buku Dipinjam ({{ $selectedLoan['total_books'] }})</h6>
                        <ul class="list-group list-group-flush small">
                            @foreach ($selectedLoan['items'] as $item)
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span>{{ $item['title'] }}</span>
                                    <span class="text-muted">x{{ $item['quantity'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">
                            Pilih salah satu peminjaman di tabel untuk melihat detailnya.
                        </p>
                    @endif
                </div>
                @if ($selectedLoan)
                    <div class="card-footer d-flex flex-column gap-2">
                        @if ($selectedLoan['can_mark_returned'])
                            <button
                                type="button"
                                class="btn btn-success"
                                wire:click="markAsReturned({{ $selectedLoan['id'] }})"
                                wire:confirm="Tandai peminjaman ini telah dikembalikan?"
                                wire:loading.attr="disabled"
                                wire:target="markAsReturned"
                            >
                                Tandai Dikembalikan
                            </button>
                        @endif
                        @if ($selectedLoan['can_cancel'])
                            <button
                                type="button"
                                class="btn btn-outline-danger"
                                wire:click="cancelLoan({{ $selectedLoan['id'] }})"
                                wire:confirm="Batalkan permintaan peminjaman ini?"
                                wire:loading.attr="disabled"
                                wire:target="cancelLoan"
                            >
                                Batalkan Permintaan
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
