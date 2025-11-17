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
                                    placeholder="Cari nama/desk/tahun"
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
                            <span class="ms-1">Tambah Penerbit</span>
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="modal fade text-left" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                 aria-hidden="true" wire:ignore.self>
                <div class="modal-dialog modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $nama_penerbit ? 'Edit Data Penerbit' : 'Tambah Data Penerbit' }}
                            </h5>
                            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form wire:submit.prevent="store">
                                
                                <div class="mb-3">
                                    <label for="nama_penerbit" class="form-label">Nama Penerbit</label>
                                    <input type="text" id="nama_penerbit" class="form-control"
                                           wire:model.defer="nama_penerbit" placeholder="Masukkan nama penerbit">
                                    @error('nama_penerbit')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea id="deskripsi" class="form-control" rows="3"
                                              wire:model.defer="deskripsi" placeholder="Masukkan deskripsi penerbit"></textarea>
                                    @error('deskripsi')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                
                                <div class="mb-3">
                                    <label for="logo" class="form-label">Logo</label>
                                    <input type="file" id="logo" class="form-control" wire:model="logo">
                                    @error('logo')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror

                                    
                                    @if ($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" alt="Preview Logo" class="mt-2 rounded" width="200">
                                    @elseif ($existingLogo)
                                        <img src="{{ asset('storage/' . $existingLogo) }}" alt="Logo Penerbit" class="mt-2 rounded" width="200">
                                    @endif
                                </div>

                                
                                <div class="mb-3">
                                    <label for="tahun_hakcipta" class="form-label">Tahun Hak Cipta</label>
                                    <input type="number" id="tahun_hakcipta" class="form-control"
                                           wire:model.defer="tahun_hakcipta" placeholder="Masukkan tahun hak cipta">
                                    @error('tahun_hakcipta')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            {{ $nama_penerbit ? 'Simpan Perubahan' : 'Simpan' }}
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
                            <th>Nama Penerbit</th>
                            <th>Deskripsi</th>
                            <th>Logo</th>
                            <th>Tahun Hak Cipta</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->listPenerbit as $index => $item)
                            <tr wire:key="penerbit-{{ $item->id }}">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_penerbit }}</td>
                                <td>{{ $item->deskripsi ?? '-' }}</td>
                                <td>
                                    @if ($item->logo)
                                        <img src="{{ asset('storage/' . $item->logo) }}" alt="Logo" class="rounded" width="100">
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </td>
                                <td>{{ $item->tahun_hakcipta ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button wire:click="edit({{ $item->id }})" data-bs-toggle="modal"
                                                data-bs-target="#modal-form" class="btn btn-sm btn-warning">
                                            Edit
                                        </button>
                                        <button
                                            wire:confirm="Yakin ingin menghapus penerbit '{{ $item->nama_penerbit }}'?"
                                            wire:click="delete({{ $item->id }})"
                                            class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Belum ada data penerbit.
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
                    {{ $this->listPenerbit->onEachSide(1)->links() }}
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
