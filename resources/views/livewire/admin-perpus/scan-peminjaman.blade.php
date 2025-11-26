<div>
    @push('styles')
        <style>
            #qr-reader {
                min-height: 360px;
            }
        </style>
    @endpush

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Pemindai QR Code
                </div>
                <div class="card-body">
                    <div id="qr-reader" wire:ignore class="ratio ratio-1x1 border rounded d-flex align-items-center justify-content-center">
                        <span class="text-muted">Mengaktifkan kamera...</span>
                    </div>
                    <p class="text-muted small mt-3 mb-0">
                        Arahkan kamera ke QR code milik siswa untuk memproses peminjaman.
                    </p>
                </div>
            </div>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    Input Manual Kode 6 Angka
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
                                placeholder="Misal: 123456"
                                wire:model.defer="manualCode"
                                autocomplete="off"
                            >
                            <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
                                Proses
                            </button>
                        </div>
                        @error('manualCode')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                        <small class="text-muted">
                            Masukkan kode 6 angka yang tampil di halaman siswa jika scanner tidak tersedia.
                        </small>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            @if ($loan)
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Detail Peminjaman</span>
                        <span class="badge bg-{{ $loan['status'] === 'accepted' ? 'success' : ($loan['status'] === 'pending' ? 'warning text-dark' : 'secondary') }}">
                            {{ ucfirst($loan['status']) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Kode</dt>
                            <dd class="col-sm-8">{{ $loan['kode'] }}</dd>

                            <dt class="col-sm-4">Siswa</dt>
                            <dd class="col-sm-8">{{ $loan['student_name'] ?? '-' }}</dd>

                            <dt class="col-sm-4">Kelas</dt>
                            <dd class="col-sm-8">{{ $loan['student_class'] ?? '-' }}</dd>

                            <dt class="col-sm-4">Diterima</dt>
                            <dd class="col-sm-8">
                                {{ $loan['accepted_at'] ? optional($loan['accepted_at'])->translatedFormat('d F Y H:i') : '-' }}
                            </dd>

                            <dt class="col-sm-4">Batas</dt>
                            <dd class="col-sm-8">
                                {{ $loan['due_at'] ? optional($loan['due_at'])->translatedFormat('d F Y') : '-' }}
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
                    Belum ada data peminjaman yang dipindai.
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
        <script>
            // Helper untuk kirim event ke Livewire (dipakai oleh scanner)
            const dispatchLivewireEvent = (eventName, ...payload) => {
                window.dispatchEvent(new CustomEvent(eventName, { detail: payload }));
            };

            // Inisialisasi scanner sekali, mencoba kamera belakang lebih dulu
            const loanScanner = {
                instance: null,
                containerId: 'qr-reader',
                initOnce() {
                    const attemptStart = async () => {
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

                        dispatchLivewireEvent('qr-scanner-error');

                        if (this.instance) {
                            try {
                                await this.instance.stop();
                            } catch (_) {
                                // ignore
                            } finally {
                                this.instance.clear();
                            }
                        }

                        const supportedFormats = window.Html5QrcodeSupportedFormats?.QR_CODE
                            ? [Html5QrcodeSupportedFormats.QR_CODE]
                            : undefined;

                        const buildConfig = (constraints, isUserFacing = false) => ({
                            fps: 10,
                            qrbox: { width: 320, height: 320 },
                            disableFlip: isUserFacing ? false : true,
                            videoConstraints: {
                                width: { ideal: 1280 },
                                height: { ideal: 720 },
                                ...(constraints ?? { facingMode: { ideal: 'environment' } }),
                            },
                        });

                        this.instance = new Html5Qrcode(this.containerId, {
                            verbose: false,
                            formatsToSupport: supportedFormats,
                            experimentalFeatures: {
                                useBarCodeDetectorIfSupported: true,
                            },
                        });

                        const startWithConstraints = async (constraints) => {
                            const isUserFacing =
                                typeof constraints?.facingMode === 'string'
                                    ? /user/i.test(constraints.facingMode)
                                    : (typeof constraints?.facingMode === 'object' && /user/i.test(constraints.facingMode?.exact ?? ''));

                            const config = buildConfig(constraints, isUserFacing);
                            await this.instance.start(
                                constraints,
                                config,
                                (decodedText) => dispatchLivewireEvent('qr-scanned', decodedText),
                                () => {}
                            );

                            container.dataset.initialized = 'true';
                        };

                        const tryStartRearFirst = async () => {
                            const attempts = [];
                            let cameras = [];

                            try {
                                cameras = await Html5Qrcode.getCameras();
                            } catch (_) {
                                cameras = [];
                            }

                            const preferred = cameras?.find((cam) => /back|rear|environment/i.test(cam.label ?? ''));

                            if (preferred?.id) {
                                attempts.push({ deviceId: { exact: preferred.id } });
                            }

                            attempts.push({ facingMode: { exact: 'environment' } });
                            attempts.push({ facingMode: { ideal: 'environment' } });

                            for (const cam of cameras ?? []) {
                                if (!cam?.id || cam.id === preferred?.id) {
                                    continue;
                                }

                                attempts.push({ deviceId: { exact: cam.id } });
                            }

                            attempts.push({ facingMode: 'environment' });
                            attempts.push({ facingMode: 'user' });

                            let lastError;

                            for (const constraints of attempts) {
                                try {
                                    await startWithConstraints(constraints);
                                    return;
                                } catch (error) {
                                    lastError = error;
                                }
                            }

                            const message = (lastError?.message) || 'Kamera tidak bisa diakses.';
                            container.innerHTML = `<span class="text-danger small">Kamera tidak bisa diakses: ${message}</span>`;
                            dispatchLivewireEvent('qr-scanner-error', message);

                            container.dataset.initialized = 'failed';
                            container.addEventListener('click', attemptStart, { once: true });
                        };

                        await tryStartRearFirst();
                    };

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', attemptStart, { once: true });
                    } else {
                        attemptStart();
                    }
                },
            };

            // Mulai fitur scan QR setelah Livewire siap
            const initLoanScanFeatures = () => {
                loanScanner.initOnce();
            };

            
            if (window.Livewire) {
                initLoanScanFeatures();
            }

            document.addEventListener('livewire:load', initLoanScanFeatures);
        </script>
    @endpush
</div>
