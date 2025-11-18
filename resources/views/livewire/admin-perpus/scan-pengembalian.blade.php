<div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    Pemindai Pengembalian
                </div>
                <div class="card-body">
                    <div id="qr-return-reader" wire:ignore class="ratio ratio-1x1 border rounded d-flex align-items-center justify-content-center">
                        <span class="text-muted">Mengaktifkan kamera...</span>
                    </div>
                    <p class="text-muted small mt-3 mb-0">
                        Arahkan kamera ke QR pengembalian siswa atau gunakan kode 6 angka sebagai cadangan.
                    </p>
                </div>
            </div>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    Input Manual Kode Pengembalian
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="processManualCode" class="d-flex flex-column gap-2">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-key"></i>
                            </span>
                            <input
                                type="text"
                                class="form-control @error('manualCode') is-invalid @enderror"
                                placeholder="Misal: 654321"
                                wire:model.defer="manualCode"
                                autocomplete="off"
                            >
                            <button class="btn btn-success" type="submit" wire:loading.attr="disabled">
                                Proses
                            </button>
                        </div>
                        @error('manualCode')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                        <small class="text-muted">
                            Masukkan kode 6 angka yang ditampilkan pada halaman peminjaman siswa.
                        </small>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            @if ($loan)
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Detail Pengembalian</span>
                        <span class="badge bg-secondary">{{ ucfirst($loan['status']) }}</span>
                    </div>
                    <div class="card-body">
                        @if (($loan['late_days'] ?? 0) > 0)
                            <div class="alert alert-warning">
                                Peminjaman terlambat {{ $loan['late_days'] }} hari. Tagihan denda: Rp{{ number_format($loan['late_fee'], 0, ',', '.') }}.
                                Silakan informasikan ke siswa sebelum menyelesaikan pengembalian.
                            </div>
                        @endif
                        @if (($pendingReturn['loan_id'] ?? null) === ($loan['id'] ?? null))
                            <div class="alert alert-info">
                                Konfirmasi pembayaran denda sebesar
                                <strong>Rp{{ number_format($pendingReturn['late_fee'] ?? 0, 0, ',', '.') }}</strong>.
                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" class="btn btn-success btn-sm" wire:click="confirmLateFee">
                                        Sudah dibayar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="cancelLateFee">
                                        Belum dibayar
                                    </button>
                                </div>
                            </div>
                        @endif
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Kode</dt>
                            <dd class="col-sm-8">{{ $loan['kode'] }}</dd>

                            <dt class="col-sm-4">Siswa</dt>
                            <dd class="col-sm-8">{{ $loan['student_name'] ?? '-' }}</dd>

                            <dt class="col-sm-4">Kelas</dt>
                            <dd class="col-sm-8">{{ $loan['student_class'] ?? '-' }}</dd>

                            <dt class="col-sm-4">Dikembalikan</dt>
                            <dd class="col-sm-8">
                                {{ $loan['returned_at'] ? optional($loan['returned_at'])->translatedFormat('d F Y H:i') : '-' }}
                            </dd>
                        </dl>
                        <hr>
                        <h6>Buku</h6>
                        <ul class="list-unstyled mb-0 small">
                            @foreach ($loan['books'] as $book)
                                <li>• {{ $book['title'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    Belum ada data pengembalian yang dipindai.
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
        <script>
            const dispatchLivewireEvent = (eventName, ...payload) => {
                window.dispatchEvent(new CustomEvent(eventName, { detail: payload }));
            };

            const returnScanner = {
                instance: null,
                containerId: 'qr-return-reader',
                initOnce() {
                    const attemptStart = () => {
                        const container = document.getElementById(this.containerId);

                        if (!container) {
                            return;
                        }

                        if (!window.Html5Qrcode) {
                            setTimeout(attemptStart, 150);
                            return;
                        }

                        if (container.dataset.initialized === 'true') {
                            return;
                        }

                        container.dataset.initialized = 'true';
                        dispatchLivewireEvent('qr-scanner-error');

                        if (this.instance) {
                            this.instance.stop().catch(() => {
                                
                            }).finally(() => {
                                this.instance.clear();
                            });
                        }

                        this.instance = new Html5Qrcode(this.containerId, { verbose: false });

                        const config = {
                            fps: 10,
                            qrbox: { width: 250, height: 250 },
                        };

                        this.instance.start(
                            { facingMode: 'environment' },
                            config,
                            (decodedText) => dispatchLivewireEvent('qr-scanned', decodedText),
                            () => {}
                        ).catch((error) => {
                            const message = error?.message ?? String(error);
                            container.innerHTML = `<span class="text-danger small">Kamera tidak bisa diakses: ${message}</span>`;
                            dispatchLivewireEvent('qr-scanner-error', message);
                        });
                    };

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', attemptStart, { once: true });
                    } else {
                        attemptStart();
                    }
                },
            };

            document.addEventListener('livewire:navigated', () => {
                const container = document.getElementById(returnScanner.containerId);
                if (container) {
                    delete container.dataset.initialized;
                }
                returnScanner.initOnce();
                setupLateModalListeners();
            });

            const setupLateModalListeners = () => {
                if (window.__lateFeeModalInitialized) {
                    return;
                }

                if (!window.bootstrap) {
                    setTimeout(setupLateModalListeners, 150);
                    return;
                }

                const resolveInstance = () => {
                    const modalElement = document.getElementById('lateFeeModal');

                    if (!modalElement) {
                        return null;
                    }

                    return bootstrap.Modal.getOrCreateInstance(modalElement);
                };

                window.addEventListener('show-late-modal', () => {
                    const instance = resolveInstance();
                    if (instance) {
                        instance.show();
                    }
                });

                window.addEventListener('hide-late-modal', () => {
                    const instance = resolveInstance();
                    if (instance) {
                        instance.hide();
                    }
                });

                window.__lateFeeModalInitialized = true;
            };

            const initReturnScanFeatures = () => {
                returnScanner.initOnce();
                setupLateModalListeners();
            };

            document.addEventListener('livewire:load', initReturnScanFeatures);

            if (window.Livewire) {
                initReturnScanFeatures();
            }
        </script>
    @endpush

    <div
        wire:ignore.self
        class="modal fade"
        id="lateFeeModal"
        tabindex="-1"
        aria-labelledby="lateFeeModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lateFeeModalLabel">Konfirmasi Denda Keterlambatan</h5>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="cancelLateFee"></button>
                </div>
                <div class="modal-body">
                    @php
                        $lateDays = $pendingReturn['late_days'] ?? ($loan['late_days'] ?? 0);
                        $lateFee = $pendingReturn['late_fee'] ?? ($loan['late_fee'] ?? 0);
                    @endphp
                    @if ($lateDays > 0)
                        <p>Siswa terlambat <strong>{{ $lateDays }}</strong> hari.</p>
                        <p>Total denda yang harus dibayar: <strong>Rp{{ number_format($lateFee, 0, ',', '.') }}</strong>.</p>
                        <p class="mb-0">Apakah denda sudah dibayar?</p>
                    @else
                        <p class="mb-0">Tidak ada denda keterlambatan.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="cancelLateFee">
                        Belum Dibayar
                    </button>
                    <button type="button" class="btn btn-success" wire:click="confirmLateFee">
                        Sudah Dibayar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
