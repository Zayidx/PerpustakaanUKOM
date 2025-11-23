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

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div class="d-flex flex-wrap gap-2">
                <select class="form-select form-select-sm shadow-sm"
                        style="max-width: 220px;"
                        wire:model.live="categoryId">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->nama }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('welcome') }}#announcements"
               class="btn btn-primary">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Beranda
            </a>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @forelse ($announcements as $announcement)
                <div class="col" wire:key="announcement-{{ $announcement->id }}">
                    <article class="h-100 card border-0 announcement-card">
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
                                <a href="{{ route('landing.pengumuman.detail', $announcement->slug) }}" class="stretched-link text-decoration-none link-dark announcement-link">
                                    {{ $announcement->judul }}
                                </a>
                            </h5>
                            <p class="card-text text-muted mb-0">
                                {{ Str::limit(strip_tags($announcement->konten_html), 120) }}
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
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

        <div class="mt-4 d-flex justify-content-center">
            {{ $announcements->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

@push('styles')
    <style>
        .announcement-card {
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
            overflow: hidden;
        }

        .announcement-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 45px rgba(15, 23, 42, 0.18);
            background: linear-gradient(145deg, #1f3c88 0%, #3575d3 100%);
        }

        .announcement-card img {
            object-fit: cover;
        }

        .announcement-link {
            color: #1f2937;
            transition: color 0.2s ease;
        }

        .announcement-card:hover .announcement-link,
        .announcement-card:hover .text-muted,
        .announcement-card:hover .card-text {
            color: #ffffff !important;
        }

        .announcement-card .badge {
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.02em;
            padding: 0.35rem 0.9rem;
        }

        .announcement-card:hover .badge {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff;
        }

        .announcement-card:hover small.text-muted {
            color: rgba(255, 255, 255, 0.75) !important;
        }
    </style>
@endpush
