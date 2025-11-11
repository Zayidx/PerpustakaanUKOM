<div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                <h5 class="mb-3 mb-md-0">Riwayat Peminjaman</h5>
                <div class="d-flex align-items-center gap-2">
                    <label for="statusFilter" class="text-muted small mb-0">Status</label>
                    <select
                        id="statusFilter"
                        class="form-select form-select-sm w-auto"
                        wire:model.live="statusFilter"
                    >
                        <option value="all">Semua</option>
                        <option value="pending">Menunggu</option>
                        <option value="accepted">Dipinjam</option>
                        <option value="returned">Dikembalikan</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Jatuh Tempo</th>
                            <th>Buku</th>
                            <th>Pengembalian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            <tr>
                                <td class="fw-semibold">{{ $loan->kode }}</td>
                                <td>
                                    <span class="badge bg-{{ $loan->status === 'pending' ? 'warning text-dark' : ($loan->status === 'accepted' ? 'success' : ($loan->status === 'returned' ? 'secondary' : 'danger')) }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td>{{ optional($loan->created_at)->translatedFormat('d F Y H:i') }}</td>
                                <td>
                                    @if ($loan->due_at)
                                        <span class="{{ now()->greaterThan($loan->due_at) && $loan->status !== 'returned' ? 'text-danger fw-semibold' : '' }}">
                                            {{ optional($loan->due_at)->translatedFormat('d F Y') }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <ul class="list-unstyled mb-0 small">
                                        @foreach ($loan->items as $item)
                                            <li>• {{ $item->buku->nama_buku }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="text-center">
                                    @if ($loan->status === 'accepted')
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            wire:click="showReturnTicket({{ $loan->id }})"
                                        >
                                            Tampilkan
                                        </button>
                                    @elseif ($loan->status === 'returned')
                                        <span class="badge bg-secondary">Selesai</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Belum ada peminjaman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @error('returnTicket')
                <div class="alert alert-danger mt-3">
                    {{ $message }}
                </div>
            @enderror

            @if ($returnTicket)
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            Kode Pengembalian
                            <span class="badge bg-primary ms-2">{{ $returnTicket['kode'] }}</span>
                        </div>
                        <button class="btn-close" wire:click="clearReturnTicket" aria-label="Tutup"></button>
                    </div>
                    <div class="card-body">
                        @php
                            $lateDays = $returnTicket['late_days'] ?? 0;
                            $lateFee = $returnTicket['late_fee'] ?? 0;
                        @endphp
                        @if ($lateDays > 0)
                            <div class="alert alert-warning">
                                Terlambat {{ $lateDays }} hari. Denda sementara: Rp{{ number_format($lateFee, 0, ',', '.') }}.
                                Mohon siapkan pembayaran saat pengembalian.
                            </div>
                        @endif
                        <div class="row g-4 align-items-center">
                            <div class="col-md-5 text-center">
                                @if ($returnQrSvg)
                                    <div class="d-inline-block border rounded p-3 bg-light">
                                        {!! $returnQrSvg !!}
                                    </div>
                                    <p class="text-muted small mt-2 mb-0">
                                            Tunjukkan QR ini atau bacakan kode 6 angka saat mengembalikan buku.
                                        </p>
                                        <div class="mt-2 text-center">
                                            <span class="badge bg-secondary">Kode: {{ $returnTicket['kode'] }}</span>
                                        </div>
                                @else
                                    <p class="text-danger mb-0">Gagal membuat QR code.</p>
                                @endif
                            </div>
                            <div class="col-md-7">
                                <dl class="row mb-3 small">
                                    <dt class="col-4">Kode</dt>
                                    <dd class="col-8">
                                        <span class="fw-semibold">{{ $returnTicket['kode'] }}</span>
                                    </dd>
                                    <dt class="col-4">Jatuh Tempo</dt>
                                    <dd class="col-8">
                                        {{ $returnTicket['due_at'] ? optional($returnTicket['due_at'])->translatedFormat('d F Y') : '-' }}
                                    </dd>
                                    <dt class="col-4">Status</dt>
                                    <dd class="col-8">{{ ucfirst($returnTicket['status'] ?? '') }}</dd>
                                </dl>
                                <h6>Buku Dipinjam</h6>
                                <ul class="list-group list-group-flush small">
                                    @foreach ($returnTicket['items'] as $item)
                                        <li class="list-group-item px-0">
                                            <div class="fw-semibold">{{ $item['judul'] }}</div>
                                            <div class="text-muted">
                                                {{ $item['author'] ?? 'Tidak diketahui' }} • {{ $item['kategori'] ?? '-' }}
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-3">
                {{ $loans->links() }}
            </div>
        </div>
    </div>
</div>
