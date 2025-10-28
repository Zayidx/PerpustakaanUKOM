<div>
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input
                    type="text"
                    class="form-control"
                    placeholder="Cari buku, penulis, atau kategori..."
                    wire:model.live.debounce.400ms="search"
                >
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end justify-content-start align-items-center gap-2">
                <span class="badge bg-primary fs-6">
                    {{ count($selectedBooksInfo) }} buku dipilih
                </span>
                <button
                    type="button"
                    class="btn btn-outline-primary position-relative"
                    data-bs-toggle="modal"
                    data-bs-target="#loanCartModal"
                >
                    <i class="bi bi-basket"></i>
                    @if(count($selectedBooksInfo) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ count($selectedBooksInfo) }}
                            <span class="visually-hidden">Buku dalam keranjang</span>
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    @error('selection')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @enderror

    <div class="row">
        <div class="col-12">
            <div class="row g-3">
                @forelse ($books as $book)
                    <div class="col-md-6">
                        <div class="card h-100 border-{{ in_array($book->id, $selectedBooks ?? [], true) ? 'primary' : 'light' }} overflow-hidden">
                            <div class="position-relative">
                                <div
                                    class="ratio ratio-3x4 bg-light"
                                    style="
                                        @if ($book->cover_depan_url)
                                            background-image: url('{{ $book->cover_depan_url }}');
                                            background-size: cover;
                                            background-position: center;
                                        @endif
                                    "
                                >
                                    @unless ($book->cover_depan_url)
                                        <div class="d-flex flex-column justify-content-center align-items-center h-100 text-muted small px-3 text-center">
                                            <i class="bi bi-book fs-3 mb-2"></i>
                                            <span>Cover belum tersedia</span>
                                        </div>
                                    @endunless
                                </div>
                                <span class="badge {{ $book->stok > 0 ? 'bg-success' : 'bg-danger' }} position-absolute top-0 start-0 m-2">
                                    {{ $book->stok > 0 ? 'Stok: '.$book->stok : 'Habis' }}
                                </span>
                                <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white px-3 py-2">
                                    <h6 class="mb-0 text-truncate">{{ $book->nama_buku }}</h6>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex gap-2 mt-auto">
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary flex-grow-1"
                                        wire:click="showDetail({{ $book->id }})"
                                    >
                                        Detail
                                    </button>
                                    <button
                                        type="button"
                                        class="btn {{ in_array($book->id, $selectedBooks ?? [], true) ? 'btn-danger' : 'btn-primary' }}"
                                        @if($book->stok < 1) disabled @endif
                                        wire:click="toggleSelection({{ $book->id }})"
                                    >
                                        @if ($book->stok < 1)
                                            Habis
                                        @else
                                            {{ in_array($book->id, $selectedBooks ?? [], true) ? 'Hapus' : 'Pilih' }}
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center text-muted">
                                Tidak ada buku ditemukan.
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $books->links() }}
            </div>
        </div>
    </div>

    <div
        wire:ignore.self
        class="modal fade"
        id="detailBookModal"
        tabindex="-1"
        aria-labelledby="detailBookModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailBookModalLabel">
                        {{ $detailBook?->nama_buku ?? 'Detail Buku' }}
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup"
                    ></button>
                </div>
                <div class="modal-body">
                    @if($detailBook)
                        @php
                            $detailCoverDepan = $detailBook->cover_depan_url;
                            $detailCoverBelakang = $detailBook->cover_belakang_url;
                        @endphp
                        <div class="row g-4">
                            <div class="col-md-5">
                                <div class="row g-2">
                                    <div class="col-6 col-md-12">
                                        <div
                                            class="ratio ratio-3x4 border rounded"
                                            style="
                                                @if ($detailCoverDepan)
                                                    background-image: url('{{ $detailCoverDepan }}');
                                                    background-size: cover;
                                                    background-position: center;
                                                @endif
                                            "
                                        >
                                            @unless ($detailCoverDepan)
                                                <span class="text-muted small d-flex align-items-center justify-content-center h-100 w-100">
                                                    Cover depan belum tersedia
                                                </span>
                                            @endunless
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-12">
                                        <div
                                            class="ratio ratio-3x4 border rounded"
                                            style="
                                                @if ($detailCoverBelakang)
                                                    background-image: url('{{ $detailCoverBelakang }}');
                                                    background-size: cover;
                                                    background-position: center;
                                                @endif
                                            "
                                        >
                                            @unless ($detailCoverBelakang)
                                                <span class="text-muted small d-flex align-items-center justify-content-center h-100 w-100">
                                                    Cover belakang belum tersedia
                                                </span>
                                            @endunless
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <span class="badge bg-secondary">
                                        {{ $detailBook->kategori?->nama_kategori_buku ?? 'Kategori tidak diketahui' }}
                                    </span>
                                </div>
                                <dl class="row mb-3">
                                    <dt class="col-sm-4">Penulis</dt>
                                    <dd class="col-sm-8 text-muted">
                                        {{ $detailBook->author?->nama_author ?? 'Tidak diketahui' }}
                                    </dd>
                                    <dt class="col-sm-4">Penerbit</dt>
                                    <dd class="col-sm-8 text-muted">
                                        {{ $detailBook->penerbit?->nama_penerbit ?? 'Tidak diketahui' }}
                                    </dd>
                                    <dt class="col-sm-4">Tanggal Terbit</dt>
                                    <dd class="col-sm-8 text-muted">
                                        {{ optional($detailBook->tanggal_terbit)->translatedFormat('d F Y') ?? 'Tidak diketahui' }}
                                    </dd>
                                    <dt class="col-sm-4">Stok</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge {{ $detailBook->stok > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $detailBook->stok > 0 ? 'Tersisa: '.$detailBook->stok : 'Stok habis' }}
                                        </span>
                                    </dd>
                                </dl>
                                <div>
                                    <h6>Deskripsi</h6>
                                    <p class="mb-0 text-muted">
                                        {{ $detailBook->deskripsi ?: 'Belum ada deskripsi.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Detail buku tidak tersedia.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        wire:ignore.self
        class="modal fade"
        id="loanCartModal"
        tabindex="-1"
        aria-labelledby="loanCartModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loanCartModalLabel">Keranjang Peminjaman</h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup"
                    ></button>
                </div>
                <div class="modal-body">
                    @if($selectedBooksInfo->isEmpty())
                        <p class="text-muted mb-0">Belum ada buku yang dipilih.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($selectedBooksInfo as $item)
                                <li class="list-group-item d-flex justify-content-between flex-column flex-md-row gap-2">
                                    <div class="d-flex align-items-start gap-3">
                                        <div
                                            class="ratio ratio-1x1 rounded overflow-hidden"
                                            style="
                                                width: 60px;
                                                @if ($item->cover_depan_url)
                                                    background-image: url('{{ $item->cover_depan_url }}');
                                                    background-size: cover;
                                                    background-position: center;
                                                @endif
                                            "
                                        >
                                            @unless ($item->cover_depan_url)
                                                <div class="d-flex align-items-center justify-content-center bg-light text-muted small h-100 w-100">
                                                    <i class="bi bi-book"></i>
                                                </div>
                                            @endunless
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-truncate">{{ $item->nama_buku }}</div>
                                            <small class="text-muted d-block">
                                                {{ $item->author?->nama_author ?? 'Tidak diketahui' }}
                                            </small>
                                            <div>
                                                @if($item->stok > 0)
                                                    <span class="badge bg-success mt-1">Stok: {{ $item->stok }}</span>
                                                @else
                                                    <span class="badge bg-danger mt-1">Stok habis</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger align-self-center"
                                        wire:click="removeFromSelection({{ $item->id }})"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        wire:click="clearSelection"
                        @if($selectedBooksInfo->isEmpty()) disabled @endif
                    >
                        Bersihkan
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        wire:click="generateLoanCode"
                        @if($selectedBooksInfo->isEmpty()) disabled @endif
                    >
                        Buat Kode
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                if (typeof bootstrap === 'undefined') {
                    return;
                }

                const ensureModal = (id) => {
                    const element = document.getElementById(id);
                    if (!element) {
                        return null;
                    }
                    let instance = bootstrap.Modal.getInstance(element);
                    if (!instance) {
                        instance = new bootstrap.Modal(element);
                    }
                    return { element, instance };
                };

                const detailModal = ensureModal('detailBookModal');
                const cartModal = ensureModal('loanCartModal');

                if (detailModal) {
                    Livewire.on('show-detail-modal', () => {
                        detailModal.instance.show();
                    });

                    Livewire.on('hide-detail-modal', () => {
                        detailModal.instance.hide();
                    });

                    detailModal.element.addEventListener('hidden.bs.modal', () => {
                        Livewire.dispatch('detail-modal-hidden');
                    });
                }

                if (cartModal) {
                    Livewire.on('hide-loan-modal', () => {
                        cartModal.instance.hide();
                    });
                }
            });
        </script>
    @endpush
</div>
