@php
    use Illuminate\Support\Str;
@endphp

<div class="py-5">
    <div class="container">
        <div class="row g-4 align-items-end mb-4">
            <div class="col-12 col-lg-8">
                <h1 class="fw-bold mb-2">Pengumuman Perpustakaan</h1>
                <p class="text-muted mb-0">Ikuti perkembangan terbaru mengenai layanan, acara, dan informasi penting lainnya.</p>
            </div>
            <div class="col-12 col-lg-4">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-primary"></i></span>
                    <input type="text"
                           class="form-control border-0"
                           placeholder="Cari judul atau kata kunci..."
                           wire:model.live.debounce.500ms="search">
                </div>
            </div>
        </div>

        <div class="row g-3 g-md-4 align-items-center mb-4">
            <div class="col-12 col-md-6">
                <div class="d-flex flex-wrap gap-2">
                    <select class="form-select form-select-sm shadow-sm"
                            style="max-width: 220px;"
                            wire:model.live="categoryId">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->nama }}</option>
                        @endforeach
                    </select>

                    <select class="form-select form-select-sm shadow-sm"
                            style="max-width: 160px;"
                            wire:model.live="perPage">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }} / halaman</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <small class="text-muted">
                    Menampilkan {{ $announcements->firstItem() ?? 0 }} - {{ $announcements->lastItem() ?? 0 }} dari {{ $announcements->total() }} pengumuman
                </small>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @forelse ($announcements as $announcement)
                <div class="col" wire:key="announcement-{{ $announcement->id }}">
                    <article class="h-100 card border-0 shadow-sm announcement-card">
                        @if ($announcement->thumbnail_url)
                            <img src="{{ $announcement->thumbnail_url }}"
                                 alt="{{ $announcement->judul }}"
                                 class="card-img-top img-fluid object-fit-cover"
                                 style="max-height: 180px;">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $announcement->kategori->nama ?? 'Umum' }}
                                </span>
                                <small class="text-muted">
                                    <i class="fa-regular fa-calendar me-1"></i>
                                    {{ $announcement->published_at?->translatedFormat('d F Y') ?? '-' }}
                                </small>
                            </div>
                            <h5 class="card-title fw-bold mb-2">
                                <a href="{{ route('landing.pengumuman.detail', $announcement->slug) }}" class="stretched-link text-decoration-none text-dark">
                                    {{ $announcement->judul }}
                                </a>
                            </h5>
                            <p class="card-text text-muted mb-0">
                                {{ Str::limit(strip_tags($announcement->konten_html), 120) }}
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 pb-3 px-4">
                            <small class="text-muted">
                                <i class="fa-regular fa-user me-1"></i>
                                {{ $announcement->admin->nama_user ?? 'Admin' }}
                            </small>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <img src="https://illustrations.popsy.co/gray/searching.svg" alt="" class="mb-3" style="max-width: 180px;">
                        <h5 class="fw-semibold">Belum ada pengumuman</h5>
                        <p class="text-muted mb-0">Coba kata kunci lain atau kembali lagi nanti.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $announcements->onEachSide(1)->links() }}
        </div>
    </div>
</div>
