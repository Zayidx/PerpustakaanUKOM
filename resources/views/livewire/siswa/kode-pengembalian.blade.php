@php
    $loanStatus = data_get($loan, 'status');
@endphp
<div
    id="return-code-wrapper"
    data-loan-code="{{ $loan['kode'] ?? 'unknown' }}"
    data-loan-status="{{ $loan['status'] ?? '' }}"
    @if ($loanStatus === 'accepted') wire:poll.2s.keep-alive="refreshLoan" @endif
>
    @if ($loan)
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        Kode Pengembalian
                    </div>
                    <div class="card-body text-center mt-5">
                        @php
                            $lateDays = $loan['late_days'] ?? 0;
                            $lateFee = $loan['late_fee'] ?? 0;
                        @endphp
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
                            <span class="badge {{ $loanStatus === 'returned' ? 'bg-secondary' : 'bg-success' }}">
                                Status: {{ $statusLabel }}
                            </span>
                        </div>

                        @if ($loanStatus === 'accepted')
                            <p class="text-muted small mb-1">
                                Kode pengembalian Anda:
                            </p>
                            <div class="fs-3 fw-bold mb-3">
                                {{ $loan['kode'] }}
                            </div>
                        @endif

                        @if ($loanStatus === 'returned')
                            <div class="alert alert-success">
                                Pengembalian Anda telah selesai diproses. QR tidak lagi diperlukan.
                            </div>
                        @elseif ($lateDays > 0)
                            <div class="alert alert-warning text-start">
                                Terlambat {{ $lateDays }} hari. Perkiraan denda: <strong>Rp{{ number_format($lateFee, 0, ',', '.') }}</strong>.
                                Mohon siapkan pembayaran saat pengembalian.
                            </div>
                        @else
                            <p class="text-muted small">Belum melewati jatuh tempo.</p>
                        @endif

                        <p class="text-muted small mb-2">
                            Batas pengembalian: {{ $loan['due_at'] ? optional($loan['due_at'])->translatedFormat('d F Y') : '-' }}
                        </p>

                        @if ($qrSvg && $loanStatus === 'accepted')
                            <div class="d-flex justify-content-center my-3">
                                <div class="rounded p-3">
                                    {!! $qrSvg !!}
                                </div>
                            </div>
                            <p class="text-muted mt-3 mb-0">
                                Tunjukkan QR ini atau bacakan kode 6 angka tersebut kepada Admin Perpus untuk menyelesaikan pengembalian.
                            </p>
                        @elseif ($loanStatus === 'accepted')
                            <p class="text-danger mb-0">Gagal membuat QR code.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Detail Pengembalian
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Kode</dt>
                            <dd class="col-sm-8">{{ $loan['kode'] }}</dd>

                            <dt class="col-sm-4">Dibuat</dt>
                            <dd class="col-sm-8">
                                {{ optional($loan['created_at'])->translatedFormat('d F Y H:i') }}
                            </dd>

                            <dt class="col-sm-4">Disetujui</dt>
                            <dd class="col-sm-8">
                                {{ $loan['accepted_at'] ? optional($loan['accepted_at'])->translatedFormat('d F Y H:i') : '-' }}
                            </dd>

                            <dt class="col-sm-4">Batas Pengembalian</dt>
                            <dd class="col-sm-8">
                                {{ $loan['due_at'] ? optional($loan['due_at'])->translatedFormat('d F Y') : '-' }}
                            </dd>

                            <dt class="col-sm-4">Admin Perpus</dt>
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
            Data pengembalian tidak tersedia.
        </div>
    @endif
</div>

@if ($loan)
    @push('scripts')
        <script>
            document.addEventListener('livewire:load', () => {
                const showAlert = ({ message, type = 'success' }) => {
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

                const wrapper = document.getElementById('return-code-wrapper');
                if (!wrapper) {
                    return;
                }

                const loanKey = `return-code-alert-${wrapper.dataset.loanCode || 'unknown'}`;
                const maybeShowInitialAlert = () => {
                    window.__shownReturnAlerts = window.__shownReturnAlerts || {};

                    if (window.__shownReturnAlerts[loanKey]) {
                        return;
                    }

                    const status = wrapper.dataset.loanStatus || null;
                    if (status !== 'returned') {
                        return;
                    }

                    window.__shownReturnAlerts[loanKey] = true;
                    showAlert({ type: 'success', message: 'Pengembalian buku selesai diproses.' });
                };

                if (window.Livewire) {
                    window.Livewire.on('loan-status-updated', (payload = {}) => {
                        window.__shownReturnAlerts = window.__shownReturnAlerts || {};
                        window.__shownReturnAlerts[loanKey] = true;
                        showAlert(payload);
                    });
                }

                maybeShowInitialAlert();
            });
        </script>
    @endpush
@endif
