<div> {{-- Container utama komponen Livewire --}}
    @if (session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2500)"
        class="alert alert-success"> {{-- Alert flash sukses --}}
        {{ session('message') }}
    </div>
    @endif

    <div class="card"> {{-- Kartu utama halaman --}}
        <div class="card-body">
            <div class="row align-items-md-center justify-content-md-between g-2 g-md-3 mb-3">
                {{-- Filter dan aksi --}}
                <div class="col-12 col-md-4 col-lg-4 col-xl-3">
                    <div class="d-flex flex-column flex-lg-row flex-lg-wrap align-items-stretch gap-2">
                        <div class="input-group input-group-sm flex-grow-1">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control w-100" placeholder="Masukan Nama/NISN"
                                wire:model.live.debounce.500ms="search">
                        </div>
                        <select class="form-select form-select-sm w-100 flex-lg-fill" wire:model.live="genderFilter">
                            @foreach ($genderOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <select class="form-select form-select-sm w-100 flex-lg-fill" wire:model.live="sort">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-3 col-lg-3 col-xl-2 d-flex justify-content-md-end">
                    <button wire:click="create" type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal"
                        data-bs-target="#modal-form"> {{-- Tombol tambah siswa --}}
                        <i class="bi bi-plus"></i>
                        <span class="ms-1">Tambah Siswa</span>
                    </button>
                </div>
            </div>

            <div class="modal fade text-left" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                aria-hidden="true" wire:ignore.self> {{-- Modal create/edit siswa --}}
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
                            <form wire:submit.prevent="store" enctype="multipart/form-data"> {{-- Form Livewire --}}
                                <div class="mb-3"> {{-- Input nama --}}
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="nama" class="form-control" wire:model.defer="nama">
                                    @error('nama')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3"> {{-- Input email --}}
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" class="form-control" wire:model.defer="email">
                                    @error('email')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3"> {{-- Input telepon --}}
                                    <label for="phone_number" class="form-label">Nomor Telepon</label>
                                    <input type="text" id="phone_number" class="form-control"
                                        wire:model.defer="phone_number">
                                    @error('phone_number')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row"> {{-- Input password & konfirmasi --}}
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
                                            <label for="password_confirmation" class="form-label">Konfirmasi
                                                Password</label>
                                            <input type="password" id="password_confirmation" class="form-control"
                                                wire:model.defer="password_confirmation">
                                            @error('password_confirmation')
                                            <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row"> {{-- Input NISN dan NIS --}}
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

                                <div class="mb-3"> {{-- Input jenis kelamin --}}
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select id="jenis_kelamin" class="form-select" wire:model.defer="jenis_kelamin">
                                        <option value="laki-laki">Laki-laki</option>
                                        <option value="perempuan">Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3"> {{-- Input alamat --}}
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <input type="text" id="alamat" class="form-control" wire:model.defer="alamat">
                                    @error('alamat')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3"> {{-- Input foto dengan preview --}}
                                    <label for="foto" class="form-label">Foto</label>
                                    <input type="file" id="foto" class="form-control" wire:model="foto"
                                        accept="image/*">
                                    @error('foto')
                                    <span class="text-danger small">{{ $message }}</span>
                                    @enderror

                                    <div class="mt-3">
                                        @if ($foto)
                                        <img src="{{ $foto->temporaryUrl() }}" alt="Preview" class="img-fluid rounded"
                                            style="max-width: 200px;">
                                        @elseif ($existingFoto)
                                        <img src="{{ asset('storage/' . $existingFoto) }}" alt="Foto Siswa"
                                            class="img-fluid rounded" style="max-width: 200px;">
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer"> {{-- Tombol aksi modal --}}
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                                        wire:target="store,foto">
                                        <span wire:loading.remove>{{ $siswa_id ? 'Simpan Perubahan' : 'Simpan' }}</span>
                                        <span wire:loading>Memproses...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive"> {{-- Tabel daftar siswa --}}
            <table class="table table-striped">
                <thead>
                    <tr> {{-- Header tabel --}}
                        <th class="text-center">No.</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>NISN</th>
                        <th>NIS</th>
                        <th>Jenis Kelamin</th>
                        <th>Alamat</th>
                        <th>Foto</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->listSiswa as $item)
                    <tr wire:key="row-{{ $item->id }}"> {{-- Satu baris data siswa --}}
                        <td class="text-center">
                            {{ $loop->iteration + ($this->listSiswa->currentPage() - 1) * $this->listSiswa->perPage() }}
                        </td>
                        <td>{{ $item->user->nama_user ?? '-' }}</td>
                        <td>{{ $item->user->email_user ?? '-' }}</td>
                        <td>{{ $item->user->phone_number ?? '-' }}</td>
                        <td>{{ $item->nisn }}</td>
                        <td>{{ $item->nis }}</td>
                        <td class="text-capitalize">{{ str_replace('-', ' ', $item->jenis_kelamin) }}</td>
                        <td>{{ $item->alamat ?? '-' }}</td>
                        <td class="w-25">
                            @if ($item->foto)
                            <img src="{{ asset('storage/' . $item->foto) }}" class="img-fluid rounded" alt="Foto Siswa">
                            @else
                            <span class="text-muted">Belum ada foto</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button wire:click="edit({{ $item->id }})" data-bs-toggle="modal"
                                    data-bs-target="#modal-form" class="btn btn-sm btn-warning">Edit</button>
                                {{-- Tombol edit --}}
                                <button
                                    wire:confirm="Yakin ingin menghapus data {{ $item->user->nama_user ?? 'siswa' }}?"
                                    wire:click="delete({{ $item->id }})" class="btn btn-sm btn-danger">Hapus</button>
                                {{-- Tombol hapus --}}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">Belum ada data siswa.</td>
                        {{-- Pesan tabel kosong --}}
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer"> {{-- Kontrol pagination --}}
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div>
                    <label class="form-label me-2 mb-0">Data per halaman</label> {{-- Dropdown jumlah data --}}
                    <select wire:model.live="perPage" class="form-select form-select-sm w-auto d-inline-block">
                        @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    {{ $this->listSiswa->onEachSide(1)->links() }} {{-- Navigasi pagination --}}
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
    const instance = bootstrap.Modal.getInstance(modalElement) ?? new bootstrap.Modal(modalElement); {
        {
            --Tutup modal bootstrap setelah aksi Livewire--
        }
    }
    instance.hide();
});
</script>
