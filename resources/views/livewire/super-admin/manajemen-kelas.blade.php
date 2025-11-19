<div>
    @if (session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2500)"
        class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row align-items-center justify-content-between g-2 g-md-3 mb-3">

                <div class="col-12 col-md">
                    <div class="d-flex flex-wrap align-items-stretch gap-2">

                        <div style="flex: 0 1 200px;">
                            <div class="input-group input-group-sm">
<input type="text" class="form-control" placeholder="Cari nama atau tingkat"
                                    wire:model.live.debounce.500ms="search" aria-label="Cari nama atau tingkat">
                            </div>
                        </div>


                        <div style="flex: 0 1 160px;">
                            <select class="form-select form-select-sm" wire:model.live="sort" aria-label="Urutkan data">
                                @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


                <div class="col-12 col-md-auto">
                    <div class="d-grid d-md-flex justify-content-md-end">
                        <button wire:click="create" type="button" class="btn btn-primary btn-sm w-100 w-md-auto"
                            data-bs-toggle="modal" data-bs-target="#modal-form-kelas">
                            <i class="bi bi-plus"></i>
                            <span class="ms-1">Tambah Kelas</span>
                        </button>
                    </div>
                </div>
            </div>


            <div class="modal fade text-left" id="modal-form-kelas" tabindex="-1" role="dialog"
                aria-labelledby="modalKelasLabel" aria-hidden="true" wire:ignore.self>
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $kelas_id ? 'Edit Data Kelas' : 'Tambah Data Kelas' }}
                            </h5>
                            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="store">
                                <div class="mb-3">
                                    <label for="nama_kelas" class="form-label">Nama Kelas</label>
                                    <input type="text" id="nama_kelas" class="form-control"
                                        wire:model.defer="nama_kelas">
                                    @error('nama_kelas')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="tingkat" class="form-label">Tingkat</label>
                                    <input type="text" id="tingkat" class="form-control" wire:model.defer="tingkat">
                                    @error('tingkat')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                                        wire:target="store">
                                        <span wire:loading.remove>{{ $kelas_id ? 'Simpan Perubahan' : 'Simpan' }}</span>
                                        <span wire:loading>Menyimpan...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center">No.</th>
                        <th>Nama Kelas</th>
                        <th>Tingkat</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->listKelas as $item)
                    <tr wire:key="kelas-{{ $item->id }}">
                        <td class="text-center">
                            {{ $loop->iteration + ($this->listKelas->currentPage() - 1) * $this->listKelas->perPage() }}
                        </td>
                        <td>{{ $item->nama_kelas }}</td>
                        <td>{{ $item->tingkat }}</td>
                        <td>{{ optional($item->created_at)->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <button wire:click="edit({{ $item->id }})" data-bs-toggle="modal"
                                    data-bs-target="#modal-form-kelas" class="btn btn-sm btn-warning">Edit</button>
                                <button wire:click="delete({{ $item->id }})"
                                    wire:confirm="Yakin ingin menghapus kelas {{ $item->nama_kelas }}?"
                                    class="btn btn-sm btn-danger">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada data kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div>
                    <label class="form-label me-2 mb-0">Data per halaman</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm w-auto d-inline-block">
                        @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    {{ $this->listKelas->onEachSide(1)->links() }}
                </div>
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