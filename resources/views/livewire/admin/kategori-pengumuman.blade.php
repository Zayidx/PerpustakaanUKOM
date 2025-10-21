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
                        <div style="flex: 1 1 220px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text"
                                       class="form-control"
                                       placeholder="Cari nama kategori"
                                       wire:model.live.debounce.500ms="search">
                            </div>
                        </div>

                        <div style="flex: 0 1 120px;">
                            <select class="form-select form-select-sm"
                                    wire:model.live="perPage">
                                @foreach ($perPageOptions as $value)
                                    <option value="{{ $value }}">{{ $value }}/hal</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-auto">
                    <div class="d-grid d-md-flex justify-content-md-end">
                        <button wire:click="create"
                                type="button"
                                class="btn btn-primary btn-sm w-100 w-md-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-kategori">
                            <i class="bi bi-plus"></i>
                            <span class="ms-1">Tambah Kategori</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">No.</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-center" style="width: 160px;">Total Pengumuman</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kategoriList as $index => $kategori)
                            <tr wire:key="kategori-{{ $kategori->id }}">
                                <td class="text-center">
                                    {{ $kategoriList->firstItem() + $index }}
                                </td>
                                <td class="fw-semibold">
                                    {{ $kategori->nama }}
                                </td>
                                <td>
                                    {{ $kategori->deskripsi ? Str::limit($kategori->deskripsi, 120) : '-' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $kategori->pengumuman_count }}</span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button wire:click="edit({{ $kategori->id }})"
                                                class="btn btn-sm btn-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-kategori">
                                            Edit
                                        </button>
                                        <button wire:click="delete({{ $kategori->id }})"
                                                wire:confirm="Hapus kategori ini?"
                                                class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Belum ada data kategori pengumuman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div class="small text-muted">
                    Menampilkan {{ $kategoriList->count() }} dari {{ $kategoriList->total() }} kategori
                </div>
                <div>
                    {{ $kategoriList->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-left"
         id="modal-kategori"
         tabindex="-1"
         role="dialog"
         aria-labelledby="modalKategoriLabel"
         aria-hidden="true"
         wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKategoriLabel">
                        {{ $kategoriId ? 'Edit Kategori Pengumuman' : 'Tambah Kategori Pengumuman' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Kategori</label>
                            <input type="text"
                                   id="nama"
                                   class="form-control"
                                   wire:model.defer="nama">
                            @error('nama')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea id="deskripsi"
                                      rows="3"
                                      class="form-control"
                                      wire:model.defer="deskripsi"
                                      placeholder="Opsional"></textarea>
                            @error('deskripsi')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('close-modal', event => {
        const modalId = event.detail?.id ?? null;
        if (!modalId) {
            return;
        }

        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            return;
        }

        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }

        const instance = bootstrap.Modal.getInstance(modalElement) ?? new bootstrap.Modal(modalElement);
        instance.hide();
    });
</script>
