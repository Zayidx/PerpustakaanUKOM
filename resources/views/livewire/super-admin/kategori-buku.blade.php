<div>
    
    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 2500)"
             class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center g-2 g-md-3 mb-3">
                <div class="col-12 col-md">
                    <div class="d-flex flex-wrap align-items-stretch gap-2">
                        <div style="flex: 0 1 220px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Cari kategori/deskripsi"
                                    wire:model.live.debounce.500ms="search"
                                >
                            </div>
                        </div>
                        <div style="flex: 0 1 160px;">
                            <select
                                class="form-select form-select-sm"
                                wire:model.live="sort"
                            >
                                @foreach ($sortOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <div class="d-grid d-md-flex justify-content-md-end">
                        <button wire:click="create" type="button" class="btn btn-primary btn-sm w-100 w-md-auto" data-bs-toggle="modal"
                                data-bs-target="#modal-form">
                            <i class="bi bi-plus"></i>
                            <span class="ms-1">Tambah Kategori Buku</span>
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="modal fade text-left" id="modal-form" tabindex="-1" role="dialog"
                 aria-labelledby="modalLabel" aria-hidden="true" wire:ignore.self>
                <div class="modal-dialog modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $editMode ? 'Edit Kategori Buku' : 'Tambah Kategori Buku' }}
                            </h5>
                            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form wire:submit.prevent="store">
                                
                                <div class="mb-3">
                                    <label for="nama_kategori_buku" class="form-label">Nama Kategori Buku</label>
                                    <input type="text" id="nama_kategori_buku" class="form-control"
                                           wire:model.defer="nama_kategori_buku" placeholder="Masukkan nama kategori">
                                    @error('nama_kategori_buku')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                
                                <div class="mb-3">
                                    <label for="deskripsi_kategori_buku" class="form-label">Deskripsi Kategori</label>
                                    <textarea id="deskripsi_kategori_buku" class="form-control" rows="3"
                                              wire:model.defer="deskripsi_kategori_buku" placeholder="Masukkan deskripsi kategori"></textarea>
                                    @error('deskripsi_kategori_buku')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            {{ $editMode ? 'Simpan Perubahan' : 'Simpan' }}
                                        </span>
                                        <span wire:loading>Memproses...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Kategori Buku</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->listKategoriBuku as $index => $item)
                            <tr wire:key="kategori-{{ $item->id }}">
                                <td class="text-center">
                                    {{ $loop->iteration + ($this->listKategoriBuku->currentPage() - 1) * $this->listKategoriBuku->perPage() }}
                                </td>
                                <td>{{ $item->nama_kategori_buku }}</td>
                                <td>{{ $item->deskripsi_kategori_buku }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button wire:click="edit({{ $item->id }})" data-bs-toggle="modal"
                                                data-bs-target="#modal-form" class="btn btn-sm btn-warning">
                                            Edit
                                        </button>
                                        <button
                                            wire:confirm="Yakin ingin menghapus kategori '{{ $item->nama_kategori_buku }}'?"
                                            wire:click="delete({{ $item->id }})"
                                            class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Belum ada data kategori buku.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            
            <div class="card-footer d-flex justify-content-between align-items-center gap-3">
                <div>
                    <label class="form-label me-2 mb-0">Data per halaman</label>
                    <select wire:model="perPage" class="form-select form-select-sm w-auto d-inline-block">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                </div>
                <div>
                    {{ $this->listKategoriBuku->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    window.addEventListener('close-modal', event => {
        const modalId = event.detail?.id ?? null;
        if (!modalId) return;
        const modalElement = document.getElementById(modalId);
        if (!modalElement) return;
        const instance = bootstrap.Modal.getInstance(modalElement) ?? new bootstrap.Modal(modalElement);
        instance.hide();
    });
</script>
