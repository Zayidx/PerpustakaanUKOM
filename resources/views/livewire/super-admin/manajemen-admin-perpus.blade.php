<div> 
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2500)" class="alert alert-success">
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
<input type="text"
                                       class="form-control"
                                       placeholder="Nama / Email / Mapel"
                                       wire:model.live.debounce.500ms="search">
                            </div>
                        </div>

                        <div style="flex: 0 1 140px;">
                            <select class="form-select form-select-sm"
                                    wire:model.live="genderFilter">
                                @foreach ($genderOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="flex: 0 1 160px;">
                            <select class="form-select form-select-sm"
                                    wire:model.live="sort">
                                @foreach ($sortOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
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
                                data-bs-target="#modal-form">
                            <i class="bi bi-plus"></i>
                            <span class="ms-1">Tambah Admin Perpus</span>
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
                                {{ $admin_perpus_id ? 'Edit Data Admin Perpus' : 'Tambah Data Admin Perpus' }}
                            </h5>
                            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="store" enctype="multipart/form-data">

                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="nama" class="form-control" wire:model.defer="nama_user">
                                    @error('nama_user') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" class="form-control" wire:model.defer="email_user">
                                    @error('email_user') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Nomor Telepon</label>
                                    <input type="text" id="phone_number" class="form-control" wire:model.defer="phone_number">
                                    @error('phone_number') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" id="password" class="form-control" wire:model.defer="password">
                                            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                            <input type="password" id="password_confirmation" class="form-control" wire:model.defer="password_confirmation">
                                            @error('password_confirmation') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="nip" class="form-label">NIP</label>
                                    <input type="text" id="nip" class="form-control" wire:model.defer="nip">
                                    @error('nip') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                                    <input type="text" id="mata_pelajaran" class="form-control" wire:model.defer="mata_pelajaran">
                                    @error('mata_pelajaran') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea id="alamat" rows="2" class="form-control" wire:model.defer="alamat" placeholder="Masukkan alamat lengkap"></textarea>
                                    @error('alamat') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                

                                <div class="mb-3">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select id="jenis_kelamin" class="form-select" wire:model.defer="jenis_kelamin">
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="foto" class="form-label">Foto</label>
                                    <input type="file" id="foto" class="form-control" wire:model="foto" accept="image/*">
                                    @error('foto') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <div class="mt-3">
                                        @if ($foto)
                                            <img src="{{ $foto->temporaryUrl() }}" class="img-fluid rounded" style="max-width:200px;">
                                        @elseif ($existingFoto)
                                            <img src="{{ asset('storage/' . $existingFoto) }}" class="img-fluid rounded" style="max-width:200px;">
                                        @endif
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="store,foto">
                                        <span wire:loading.remove>{{ $admin_perpus_id ? 'Simpan Perubahan' : 'Simpan' }}</span>
                                        <span wire:loading>Memproses...</span>
                                    </button>
                                </div>
                            </form>
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
                            <th>NIP</th>
                            <th>Mata Pelajaran</th>
                            <th>Alamat</th> 
                            <th>Jenis Kelamin</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->listAdminPerpus as $item)
                            <tr wire:key="row-{{ $item->id }}">
                                <td class="text-center">{{ $loop->iteration + ($this->listAdminPerpus->currentPage() - 1) * $this->listAdminPerpus->perPage() }}</td>
                                <td>{{ $item->user->nama_user ?? '-' }}</td>
                                <td>{{ $item->user->email_user ?? '-' }}</td>
                                <td>{{ $item->user->phone_number ?? '-' }}</td>
                                <td>{{ $item->nip ?? '-' }}</td>
                                <td>{{ $item->mata_pelajaran ?? '-' }}</td>
                                <td>{{ $item->alamat ?? '-' }}</td> 
                                <td>{{ $item->jenis_kelamin ?? '-' }}</td>
                                <td class="w-25">
                                    @if ($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}" class="img-fluid rounded" alt="Foto Admin Perpus">
                                    @else
                                        <span class="text-muted">Belum ada foto</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button wire:click="edit({{ $item->id }})" data-bs-toggle="modal"
                                            data-bs-target="#modal-form" class="btn btn-sm btn-warning">Edit</button>
                                        <button wire:confirm="Yakin ingin menghapus data {{ $item->user->nama_user ?? 'Admin Perpus' }}?"
                                            wire:click="delete({{ $item->id }})" class="btn btn-sm btn-danger">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada data Admin Perpus.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center gap-3">
                <div>
                    <label class="form-label me-2 mb-0">Data per halaman</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm w-auto d-inline-block">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    {{ $this->listAdminPerpus->onEachSide(1)->links() }}
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
