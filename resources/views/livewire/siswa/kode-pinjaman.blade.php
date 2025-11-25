@php
    $loanStatus = data_get($loan, 'status');
@endphp
<div wire:poll.2s.keep-alive="refreshLoan">
    @if ($loan)
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        Kode Peminjaman
                    </div>
                    <div class="card-body text-center mt-5">
                        @php
                            $statusLabels = [
                                'pending' => 'Menunggu',
                                'accepted' => 'Sedang Dipinjam',
                                'returned' => 'Dikembalikan',
                                'cancelled' => 'Dibatalkan',
                            ];
                            $statusLabel = $statusLabels[$loan['status']] ?? ucfirst($loan['status']);
                        @endphp
                        <div class="mb-3">
                            <span class="badge bg-{{ $loan['status'] === 'pending' ? 'warning text-dark' : ($loan['status'] === 'accepted' ? 'success' : 'secondary') }}">
                                Status: {{ $statusLabel }}
                            </span>
                        </div>
                        @if ($loanStatus === 'pending')
                            <p class="text-muted small mb-1">
                                Kode peminjaman Anda:
                            </p>
                            <div class="fs-3 fw-bold mb-3">
                                {{ $loan['kode'] }}
                            </div>
                        @endif
                        @if ($loanStatus === 'accepted')
                            <div class="alert alert-success">
                                Peminjaman Anda telah disetujui. QR tidak lagi diperlukan.
                            </div>
                        @elseif ($loanStatus === 'returned')
                            <div class="alert alert-success">
                                Peminjaman sudah dikembalikan. QR ini tidak lagi diperlukan.
                            </div>
                        @elseif ($loanStatus === 'cancelled')
                            <div class="alert alert-warning">
                                Peminjaman dibatalkan. QR ini tidak lagi berlaku.
                            </div>
                        @elseif ($qrSvg)
                            <div class="d-flex justify-content-center">
                                {!! $qrSvg !!}
                            </div>
                            <p class="text-muted mt-3 mb-0">
                                Tunjukkan QR code atau bacakan kode 6 angka ini kepada Admin Perpus untuk diproses.
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

                            <dt class="col-sm-4">Admin Perpus Penerima</dt>
                            <dd class="col-sm-8">{{ $loan['admin_perpus'] ?? '-' }}</dd>
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

@if ($loan)
    @push('scripts')
        <script>
        document.addEventListener('livewire:load', () => {
            const showAlert = ({ message, type = 'info' }) => {
                if (!window.Swal) {
                    return;
                }

                window.Swal.fire({
                    icon: type,
                    title: type === 'error' ? 'Gagal' : 'Berhasil',
                    text: message,
                    timer: 2500,
                    showConfirmButton: false,
                    timerProgressBar: true,
                });
            };

            const loanCodeKey = `loan-code-alert-{{ $loan['kode'] ?? 'unknown' }}`;
            const statusMessages = {
                accepted: { type: 'success', message: 'Peminjaman berhasil dipindai dan disetujui.' },
                returned: { type: 'success', message: 'Peminjaman ini sudah selesai.' },
                cancelled: { type: 'error', message: 'Peminjaman dibatalkan oleh petugas.' },
            };

            const registerInitialAlert = () => {
                window.__shownLoanAlerts = window.__shownLoanAlerts || {};

                if (window.__shownLoanAlerts[loanCodeKey]) {
                    return;
                }

                const currentStatus = "{{ $loan['status'] ?? '' }}";
                if (!currentStatus || currentStatus === 'pending') {
                    return;
                }

                const payload = statusMessages[currentStatus] ?? {
                    type: 'info',
                    message: 'Status peminjaman diperbarui.',
                };

                window.__shownLoanAlerts[loanCodeKey] = true;
                showAlert(payload);
            };

            if (window.Livewire) {
                window.Livewire.on('loan-status-updated', (payload = {}) => {
                    window.__shownLoanAlerts = window.__shownLoanAlerts || {};
                    window.__shownLoanAlerts[loanCodeKey] = true;
                    showAlert(payload);
                });
            }

            registerInitialAlert();
        });
        </script>
    @endpush
@endif
