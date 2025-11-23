<div>
    @if (session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2500)"
        class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row align-items-center g-2 g-md-3 mb-3">

                <div class="col-12 col-md">
                    <div class="d-flex flex-wrap align-items-stretch gap-2">

                        <div style="flex: 0 1 180px;">
                            <div class="input-group input-group-sm">
<input
                                    type="text"
                                    class="form-control"
                                    placeholder="Nama / NISN"
                                    wire:model.live.debounce.500ms="search"
                                >
                            </div>
                        </div>

                        <div style="flex: 0 1 120px;">
                            <select
                                class="form-select form-select-sm"
                                wire:model.live="genderFilter"
                            >
                                @foreach ($genderOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="flex: 0 1 140px;">
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
                        <button
                            wire:click="create"
                            type="button"
                            class="btn btn-primary btn-sm w-100 w-md-auto"
                            data-bs-toggle="modal"
                            data-bs-target="#modal-form"
                        >
                            <i class="bi bi-plus"></i>
                            <span class="ms-1">Tambah Siswa</span>
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="modal fade text-left"
                id="modal-form"
                tabindex="-1"
                role="dialog"
                aria-labelledby="modalLabel"
                aria-hidden="true"
                wire:ignore.self
            >
                <div class="modal-dialog modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $siswa_id ? 'Edit Data Siswa' : 'Tambah Data Siswa' }}
                            </h5>
                            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="store" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="nama" class="form-control" wire:model.defer="nama">
                                    @error('nama')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" class="form-control" wire:model.defer="email">
                                    @error('email')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Nomor Telepon</label>
                                    <input type="text" id="phone_number" class="form-control"
                                        wire:model.defer="phone_number">
                                    @error('phone_number')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" id="password" class="form-control"
                                                wire:model.defer="password">
                                            @error('password')
                                            <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">
                                                Konfirmasi Password
                                            </label>
                                            <input type="password" id="password_confirmation" class="form-control"
                                                wire:model.defer="password_confirmation">
                                            @error('password_confirmation')
                                            <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nisn" class="form-label">NISN</label>
                                            <input type="text" id="nisn" class="form-control" wire:model.defer="nisn">
                                            @error('nisn')
                                            <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nis" class="form-label">NIS</label>
                                            <input type="text" id="nis" class="form-control" wire:model.defer="nis">
                                            @error('nis')
                                            <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select id="jenis_kelamin" class="form-select" wire:model.defer="jenis_kelamin">
                                        <option value="laki-laki">Laki-laki</option>
                                        <option value="perempuan">Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="kelas_id" class="form-label">Kelas</label>
                                            <select id="kelas_id" class="form-select" wire:model.defer="kelas_id">
                                                <option value="">Pilih Kelas</option>
                                                @foreach ($this->kelasOptions as $kelas)
                                                    <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                                @endforeach
                                            </select>
                                            @error('kelas_id')
                                            <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jurusan_id" class="form-label">Jurusan</label>
                                            <select id="jurusan_id" class="form-select" wire:model.defer="jurusan_id">
                                                <option value="">Pilih Jurusan</option>
                                                @foreach ($this->jurusanOptions as $jurusan)
                                                    <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                                                @endforeach
                                            </select>
                                            @error('jurusan_id')
                                            <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <input type="text" id="alamat" class="form-control" wire:model.defer="alamat">
                                    @error('alamat')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="foto" class="form-label">Foto</label>
                                    <input
                                        type="file"
                                        id="foto"
                                        class="form-control"
                                        wire:model="foto"
                                        accept="image/jpeg,image/png"
                                    >
                                    @error('foto')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror

                                    <div class="mt-3">
                                        @if ($this->canPreviewImage($foto))
                                            <img
                                                src="{{ $foto->temporaryUrl() }}"
                                                alt="Preview"
                                                class="img-fluid rounded"
                                                style="max-width: 200px;"
                                            >
                                        @elseif ($existingFoto)
                                            <img
                                                src="{{ $this->imageUrl($existingFoto, 'admin/foto-siswa') }}"
                                                alt="Foto Siswa"
                                                class="img-fluid rounded"
                                                style="max-width: 200px;"
                                            >
                                        @endif
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                        Tutup
                                    </button>
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        wire:loading.attr="disabled"
                                        wire:target="store,foto"
                                    >
                                        <span wire:loading.remove>
                                            {{ $siswa_id ? 'Simpan Perubahan' : 'Simpan' }}
                                        </span>
                                        <span wire:loading>Memproses...</span>
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
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>NISN</th>
                        <th>NIS</th>
                        <th>Jenis Kelamin</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Alamat</th>
                        <th>Foto</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->listSiswa as $item)
                    <tr wire:key="row-{{ $item->id }}">
                        <td class="text-center">
                            {{ $loop->iteration + ($this->listSiswa->currentPage() - 1) * $this->listSiswa->perPage() }}
                        </td>
                        <td>{{ $item->user->nama_user ?? '-' }}</td>
                        <td>{{ $item->user->email_user ?? '-' }}</td>
                        <td>{{ $item->user->phone_number ?? '-' }}</td>
                        <td>{{ $item->nisn }}</td>
                        <td>{{ $item->nis }}</td>
                        <td class="text-capitalize">{{ str_replace('-', ' ', $item->jenis_kelamin) }}</td>
                        <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                        <td>{{ $item->jurusan->nama_jurusan ?? '-' }}</td>
                        <td>{{ $item->alamat ?? '-' }}</td>
                        <td class="w-25">
                            @if ($item->foto)
                                <img
                                    src="{{ $this->imageUrl($item->foto, 'admin/foto-siswa') }}"
                                    class="img-fluid rounded"
                                    alt="Foto Siswa"
                                >
                            @else
                                <span class="text-muted">Belum ada foto</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button
                                    wire:click="edit({{ $item->id }})"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modal-form"
                                    class="btn btn-sm btn-warning"
                                >
                                    Edit
                                </button>

                                <button
                                    wire:confirm="Yakin ingin menghapus data {{ $item->user->nama_user ?? 'siswa' }}?"
                                    wire:click="delete({{ $item->id }})"
                                    class="btn btn-sm btn-danger"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted">Belum ada data siswa.</td>
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
                    {{ $this->listSiswa->onEachSide(1)->links() }}
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
