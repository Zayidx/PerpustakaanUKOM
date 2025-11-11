<div>
    @if ($loan)
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        Kode Peminjaman
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="badge bg-{{ $loan['status'] === 'pending' ? 'warning text-dark' : ($loan['status'] === 'accepted' ? 'success' : 'secondary') }}">
                                Status: {{ ucfirst($loan['status']) }}
                            </span>
                        </div>
                        <p class="text-muted small mb-1">
                            Kode peminjaman Anda:
                        </p>
                        <div class="fs-3 fw-bold mb-3">
                            {{ $loan['kode'] }}
                        </div>
                        @if ($qrSvg)
                            <div class="d-flex justify-content-center">
                                {!! $qrSvg !!}
                            </div>
                            <p class="text-muted mt-3 mb-0">
                                Tunjukkan QR code atau bacakan kode 6 angka ini kepada guru untuk diproses.
                            </p>
                        @else
                            <p class="text-danger mb-0">Gagal membuat QR code.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Detail Peminjaman
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Kode</dt>
                            <dd class="col-sm-8">{{ $loan['kode'] }}</dd>

                            <dt class="col-sm-4">Dibuat</dt>
                            <dd class="col-sm-8">
                                {{ optional($loan['created_at'])->translatedFormat('d F Y H:i') }}
                            </dd>

                            <dt class="col-sm-4">Diterima</dt>
                            <dd class="col-sm-8">
                                {{ $loan['accepted_at'] ? optional($loan['accepted_at'])->translatedFormat('d F Y H:i') : '-' }}
                            </dd>

                            <dt class="col-sm-4">Batas Pengembalian</dt>
                            <dd class="col-sm-8">
                                {{ $loan['due_at'] ? optional($loan['due_at'])->translatedFormat('d F Y') : '-' }}
                            </dd>

                            <dt class="col-sm-4">Guru Penerima</dt>
                            <dd class="col-sm-8">{{ $loan['guru'] ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        Buku yang Dipinjam
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach ($loan['items'] as $item)
                                <li class="list-group-item">
                                    <div class="fw-semibold">{{ $item['judul'] }}</div>
                                    <small class="text-muted">
                                        {{ $item['author'] ?? 'Tidak diketahui' }} • {{ $item['kategori'] ?? '-' }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-danger">
            Data peminjaman tidak tersedia.
        </div>
    @endif
</div>
