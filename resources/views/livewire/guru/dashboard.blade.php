<div>
    <p class="text-muted mb-3">Kelola permintaan peminjaman dan pantau pengembalian siswa.</p>

    <div class="page-content">
        <section class="row gy-4">
            <div class="col-12 col-xl-8">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon purple">
                                        <i class="bi bi-inboxes-fill"></i>
                                    </div>
                                    <div>
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
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon blue">
                                        <i class="bi bi-qr-code-scan"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted">Aktif</h6>
                                        <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon green">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted">Jatuh Tempo (&lt;=3h)</h6>
                                        <h4 class="mb-0">{{ number_format($stats['due_soon']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stats-icon red">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted">Siswa Dibantu</h6>
                                        <h4 class="mb-0">{{ number_format($stats['students_served']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="mb-0">Permintaan Menunggu Persetujuan</h4>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Peminjam</th>
                                        <th>Permintaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pendingList as $loan)
                                        <tr>
                                            <td class="fw-semibold">{{ $loan->kode }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $loan->siswa?->user?->nama_user ?? 'Siswa' }}</div>
                                                <small class="text-muted">ID: {{ $loan->siswa_id }}</small>
                                            </td>
                                            <td>{{ optional($loan->created_at)->translatedFormat('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Tidak ada permintaan menunggu.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="mb-0">Pengembalian Dalam 7 Hari</h4>
                    </div>
                    <div class="card-body">
                        @forelse ($dueSoonList as $loan)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <p class="mb-0 fw-semibold">{{ $loan->siswa?->user?->nama_user ?? 'Siswa' }}</p>
                                    <small class="text-muted">Kode {{ $loan->kode }}</small>
                                </div>
                                <span class="badge bg-warning text-dark">
                                    {{ optional($loan->due_at)->translatedFormat('d M') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">Tidak ada jadwal pengembalian terdekat.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="mb-0">Aktivitas Terbaru</h4>
                    </div>
                    <div class="card-body">
                        @forelse ($recentActivities as $loan)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="fw-semibold mb-0">{{ $loan->siswa?->user?->nama_user ?? 'Siswa' }}</p>
                                        <small class="text-muted text-capitalize">{{ str_replace('-', ' ', $loan->status) }}</small>
                                    </div>
                                    <small class="text-muted">
                                        {{ optional($loan->updated_at ?? $loan->created_at)->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar {{ $loan->status === 'returned' ? 'bg-success' : 'bg-primary' }}"
                                        role="progressbar" style="width: {{ $loan->status === 'returned' ? 100 : 60 }}%">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">Belum ada aktivitas yang tercatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
