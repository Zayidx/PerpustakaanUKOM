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
        <div class="card-body">
            <div class="row align-items-center g-2 g-md-3 mb-3">
                <div class="col-12 col-md">
                    <div class="d-flex flex-wrap align-items-stretch gap-2">
                        <div style="flex: 0 1 240px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text"
                                       class="form-control"
                                       placeholder="Cari judul atau lokasi"
                                       wire:model.live.debounce.500ms="search">
                            </div>
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
                    <div class="d-grid d-md-flex justify-content-md-end">
                        <button wire:click="create"
                                type="button"
                                class="btn btn-primary btn-sm w-100 w-md-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-acara">
                            <i class="bi bi-plus"></i>
                            <span class="ms-1">Tambah Acara</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 64px;">No.</th>
                            <th>Judul</th>
                            <th style="width: 180px;">Tanggal &amp; Waktu</th>
                            <th>Lokasi</th>
                            <th style="width: 160px;">Kategori</th>
                            <th class="text-center" style="width: 120px;">Poster</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($eventList as $index => $event)
                            <tr wire:key="event-{{ $event->id }}">
                                <td class="text-center">{{ $eventList->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $event->judul }}</div>
                                    <div class="text-muted small">{{ Str::limit(strip_tags($event->deskripsi), 80) }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $event->mulai_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                    <div class="text-muted small">
                                        {{ $event->mulai_at?->translatedFormat('H.i') ?? '-' }}
                                        @if ($event->selesai_at)
                                            &ndash; {{ $event->selesai_at->translatedFormat('H.i') }}
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $event->lokasi }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $event->kategori->nama ?? 'Tidak diketahui' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($event->poster_url)
                                        <a href="{{ $event->poster_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-warning"
                                                wire:click="edit({{ $event->id }})"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-acara">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                                wire:confirm="Hapus acara ini?"
                                                wire:click="delete({{ $event->id }})">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data acara.</td>
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
                    Menampilkan {{ $eventList->count() }} dari {{ $eventList->total() }} acara
                </div>
                <div>
                    {{ $eventList->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="modal-acara" tabindex="-1" role="dialog" aria-labelledby="modalAcaraLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAcaraLabel">{{ $acaraId ? 'Edit Acara' : 'Tambah Acara' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Acara</label>
                            <input type="text" id="judul" class="form-control" wire:model.defer="judul">
                            @error('judul')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="lokasi" class="form-label">Lokasi</label>
                                <input type="text" id="lokasi" class="form-control" wire:model.defer="lokasi">
                                @error('lokasi')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-3">
                                <label for="kategori_acara_id" class="form-label">Kategori Acara</label>
                                <select id="kategori_acara_id" class="form-select" wire:model.defer="kategori_acara_id">
                                    <option value="">Pilih kategori</option>
                                    @foreach ($kategoriOptions as $kategori)
                                        <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                                    @endforeach
                                </select>
                                @error('kategori_acara_id')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-3">
                                <label for="poster_url" class="form-label">Poster (URL)</label>
                                <input type="url" id="poster_url" class="form-control" wire:model.defer="poster_url">
                                @error('poster_url')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="mulai_at_input" class="form-label">Mulai</label>
                                <input type="datetime-local" id="mulai_at_input" class="form-control" wire:model.defer="mulai_at_input">
                                @error('mulai_at_input')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="selesai_at_input" class="form-label">Selesai</label>
                                <input type="datetime-local" id="selesai_at_input" class="form-control" wire:model.defer="selesai_at_input">
                                @error('selesai_at_input')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea id="deskripsi" class="form-control" rows="4" wire:model.defer="deskripsi" placeholder="Ceritakan secara singkat mengenai acara"></textarea>
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
