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
            <span class="badge bg-primary fs-6">
                {{ count($selectedBooksInfo) }} buku dipilih
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="row g-3">
                @forelse ($books as $book)
                    <div class="col-md-6">
                        <div class="card h-100 border-{{ in_array($book->id, $selectedBooks ?? [], true) ? 'primary' : 'light' }}">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h5 class="card-title mb-0">{{ $book->nama_buku }}</h5>
                                    <span class="badge {{ $book->stok > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $book->stok > 0 ? 'Stok: '.$book->stok : 'Habis' }}
                                    </span>
                                </div>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-person me-1"></i>
                                    {{ $book->author?->nama_author ?? 'Tidak diketahui' }}
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-tag me-1"></i>
                                    {{ $book->kategori?->nama_kategori_buku ?? 'Tidak ada kategori' }}
                                </p>
                                <p class="flex-grow-1 text-truncate">
                                    {{ \Illuminate\Support\Str::limit($book->deskripsi, 120) }}
                                </p>
                                <div class="d-flex gap-2 mt-3">
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

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Keranjang Peminjaman
                </div>
                <div class="card-body">
                    @if($selectedBooksInfo->isEmpty())
                        <p class="text-muted mb-0">Belum ada buku yang dipilih.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($selectedBooksInfo as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold">{{ $item->nama_buku }}</div>
                                        <small class="text-muted">
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
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        wire:click="removeFromSelection({{ $item->id }})"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <div class="d-flex gap-2 mt-3">
                            <button
                                type="button"
                                class="btn btn-outline-secondary w-50"
                                wire:click="clearSelection"
                            >
                                Bersihkan
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary w-50"
                                wire:click="generateLoanCode"
                            >
                                Buat Kode
                            </button>
                        </div>
                        @error('selection')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        @endif
                    </div>
                </div>

            @if ($detailBook)
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        Detail Buku
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $detailBook->nama_buku }}</h5>
                        <p class="text-muted mb-2">
                            <i class="bi bi-person me-1"></i>
                            {{ $detailBook->author?->nama_author ?? 'Tidak diketahui' }}
                        </p>
                        <p class="text-muted mb-2">
                            <i class="bi bi-tag me-1"></i>
                            {{ $detailBook->kategori?->nama_kategori_buku ?? 'Tidak ada kategori' }}
                        </p>
                        <p class="text-muted mb-2">
                            <i class="bi bi-buildings me-1"></i>
                            {{ $detailBook->penerbit?->nama_penerbit ?? 'Tidak diketahui' }}
                        </p>
                        <p class="text-muted mb-2">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ optional($detailBook->tanggal_terbit)->translatedFormat('d F Y') }}
                        </p>
                        <p class="text-muted mb-2">
                            <i class="bi bi-box-seam me-1"></i>
                            {{ $detailBook->stok > 0 ? 'Stok tersisa: '.$detailBook->stok : 'Stok habis' }}
                        </p>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="ratio ratio-3x4 border rounded">
                                    @if ($detailBook->cover_depan)
                                        <img
                                            src="{{ asset('storage/'.$detailBook->cover_depan) }}"
                                            alt="Cover depan"
                                            class="object-fit-cover rounded"
                                        >
                                    @else
                                        <span class="text-muted small d-flex align-items-center justify-content-center">
                                            Tidak ada cover
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="ratio ratio-3x4 border rounded">
                                    @if ($detailBook->cover_belakang)
                                        <img
                                            src="{{ asset('storage/'.$detailBook->cover_belakang) }}"
                                            alt="Cover belakang"
                                            class="object-fit-cover rounded"
                                        >
                                    @else
                                        <span class="text-muted small d-flex align-items-center justify-content-center">
                                            Tidak ada cover
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">{{ $detailBook->deskripsi }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
