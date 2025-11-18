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
                            <th>Kode QR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            <tr>
                                <td class="fw-semibold">{{ $loan->kode }}</td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-warning text-dark',
                                            'accepted' => 'bg-success',
                                            'returned' => 'bg-secondary',
                                            'cancelled' => 'bg-danger',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Menunggu',
                                            'accepted' => 'Sedang Dipinjam',
                                            'returned' => 'Dikembalikan',
                                            'cancelled' => 'Dibatalkan',
                                        ];
                                        $status = $loan->status;
                                    @endphp
                                    <span class="badge {{ $statusClasses[$status] ?? 'bg-secondary' }}">
                                        {{ $statusLabels[$status] ?? ucfirst($status) }}
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
                                    @if ($loan->status === 'pending')
                                        <a
                                            href="{{ route('siswa.kode-peminjaman', ['kode' => $loan->kode]) }}"
                                            class="btn btn-outline-secondary btn-sm"
                                            wire:navigate
                                        >
                                            Lihat Kode Peminjaman
                                        </a>
                                    @elseif ($loan->status === 'accepted')
                                        <a
                                            href="{{ route('siswa.kode-pengembalian', ['kode' => $loan->kode]) }}"
                                            class="btn btn-outline-primary btn-sm"
                                            wire:navigate
                                        >
                                            Lihat Kode Pengembalian
                                        </a>
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

            <div class="mt-3">
                {{ $loans->links() }}
            </div>
        </div>
    </div>
</div>
