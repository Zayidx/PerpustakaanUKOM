@php
    use Illuminate\Support\Str;
@endphp

<div class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <article class="card border-0 shadow-sm">
                    @if ($announcement->thumbnail_url)
                        <img src="{{ $announcement->thumbnail_url }}"
                             alt="{{ $announcement->judul }}"
                             class="card-img-top img-fluid object-fit-cover"
                             style="max-height: 360px;">
                    @endif

                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                {{ $announcement->kategori->nama ?? 'Umum' }}
                            </span>
                            <small class="text-muted">
                                <i class="fa-regular fa-calendar me-1"></i>
                                {{ $announcement->published_at?->translatedFormat('d F Y, H:i') ?? '-' }}
                            </small>
                            <small class="text-muted">
                                <i class="fa-regular fa-user me-1"></i>
                                {{ $announcement->admin->nama_user ?? 'Admin' }}
                            </small>
                        </div>

                        <h1 class="fw-bold mb-3">{{ $announcement->judul }}</h1>

                        @if ($announcement->thumbnail_caption)
                            <p class="text-muted fst-italic small">{{ $announcement->thumbnail_caption }}</p>
                        @endif

                        <div class="announcement-content">
                            {!! $announcement->konten_html !!}
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('landing.pengumuman') }}" class="btn btn-primary">
                                <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Pengumuman
                            </a>
                        </div>
                    </div>
                </article>
            </div>

            <aside class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Bagikan Pengumuman</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fa-brands fa-facebook-f me-1"></i> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($announcement->judul) }}"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-info">
                                    <i class="fa-brands fa-x-twitter me-1"></i> X
                                </a>
                                <a href="https://wa.me/?text={{ urlencode($announcement->judul . ' ' . request()->fullUrl()) }}"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-success">
                                    <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Pengumuman Lainnya</h5>
                            <div class="list-group list-group-flush">
                                @forelse ($otherAnnouncements as $item)
                                    <a href="{{ route('landing.pengumuman.detail', $item->slug) }}"
                                       class="list-group-item list-group-item-action py-3">
                                        <div class="d-flex gap-3">
                                            @if ($item->thumbnail_url)
                                                <img src="{{ $item->thumbnail_url }}"
                                                     alt="{{ $item->judul }}"
                                                     class="rounded"
                                                     style="width: 72px; height: 72px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <span class="badge bg-primary-subtle text-primary mb-1">
                                                    {{ $item->kategori->nama ?? 'Umum' }}
                                                </span>
                                                <h6 class="fw-semibold mb-1 text-dark">{{ Str::limit($item->judul, 60) }}</h6>
                                                <small class="text-muted">
                                                    <i class="fa-regular fa-calendar me-1"></i>
                                                    {{ $item->published_at?->translatedFormat('d M Y') ?? '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        Belum ada pengumuman lainnya.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .announcement-content {
            font-size: 1.05rem;
            line-height: 1.75;
            color: #4b5563;
        }

        .announcement-content h2,
        .announcement-content h3,
        .announcement-content h4 {
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .announcement-content img {
            max-width: 100%;
            border-radius: 0.75rem;
            margin: 1.5rem 0;
        }

        .announcement-content ul,
        .announcement-content ol {
            padding-left: 1.5rem;
        }

        .announcement-content blockquote {
            border-left: 4px solid #3b82f6;
            padding-left: 1rem;
            margin: 1.5rem 0;
            font-style: italic;
            color: #1f2937;
        }
    </style>
@endpush
