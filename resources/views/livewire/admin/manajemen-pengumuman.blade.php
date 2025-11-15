@php
    use Illuminate\Support\Str;
@endphp

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
        <div class="card-body d-flex flex-column">
            <div class="row align-items-center g-2 g-md-3 mb-3">
                <div class="col-12 col-md">
                    <div class="d-flex flex-wrap align-items-stretch gap-2">
                        <div style="flex: 1 1 220px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text"
                                       class="form-control"
                                       placeholder="Cari judul atau konten"
                                       wire:model.live.debounce.500ms="search">
                            </div>
                        </div>

                        <div style="flex: 0 1 160px;">
                            <select class="form-select form-select-sm"
                                    wire:model.live="statusFilter">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="flex: 0 1 180px;">
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
                    <div class="d-grid d-md-flex justify-content-md-end gap-2">
                       
                        <button wire:click="create"
                                type="button"
                                class="btn btn-primary btn-sm w-100 w-md-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-pengumuman">
                            <i class="bi bi-plus"></i>
                            <span class="ms-1">Tambah Pengumuman</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive flex-grow-1 overflow-auto"
                 style="max-height: calc(100vh - 360px);">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 72px;">No.</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Penulis</th>
                            <th class="text-center" style="width: 120px;">Status</th>
                            <th class="text-nowrap" style="width: 180px;">Tanggal Publikasi</th>
                            <th class="text-center" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengumumanList as $item)
                            <tr wire:key="pengumuman-{{ $item->id }}">
                                <td class="text-center">
                                    {{ $loop->iteration + ($pengumumanList->currentPage() - 1) * $pengumumanList->perPage() }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 260px;">
                                        {{ $item->judul }}
                                    </div>
                                    <div class="small text-muted text-truncate" style="max-width: 260px;">
                                        {{ Str::limit(strip_tags($item->konten_html), 90) }}
                                    </div>
                                </td>
                                <td>{{ $item->kategori->nama ?? '-' }}</td>
                                <td>{{ $item->admin->nama_user ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($item->status === 'published')
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $item->published_at ? $item->published_at->format('d M Y H:i') : 'Belum dipublikasikan' }}
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button wire:click="edit({{ $item->id }})"
                                                class="btn btn-sm btn-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-pengumuman">
                                            Edit
                                        </button>
                                        <button wire:click="delete({{ $item->id }})"
                                                wire:confirm="Hapus pengumuman ini?"
                                                class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada data pengumuman.
                                </td>
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
                        @foreach ($perPageOptions as $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="small text-muted">
                    Menampilkan {{ $pengumumanList->count() }} dari {{ $pengumumanList->total() }} pengumuman
                </div>
                <div>
                    {{ $pengumumanList->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-left"
         id="modal-pengumuman"
         tabindex="-1"
         role="dialog"
         aria-labelledby="modalPengumumanLabel"
         aria-hidden="true"
         wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPengumumanLabel">
                        {{ $pengumumanId ? 'Edit Pengumuman' : 'Tambah Pengumuman' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Pengumuman</label>
                            <input type="text"
                                   id="judul"
                                   class="form-control"
                                   wire:model.defer="judul">
                            @error('judul')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="kategori_pengumuman_id" class="form-label">Kategori</label>
                                <select id="kategori_pengumuman_id"
                                        class="form-select"
                                        wire:model.defer="kategori_pengumuman_id">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategoriOptions as $kategori)
                                        <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                                    @endforeach
                                </select>
                                @error('kategori_pengumuman_id')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            {{-- Penulis otomatis mengikuti admin yang login --}}
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select id="status"
                                        class="form-select"
                                        wire:model.defer="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                                @error('status')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="thumbnail_url" class="form-label">URL Thumbnail</label>
                                <input type="url"
                                       id="thumbnail_url"
                                       class="form-control"
                                       placeholder="https://"
                                       wire:model.defer="thumbnail_url">
                                @error('thumbnail_url')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="thumbnail_caption" class="form-label">Caption Thumbnail</label>
                                <input type="text"
                                       id="thumbnail_caption"
                                       class="form-control"
                                       wire:model.defer="thumbnail_caption">
                                @error('thumbnail_caption')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="konten-editor" class="form-label">Konten Pengumuman</label>
                            <div wire:ignore>
                                <textarea id="konten-editor"></textarea>
                            </div>
                            <div class="form-text">
                                Gunakan toolbar atau sintaks Markdown Laravel untuk memformat konten.
                            </div>
                            @error('konten')
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
@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
        <script>
            document.addEventListener('livewire:init', () => {
                let pengumumanEditor = null;

                const initializeEditor = (content = '') => {
                    const textarea = document.getElementById('konten-editor');
                    if (!textarea) {
                        return;
                    }

                    if (pengumumanEditor) {
                        pengumumanEditor.toTextArea();
                        pengumumanEditor = null;
                    }

                    textarea.value = content ?? '';

                    pengumumanEditor = new EasyMDE({
                        element: textarea,
                        initialValue: content ?? '',
                        spellChecker: false,
                        minHeight: '260px',
                        toolbar: [
                            'bold',
                            'italic',
                            'heading',
                            '|',
                            'quote',
                            'unordered-list',
                            'ordered-list',
                            '|',
                            'link',
                            'image',
                            '|',
                            'preview',
                            'side-by-side',
                            'fullscreen',
                        ],
                    });

                    pengumumanEditor.codemirror.on('change', () => {
                        @this.set('konten', pengumumanEditor.value());
                    });
                };

                Livewire.on('initialize-editor', ({ content }) => {
                    setTimeout(() => initializeEditor(content ?? ''), 75);
                });

                document.addEventListener('hidden.bs.modal', event => {
                    if (event.target?.id === 'modal-pengumuman' && pengumumanEditor) {
                        pengumumanEditor.toTextArea();
                        pengumumanEditor = null;
                    }
                });
            });

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
    @endpush
@endonce
</div>
