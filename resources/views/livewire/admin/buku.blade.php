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

    {{-- Kartu utama --}}
    <div class="card">
        <div class="card-body">
            {{-- Tombol Tambah --}}
            <div class="d-flex justify-content-end gap-2 mb-3">
                <button wire:click="create" type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#modal-form">
                    <i class="bi bi-plus"></i>
                    <span class="ms-1">Tambah Buku</span>
                </button>
            </div>

            {{-- Modal Form --}}
            <div class="modal fade text-left" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                 aria-hidden="true" wire:ignore.self>
                <div class="modal-dialog modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $editMode ? 'Edit Data Buku' : 'Tambah Data Buku' }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <form wire:submit.prevent="store">
                                {{-- Nama Buku --}}
                                <div class="mb-3">
                                    <label for="nama_buku" class="form-label">Judul Buku</label>
                                    <input type="text" id="nama_buku" class="form-control"
                                           wire:model.defer="nama_buku" placeholder="Masukkan judul buku">
                                    @error('nama_buku') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                {{-- Author --}}
                                <div class="mb-3">
                                    <label for="author_id" class="form-label">Penulis</label>
                                    <select id="author_id" class="form-select" wire:model.defer="author_id">
                                        <option value="">-- Pilih Penulis --</option>
                                        @foreach($authors as $author)
                                            <option value="{{ $author->id }}">{{ $author->nama_author }}</option>
                                        @endforeach
                                    </select>
                                    @error('author_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                {{-- Kategori --}}
                                <div class="mb-3">
                                    <label for="kategori_id" class="form-label">Kategori</label>
                                    <select id="kategori_id" class="form-select" wire:model.defer="kategori_id">
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($kategori_buku as $kategori)
                                            <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori_buku }}</option>
                                        @endforeach
                                    </select>
                                    @error('kategori_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                {{-- Penerbit --}}
                                <div class="mb-3">
                                    <label for="penerbit_id" class="form-label">Penerbit</label>
                                    <select id="penerbit_id" class="form-select" wire:model.defer="penerbit_id">
                                        <option value="">-- Pilih Penerbit --</option>
                                        @foreach($penerbits as $penerbit)
                                            <option value="{{ $penerbit->id }}">{{ $penerbit->nama_penerbit }}</option>
                                        @endforeach
                                    </select>
                                    @error('penerbit_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                {{-- Tanggal Terbit --}}
                                <div class="mb-3">
                                    <label for="tanggal_terbit" class="form-label">Tanggal Terbit</label>
                                    <input type="date" id="tanggal_terbit" class="form-control"
                                           wire:model.defer="tanggal_terbit">
                                    @error('tanggal_terbit') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                {{-- Cover Depan --}}
                                <div class="mb-3">
                                    <label for="cover_depan" class="form-label">Cover Depan</label>
                                    <input type="file" id="cover_depan" class="form-control" wire:model="cover_depan">
                                    @error('cover_depan') <span class="text-danger small">{{ $message }}</span> @enderror

                                    @if ($cover_depan)
                                        <img src="{{ $cover_depan->temporaryUrl() }}" alt="Preview Cover Depan" class="mt-2 rounded" width="200">
                                    @elseif ($existingCoverDepan)
                                        <img src="{{ asset('storage/' . $existingCoverDepan) }}" alt="Cover Depan Buku" class="mt-2 rounded" width="200">
                                    @endif
                                </div>

                                {{-- Cover Belakang --}}
                                <div class="mb-3">
                                    <label for="cover_belakang" class="form-label">Cover Belakang</label>
                                    <input type="file" id="cover_belakang" class="form-control" wire:model="cover_belakang">
                                    @error('cover_belakang') <span class="text-danger small">{{ $message }}</span> @enderror

                                    @if ($cover_belakang)
                                        <img src="{{ $cover_belakang->temporaryUrl() }}" alt="Preview Cover Belakang" class="mt-2 rounded" width="200">
                                    @elseif ($existingCoverBelakang)
                                        <img src="{{ asset('storage/' . $existingCoverBelakang) }}" alt="Cover Belakang Buku" class="mt-2 rounded" width="200">
                                    @endif
                                </div>

                                {{-- Deskripsi --}}
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea id="deskripsi" class="form-control" rows="3"
                                              wire:model.defer="deskripsi" placeholder="Masukkan deskripsi buku"></textarea>
                                    @error('deskripsi') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <span wire:loading.remove>{{ $editMode ? 'Simpan Perubahan' : 'Simpan' }}</span>
                                        <span wire:loading>Memproses...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel daftar buku --}}
            <div class="table-responsive mt-3">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Kategori</th>
                            <th>Penerbit</th>
                            <th>Tanggal Terbit</th>
                            <th>Cover Depan</th>
                            <th>Cover Belakang</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->listBuku as $item)
                            <tr wire:key="buku-{{ $item->id }}">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_buku }}</td>
                                <td>{{ $item->author->nama_author ?? '-' }}</td>
                                <td>{{ $item->kategori->nama_kategori_buku ?? '-' }}</td>
                                <td>{{ $item->penerbit->nama_penerbit ?? '-' }}</td>
                                <td>{{ optional($item->tanggal_terbit)->format('d M Y') ?? '-' }}</td>
                                <td>
                                    @if ($item->cover_depan)
                                        <img src="{{ asset('storage/' . $item->cover_depan) }}" alt="Cover Depan" class="rounded" width="80">
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->cover_belakang)
                                        <img src="{{ asset('storage/' . $item->cover_belakang) }}" alt="Cover Belakang" class="rounded" width="80">
                                    @else
                                        <span class="text-muted">Tidak ada</span>
                                    @endif
                                </td>
                                <td>{{ $item->deskripsi ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button wire:click="edit({{ $item->id }})" data-bs-toggle="modal"
                                                data-bs-target="#modal-form" class="btn btn-sm btn-warning">
                                            Edit
                                        </button>
                                        <button wire:click="delete({{ $item->id }})" class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada data buku.</td>
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
                    {{ $this->listBuku->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Script untuk menutup modal setelah submit --}}
    <script>
        document.addEventListener('livewire:load', function () {
            window.addEventListener('close-modal', event => {
                const modalId = event.detail?.id ?? null;
                if (!modalId) return;
                const modalElement = document.getElementById(modalId);
                if (!modalElement) return;

                let instance = bootstrap.Modal.getInstance(modalElement);
                if (!instance) {
                    instance = new bootstrap.Modal(modalElement);
                }
                instance.hide();
            });
        });
    </script>
</div>
