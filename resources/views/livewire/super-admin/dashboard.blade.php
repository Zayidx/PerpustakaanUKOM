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
    <p class="text-muted mb-3">Pantau aktivitas perpustakaan, koleksi, dan peminjaman secara real-time.</p>

    <div class="page-content">
        <section class="row gy-4">
            <div class="col-12 col-xl-8">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="row align-items-center">
                                    <div class="col-4 d-flex justify-content-start">
                                        <div class="stats-icon purple">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                    </div>
                                    <div class="col-8 text-end text-md-start">
                                        <h6 class="text-muted font-semibold">Total Siswa</h6>
                                        <h4 class="font-extrabold mb-0">{{ number_format($stats['students']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="row align-items-center">
                                    <div class="col-4 d-flex justify-content-start">
                                        <div class="stats-icon blue">
                                            <i class="bi bi-person-vcard-fill"></i>
                                        </div>
                                    </div>
                                    <div class="col-8 text-end text-md-start">
                                        <h6 class="text-muted font-semibold">Admin Perpus &amp; Super Admin</h6>
                                        <h4 class="font-extrabold mb-0">{{ number_format($stats['staff']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="row align-items-center">
                                    <div class="col-4 d-flex justify-content-start">
                                        <div class="stats-icon green">
                                            <i class="bi bi-book-half"></i>
                                        </div>
                                    </div>
                                    <div class="col-8 text-end text-md-start">
                                        <h6 class="text-muted font-semibold">Koleksi Buku</h6>
                                        <h4 class="font-extrabold mb-0">{{ number_format($stats['books']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-body px-4 py-4-5">
                                <div class="row align-items-center">
                                    <div class="col-4 d-flex justify-content-start">
                                        <div class="stats-icon red">
                                            <i class="bi bi-stars"></i>
                                        </div>
                                    </div>
                                    <div class="col-8 text-end text-md-start">
                                        <h6 class="text-muted font-semibold">Anggota Baru (7h)</h6>
                                        <h4 class="font-extrabold mb-0">{{ number_format($stats['new_members']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted">Peminjaman Aktif</small>
                                <h3 class="mb-0">{{ number_format($stats['active_loans']) }}</h3>
                                <p class="text-muted small mb-0">Sedang dipinjam oleh siswa.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted">Menunggu Persetujuan</small>
                                <h3 class="mb-0">{{ number_format($stats['pending_loans']) }}</h3>
                                <p class="text-muted small mb-0">Perlu ditinjau oleh Super Admin/Admin Perpus.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <small class="text-muted">Lewat Jatuh Tempo</small>
                                <h3 class="mb-0">{{ number_format($stats['overdue_loans']) }}</h3>
                                <p class="text-muted small mb-0">Segera hubungi peminjam terkait.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Peminjaman Terbaru</h4>
                        <span class="text-muted small">5 transaksi terakhir</span>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Peminjam</th>
                                        <th>Status</th>
                                        <th>Tgl Permintaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentLoans as $loan)
                                        <tr>
                                            <td class="fw-semibold">{{ $loan->kode }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $loan->siswa?->user?->nama_user ?? 'Siswa' }}</div>
                                                <div class="small text-muted">ID: {{ $loan->siswa_id }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-capitalize text-dark border">
                                                    {{ str_replace('-', ' ', $loan->status) }}
                                                </span>
                                            </td>
                                            <td>{{ optional($loan->created_at)->translatedFormat('d M Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada peminjaman.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Jatuh Tempo Terdekat</h4>
                    </div>
                    <div class="card-body">
                        @forelse ($upcomingDue as $loan)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <p class="mb-0 fw-semibold">{{ $loan->siswa?->user?->nama_user ?? 'Siswa' }}</p>
                                    <small class="text-muted">Kode {{ $loan->kode }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-warning text-dark">
                                        {{ optional($loan->due_at)->translatedFormat('d M') }}
                                    </span>
                                    <div class="small text-muted text-capitalize">{{ $loan->status }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">Tidak ada daftar jatuh tempo minggu ini.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="mb-0">Kategori Terpopuler</h4>
                    </div>
                    <div class="card-body">
                        @php
                            $totalBooks = max($stats['books'], 1);
                        @endphp
                        @forelse ($topCategories as $category)
                            @php
                                $percentage = round(($category->buku_count / $totalBooks) * 100);
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="mb-0 fw-semibold">{{ $category->nama_kategori_buku }}</p>
                                        <small class="text-muted">{{ $category->buku_count }} buku tersedia</small>
                                    </div>
                                    <span class="badge bg-light text-primary border">{{ $percentage }}%</span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">Belum ada data kategori buku.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
