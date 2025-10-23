<div>
    {{-- Notifikasi sukses --}}
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
            {{-- Tombol tambah --}}
            <div class="d-flex justify-content-end gap-2 mb-3">
                <button wire:click="create" type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#modal-form">
                    <i class="bi bi-plus"></i>
                    <span class="ms-1">Tambah Author</span>
                </button>
            </div>

            {{-- Modal Form --}}
            <div class="modal fade text-left" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                aria-hidden="true" wire:ignore.self>
                <div class="modal-dialog modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $nama_author ? 'Edit Data Author' : 'Tambah Data Author' }}
                            </h5>
                            <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form wire:submit.prevent="store">
                                {{-- Nama Author --}}
                                <div class="mb-3">
                                    <label for="nama_author" class="form-label">Nama Author</label>
                                    <input type="text" id="nama_author" class="form-control"
                                        wire:model.defer="nama_author" placeholder="Masukkan nama penulis">
                                    @error('nama_author')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Email Author --}}
                                <div class="mb-3">
                                    <label for="email_author" class="form-label">Email Author</label>
                                    <input type="email" id="email_author" class="form-control"
                                        wire:model.defer="email_author" placeholder="Masukkan email author">
                                    @error('email_author')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Nomor Telepon --}}
                                <div class="mb-3">
                                    <label for="no_telp" class="form-label">Nomor Telepon</label>
                                    <input type="text" id="no_telp" class="form-control"
                                        wire:model.defer="no_telp" placeholder="Masukkan nomor telepon author">
                                    @error('no_telp')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Alamat --}}
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <input type="text" id="alamat" class="form-control"
                                        wire:model.defer="alamat" placeholder="Masukkan alamat author">
                                    @error('alamat')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Foto --}}
                                <div class="mb-3">
                                    <label for="foto" class="form-label">Foto</label>
                                    <input type="file" id="foto" class="form-control" wire:model="foto">
                                    @error('foto')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror

                                    {{-- Preview foto baru --}}
                                    @if ($foto)
                                        <img src="{{ $foto->temporaryUrl() }}" alt="Preview Foto" class="mt-2 rounded" width="200">
                                    @elseif ($existingFoto)
                                        <img src="{{ asset('storage/' . $existingFoto) }}" alt="Foto Author" class="mt-2 rounded" width="200">
                                    @endif
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            {{ $nama_author ? 'Simpan Perubahan' : 'Simpan' }}
                                        </span>
                                        <span wire:loading>Memproses...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel daftar author --}}
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Author</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->listAuthor as $index => $item)
                            <tr wire:key="author-{{ $item->id }}">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_author }}</td>
                                <td>{{ $item->email_author ?? '-' }}</td>
                                <td>{{ $item->no_telp ?? '-' }}</td>
                                <td>{{ $item->alamat ?? '-' }}</td>
                                <td>
                                    @if ($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto" class="rounded" width="100">
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button wire:click="edit({{ $item->id }})" data-bs-toggle="modal"
                                            data-bs-target="#modal-form" class="btn btn-sm btn-warning">
                                            Edit
                                        </button>
                                        <button
                                            wire:confirm="Yakin ingin menghapus author '{{ $item->nama_author }}'?"
                                            wire:click="delete({{ $item->id }})"
                                            class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada data author.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
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
                    {{ $this->listAuthor->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script untuk menutup modal setelah submit --}}
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
