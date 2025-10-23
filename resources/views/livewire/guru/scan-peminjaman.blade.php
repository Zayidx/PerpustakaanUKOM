<div>
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
        </div>

        <div class="col-lg-6">
            @if ($errorMessage)
                <div class="alert alert-danger">
                    {{ $errorMessage }}
                </div>
            @endif

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
        <script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>
        <script>
            const loanScanner = {
                instance: null,
                containerId: 'qr-reader',
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
                        window.Livewire?.dispatch('qr-scanner-error', null);

                        if (this.instance) {
                            this.instance.stop().catch(() => {
                                /* ignore */
                            }).finally(() => {
                                this.instance.clear();
                            });
                        }

                        this.instance = new Html5Qrcode(this.containerId, {
                            verbose: false,
                        });

                        const config = {
                            fps: 10,
                            qrbox: { width: 250, height: 250 },
                        };

                        this.instance.start(
                            { facingMode: 'environment' },
                            config,
                            (decodedText) => window.Livewire?.dispatch('qr-scanned', decodedText),
                            () => {}
                        ).catch((error) => {
                            const message = error?.message ?? String(error);
                            container.innerHTML = `<span class="text-danger small">Kamera tidak bisa diakses: ${message}</span>`;
                            window.Livewire?.dispatch('qr-scanner-error', message);
                        });
                    };

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', attemptStart, { once: true });
                    } else {
                        attemptStart();
                    }
                },
            };

            document.addEventListener('livewire:load', () => loanScanner.initOnce());
            document.addEventListener('livewire:navigated', () => {
                const container = document.getElementById(loanScanner.containerId);
                if (container) {
                    delete container.dataset.initialized;
                }
                loanScanner.initOnce();
            });

            // Fallback for initial render if Livewire already loaded
            if (window.Livewire) {
                loanScanner.initOnce();
            }
        </script>
    @endpush
</div>
