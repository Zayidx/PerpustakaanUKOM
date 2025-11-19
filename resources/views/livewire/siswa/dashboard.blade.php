<style>
    .stats-icon {
    width: 3rem;
    height: 3rem;
    flex-shrink: 0;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card-body .d-flex {
    min-height: 73px;
}

.stats-icon i {
    transform: translate(-5px, -15px); 
}
</style>
<div>
    <p class="text-muted mb-3">Lihat status peminjaman aktif, riwayat, dan jadwal pengembalian buku.</p>

    <div class="page-content">
        <section class="row gy-4">
            <div class="col-12 col-xl-8">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="stats-icon purple">
                                        <i class="bi bi-bookmark-check"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted">Sedang Dipinjam</h6>
                                        <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="stats-icon blue">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted">Menunggu</h6>
                                        <h4 class="mb-0">{{ number_format($stats['pending']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="stats-icon red">
                                        <i class="bi bi-exclamation-circle"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted">Terlambat</h6>
                                        <h4 class="mb-0">{{ number_format($stats['overdue']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="stats-icon green">
                                        <i class="bi bi-journal-bookmark"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted">Total Peminjaman</h6>
                                        <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="mb-0">Buku yang Sedang Dipinjam</h4>
                    </div>
                    <div class="card-body">
                        @forelse ($currentLoans as $loan)
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="mb-0 fw-semibold">Kode {{ $loan->kode }}</p>
                                        <small class="text-muted">
                                            {{ $loan->items->pluck('buku.nama_buku')->filter()->take(2)->implode(', ') }}
                                            @if ($loan->items->count() > 2)
                                                (+{{ $loan->items->count() - 2 }} lainnya)
                                            @endif
                                        </small>
                                    </div>
                                    <span class="badge bg-light text-dark border">
                                        Jatuh tempo {{ optional($loan->due_at)->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">Belum ada buku yang sedang dipinjam.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="mb-0">Riwayat Aktivitas</h4>
                    </div>
                    <div class="card-body">
                        @forelse ($recentHistory as $loan)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="mb-0 fw-semibold">Kode {{ $loan->kode }}</p>
                                        <small class="text-muted">
                                            {{ $loan->items->pluck('buku.nama_buku')->filter()->first() ?? 'Koleksi buku' }}
                                        </small>
                                    </div>
                                    <small class="text-muted text-capitalize">{{ str_replace('-', ' ', $loan->status) }}</small>
                                </div>
                                <small class="text-muted">
                                    {{ optional($loan->created_at)->translatedFormat('d M Y H:i') }}
                                </small>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar {{ $loan->status === 'returned' ? 'bg-success' : 'bg-primary' }}"
                                        style="width: {{ $loan->status === 'returned' ? 100 : 70 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">Belum ada riwayat peminjaman.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
