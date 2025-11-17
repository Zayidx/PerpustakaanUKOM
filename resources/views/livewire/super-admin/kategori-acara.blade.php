@php
    use Illuminate\Support\Str;
@endphp

<div>
    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 2500)"
             class="alert alert-info">
            {{ session('message') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row align-items-center g-2 g-md-3 mb-3">
                <div class="col-12 col-md">
                    <div class="d-flex flex-wrap align-items-stretch gap-2">
                        <div style="flex: 0 1 240px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text"
                                       class="form-control"
                                       placeholder="Cari nama kategori"
                                       wire:model.live.debounce.500ms="search">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-auto">
                    <div class="d-grid d-md-flex justify-content-md-end">
                        <button wire:click="create"
                                type="button"
                                class="btn btn-primary btn-sm w-100 w-md-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-kategori-acara">
                            <i class="bi bi-plus"></i>
                            <span class="ms-1">Tambah Kategori</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 64px;">No.</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-center" style="width: 140px;">Total Acara</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kategoriList as $index => $kategori)
                            <tr wire:key="kategori-acara-{{ $kategori->id }}">
                                <td class="text-center">{{ $kategoriList->firstItem() + $index }}</td>
                                <td class="fw-semibold">{{ $kategori->nama }}</td>
                                <td>{{ $kategori->deskripsi ? Str::limit($kategori->deskripsi, 120) : '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary">{{ $kategori->acara_count }}</span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-warning"
                                                wire:click="edit({{ $kategori->id }})"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-kategori-acara">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                                wire:confirm="Hapus kategori ini?"
                                                wire:click="delete({{ $kategori->id }})">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada kategori acara.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label me-0 mb-0">Data per halaman</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm w-auto">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="small text-muted">
                    Menampilkan {{ $kategoriList->count() }} dari {{ $kategoriList->total() }} kategori
                </div>
                <div>
                    {{ $kategoriList->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="modal-kategori-acara" tabindex="-1" role="dialog" aria-labelledby="modalKategoriAcaraLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKategoriAcaraLabel">{{ $kategoriId ? 'Edit Kategori Acara' : 'Tambah Kategori Acara' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="namaKategori" class="form-label">Nama Kategori</label>
                            <input type="text" id="namaKategori" class="form-control" wire:model.defer="nama">
                            @error('nama')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="deskripsiKategori" class="form-label">Deskripsi</label>
                            <textarea id="deskripsiKategori" class="form-control" rows="3" wire:model.defer="deskripsi" placeholder="Opsional"></textarea>
                            @error('deskripsi')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('close-modal', ({ id }) => {
                const modalElement = document.getElementById(id);
                if (!modalElement) {
                    return;
                }

                const instance = bootstrap.Modal.getInstance(modalElement) ?? new bootstrap.Modal(modalElement);
                instance.hide();
            });
        });
    </script>
@endpush
