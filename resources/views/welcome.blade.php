@extends('components.layouts.partials.template-welcome')

@php
    use Illuminate\Support\Str;
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        section.scroll-target {
            scroll-margin-top: 96px;
        }

        @media (min-width: 992px) {
            section.scroll-target {
                scroll-margin-top: 120px;
            }
        }

        .announcements-section .announcement-card {
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .announcements-section .announcement-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 45px rgba(15, 23, 42, 0.18);
            background: linear-gradient(145deg, #1f3c88 0%, #3575d3 100%);
        }

        .announcements-section .announcement-card .badge {
            border-radius: 999px;
            letter-spacing: 0.02em;
            padding: 0.35rem 0.9rem;
        }

        .announcements-section .announcement-card:hover .badge {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff;
        }

        .announcements-section .announcement-card a.stretched-link {
            color: #1f2937;
            transition: color 0.2s ease;
        }

        .announcements-section .announcement-card:hover a.stretched-link,
        .announcements-section .announcement-card:hover .text-muted {
            color: #ffffff !important;
        }
    </style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section id="home" class="hero-section py-5 overflow-hidden scroll-target">
        <div class="container">
            <div class="row align-items-center gy-5" data-aos="fade-up" data-aos-duration="900">
                <div class="col-12 col-lg-6 text-center text-lg-start" data-aos="fade-right" data-aos-delay="100">
                    <h1 class="display-4 fw-bold mb-4">Selamat Datang di Pusat Belajar Anda</h1>
                    <p class="lead mb-4">Jelajahi ribuan buku, sumber belajar, dan program yang dirancang untuk menginspirasi perjalanan belajar Anda.</p>
                    <form class="input-group input-group-lg mb-4 mx-auto mx-lg-0 shadow-sm" style="max-width: 420px;" role="search" method="GET" action="{{ route('landing.book-search') }}">
                        <label for="search-books" class="visually-hidden">Cari buku atau penulis</label>
                        <input id="search-books" type="search" name="q" class="form-control" placeholder="Cari buku, penulis..." aria-label="Cari buku atau penulis" value="{{ old('q') }}">
                        <button class="btn btn-primary" type="submit" aria-label="Cari">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center justify-content-lg-start">
                        <a href="{{ route('landing.book-search') }}" class="btn btn-primary btn-lg px-4">Jelajahi Sekarang</a>
                        <a href="#services" class="btn btn-outline-primary btn-lg px-4">Pelajari Lebih Lanjut</a>
                    </div>
                </div>
                <div class="col-12 col-lg-6 text-center" data-aos="fade-left" data-aos-delay="200">
                    <div class="hero-image position-relative mx-auto">
                        <img src="{{ asset('assets/img/hero.webp') }}" alt="Ilustrasi pembaca" class="hero-illustration img-fluid" decoding="async" fetchpriority="high" height="550">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="statistics-section py-5 bg-light scroll-target" data-aos="fade-up" data-aos-delay="100">
        <div class="container">
            <div class="row row-cols-2 row-cols-md-4 g-4 text-center justify-content-center">
                <div class="col" data-aos="zoom-in" data-aos-delay="100">
                    <div class="stat-card p-4 h-100">
                        <h3 class="text-primary fw-bold mb-2">50K+</h3>
                        <p class="text-muted mb-0">Buku Tersedia</p>
                    </div>
                </div>
                <div class="col" data-aos="zoom-in" data-aos-delay="200">
                    <div class="stat-card p-4 h-100">
                        <h3 class="text-primary fw-bold mb-2">2K+</h3>
                        <p class="text-muted mb-0">Anggota Aktif</p>
                    </div>
                </div>
                <div class="col" data-aos="zoom-in" data-aos-delay="300">
                    <div class="stat-card p-4 h-100">
                        <h3 class="text-primary fw-bold mb-2">100+</h3>
                        <p class="text-muted mb-0">Acara Tahunan</p>
                    </div>
                </div>
                <div class="col" data-aos="zoom-in" data-aos-delay="400">
                    <div class="stat-card p-4 h-100">
                        <h3 class="text-primary fw-bold mb-2">24/7</h3>
                        <p class="text-muted mb-0">Akses Digital</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Buku Unggulan Section -->
    <section id="books" class="featured-books py-5 scroll-target" data-aos="fade-up" data-aos-delay="100">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Buku Unggulan</h2>
                <p class="text-muted mb-0">Pilihan terbaik untuk menemani perjalanan belajar Anda.</p>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                <div class="col" data-aos="fade-up" data-aos-delay="150">
                    <div class="book-card h-100 d-flex flex-column">
                        <div class="book-image bg-gradient-1">
                            <img src="{{ asset('assets/img/buku/BungkamSuara.jpg') }}" alt="Sampul Langit Pengetahuan" class="book-thumbnail" loading="lazy" decoding="async" width="320" height="420">
                        </div>
                        <div class="p-3 d-flex flex-column gap-2 flex-grow-1">
                            <div>
                                <h5 class="fw-bold mb-1">Bungkam Suara</h5>
                                <p class="text-muted small mb-2">Oleh JS.Khairen</p>
                                <div class="text-warning small">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-regular fa-star"></i>
                                </div>
                            </div>
                            <a class="btn btn-primary w-100 btn-sm mt-auto" href="#">Pinjam Sekarang</a>
                        </div>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="200">
                    <div class="book-card h-100 d-flex flex-column">
                        <div class="book-image bg-gradient-2">
                            <img src="{{ asset('assets/img/buku/KamiBukanSarjanaKertas.jpg') }}" alt="Sampul Strategi Belajar Efektif" class="book-thumbnail" loading="lazy" decoding="async" width="320" height="420">
                        </div>
                        <div class="p-3 d-flex flex-column gap-2 flex-grow-1">
                            <div>
                                <h5 class="fw-bold mb-1">Kami Bukan Sarjana Kertas</h5>
                                <p class="text-muted small mb-2">Oleh JS.Khairen</p>
                                <div class="text-warning small">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star-half-stroke"></i>
                                    <i class="fa-regular fa-star"></i>
                                </div>
                            </div>
                            <a class="btn btn-primary w-100 btn-sm mt-auto" href="#">Pinjam Sekarang</a>
                        </div>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="250">
                    <div class="book-card h-100 d-flex flex-column">
                        <div class="book-image bg-gradient-3">
                            <img src="{{ asset('assets/img/buku/DompetAyahSepatuIbu.jpeg') }}" alt="Sampul Kronik Sejarah" class="book-thumbnail" loading="lazy" decoding="async" width="320" height="420">
                        </div>
                        <div class="p-3 d-flex flex-column gap-2 flex-grow-1">
                            <div>
                                <h5 class="fw-bold mb-1">Dompet Ayah Sepatu Ibu</h5>
                                <p class="text-muted small mb-2">Oleh JS.Khairen</p>
                                <div class="text-warning small">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star-half-stroke"></i>
                                </div>
                            </div>
                            <a class="btn btn-primary w-100 btn-sm mt-auto" href="#">Pinjam Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section py-5 bg-light scroll-target" data-aos="fade-up" data-aos-delay="100">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Layanan Kami</h2>
                <p class="text-muted mb-0">Fasilitas lengkap untuk mendukung pengalaman belajar terbaik.</p>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4">
                <div class="col" data-aos="zoom-in" data-aos-delay="150">
                    <div class="service-card text-center p-4 h-100">
                        <div class="service-icon">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Koleksi Luas</h5>
                        <p class="text-muted mb-0">Akses ke 50.000+ buku dari berbagai genre dan mata pelajaran.</p>
                    </div>
                </div>
                <div class="col" data-aos="zoom-in" data-aos-delay="200">
                    <div class="service-card text-center p-4 h-100">
                        <div class="service-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Kelompok Belajar</h5>
                        <p class="text-muted mb-0">Ruang belajar kolaboratif untuk kerja kelompok.</p>
                    </div>
                </div>
                <div class="col" data-aos="zoom-in" data-aos-delay="250">
                    <div class="service-card text-center p-4 h-100">
                        <div class="service-icon">
                            <i class="fa-solid fa-laptop"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Sumber Digital</h5>
                        <p class="text-muted mb-0">E-book, jurnal, dan basis data daring tersedia 24/7.</p>
                    </div>
                </div>
                <div class="col" data-aos="zoom-in" data-aos-delay="300">
                    <div class="service-card text-center p-4 h-100">
                        <div class="service-icon">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Program Membaca</h5>
                        <p class="text-muted mb-0">Program interaktif untuk meningkatkan kebiasaan membaca.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="events-section py-5 scroll-target" data-aos="fade-up" data-aos-delay="100">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Acara Mendatang</h2>
                <p class="text-muted mb-0">Ikuti kegiatan terbaru dan perluas jejaring belajar Anda.</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                @forelse ($upcomingEvents as $index => $event)
                    <div class="col" data-aos="fade-up" data-aos-delay="{{ 150 + ($index * 50) }}">
                        <button type="button"
                                class="event-card p-4 h-100 w-100 text-start border-0"
                                data-bs-toggle="modal"
                                data-bs-target="#eventDetailModal"
                                data-title="{{ e($event->judul) }}"
                                data-date="{{ $event->mulai_at?->translatedFormat('d F Y') ?? '-' }}"
                                data-day="{{ $event->mulai_at?->translatedFormat('d') ?? '--' }}"
                                data-month="{{ $event->mulai_at?->translatedFormat('M') ?? '--' }}"
                                data-time="{{ $event->mulai_at?->translatedFormat('H.i') ?? '-' }}{{ $event->selesai_at ? ' - ' . $event->selesai_at->translatedFormat('H.i') : '' }}"
                                data-location="{{ e($event->lokasi) }}"
                                data-description="{{ e(Str::of($event->deskripsi ?? '')->stripTags()->replace(['\r\n', '\n', '\r'], ' ')) }}"
                                data-poster="{{ $event->poster_url ? e($event->poster_url) : '' }}">
                            <div class="d-flex gap-3 align-items-start">
                                <div class="event-date rounded-3 text-center px-3 py-2">
                                    <div class="fw-bold fs-4">{{ $event->mulai_at?->translatedFormat('d') ?? '--' }}</div>
                                    <div class="small text-uppercase">{{ $event->mulai_at?->translatedFormat('M') ?? '--' }}</div>
                                </div>
                                <div>
                                    @if ($event->kategori?->nama)
                                        <span class="badge bg-primary-subtle text-primary mb-2">{{ $event->kategori->nama }}</span>
                                    @endif
                                    <h5 class="fw-bold mb-2 text-dark">{{ $event->judul }}</h5>
                                    <p class="text-muted mb-1"><i class="fa-regular fa-clock me-2"></i>{{ $event->mulai_at?->translatedFormat('H.i') ?? '-' }}@if ($event->selesai_at) &ndash; {{ $event->selesai_at->translatedFormat('H.i') }}@endif</p>
                                    <p class="text-muted mb-0"><i class="fa-solid fa-location-dot me-2"></i>{{ $event->lokasi }}</p>
                                </div>
                            </div>
                        </button>
                    </div>
                @empty
                    <div class="col">
                        <div class="event-card p-4 text-center h-100">
                            <h5 class="fw-bold mb-2">Belum ada acara terjadwal</h5>
                            <p class="text-muted mb-0">Nantikan jadwal acara terbaru dari kami.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventDetailModalLabel">Detail Acara</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="event-date-modal text-center p-3 rounded-3 bg-primary text-white mb-3">
                                <div class="fs-1 fw-bold" id="eventModalDay">--</div>
                                <div class="text-uppercase" id="eventModalMonth">---</div>
                            </div>
                            <div id="eventModalPosterWrapper" class="rounded overflow-hidden d-none">
                                <img src="" alt="Poster Acara" id="eventModalPoster" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h3 class="fw-bold mb-3" id="eventModalTitle"></h3>
                            <p class="text-muted mb-2"><i class="fa-regular fa-calendar me-2"></i><span id="eventModalDate">-</span></p>
                            <p class="text-muted mb-3"><i class="fa-regular fa-clock me-2"></i><span id="eventModalTime">-</span></p>
                            <p class="text-muted mb-4"><i class="fa-solid fa-location-dot me-2"></i><span id="eventModalLocation">-</span></p>
                            <p class="mb-0" id="eventModalDescription"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials Section -->
    <section class="testimonials-section py-5 bg-light scroll-target" data-aos="fade-up" data-aos-delay="100">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Kata Para Siswa</h2>
                <p class="text-muted mb-0">Pengalaman nyata dari para pengunjung Ruang Membaca.</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <div class="col" data-aos="fade-up" data-aos-delay="150">
                    <div class="testimonial-card h-100">
                        <span class="quote-icon"><i class="fa-solid fa-quote-right"></i></span>
                        <div class="mb-3 text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star-half-stroke"></i>
                        </div>
                        <p class="mb-4">"Perpustakaan ini membuat belajar menjadi menyenangkan. Koleksi bukunya lengkap dan ruangannya nyaman!"</p>
                        <div class="author">
                            <div class="avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Alya Pratiwi</h6>
                                <small class="text-muted">Kelas 11</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card h-100">
                        <span class="quote-icon"><i class="fa-solid fa-quote-right"></i></span>
                        <div class="mb-3 text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="mb-4">"Program membaca bulanannya seru sekali! Banyak kegiatan yang mendorong kami untuk rajin membaca."</p>
                        <div class="author">
                            <div class="avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Bima Nugraha</h6>
                                <small class="text-muted">Kelas 10</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="250">
                    <div class="testimonial-card h-100">
                        <span class="quote-icon"><i class="fa-solid fa-quote-right"></i></span>
                        <div class="mb-3 text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <p class="mb-4">"Aplikasi pinjam bukunya memudahkan saya untuk mengatur jadwal bacaan tanpa takut terlambat."</p>
                        <div class="author">
                            <div class="avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Citra Dewi</h6>
                                <small class="text-muted">Kelas 12</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card h-100">
                        <span class="quote-icon"><i class="fa-solid fa-quote-right"></i></span>
                        <div class="mb-3 text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <p class="mb-4">"Admin perpustakaannya ramah dan selalu siap membantu. Sangat nyaman untuk belajar."</p>
                        <div class="author">
                            <div class="avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Dimas Putra</h6>
                                <small class="text-muted">Kelas 8</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="350">
                    <div class="testimonial-card h-100">
                        <span class="quote-icon"><i class="fa-solid fa-quote-right"></i></span>
                        <div class="mb-3 text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star-half-stroke"></i>
                        </div>
                        <p class="mb-4">"Koleksi referensinya lengkap. Saya sering meminjam buku untuk tugas sekolah dan lomba."</p>
                        <div class="author">
                            <div class="avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Eka Wardani</h6>
                                <small class="text-muted">Kelas 12</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col" data-aos="fade-up" data-aos-delay="400">
                    <div class="testimonial-card h-100">
                        <span class="quote-icon"><i class="fa-solid fa-quote-right"></i></span>
                        <div class="mb-3 text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="mb-4">"Sumber digitalnya lengkap. Saya bisa mengakses jurnal ilmiah kapan saja tanpa khawatir kuota perpustakaan."</p>
                        <div class="author">
                            <div class="avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">James Chen</h6>
                                <small class="text-muted">Kelas 9</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section py-5 scroll-target" data-aos="fade-up" data-aos-delay="100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="zoom-in" data-aos-delay="200">
                    <div class="newsletter-card text-center text-white">
                        <h2 class="fw-bold mb-3">Tetap Terinformasi</h2>
                        <p class="mb-4">Berlangganan buletin kami untuk rekomendasi buku, acara, dan kabar terbaru perpustakaan.</p>
                        <form class="d-flex flex-column flex-sm-row gap-2 justify-content-center" novalidate>
                            <label for="newsletter-email" class="visually-hidden">Email</label>
                            <input id="newsletter-email" type="email" class="form-control" placeholder="Masukkan email Anda" required aria-label="Masukkan email Anda untuk berlangganan">
                            <button type="submit" class="btn btn-light fw-semibold px-4">Berlangganan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Library Info Section -->
    <section id="contact" class="contact-section py-5 bg-light scroll-target" data-aos="fade-up" data-aos-delay="100">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Library Information</h2>
                <p class="text-muted mb-0">Hubungi kami melalui kanal berikut untuk bantuan dan informasi terbaru.</p>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
                <div class="col" data-aos="zoom-in" data-aos-delay="150">
                    <div class="info-card text-center h-100">
                        <i class="fas fa-map-marker-alt text-primary mb-3 fs-3"></i>
                        <h5 class="fw-bold mb-2">Location</h5>
                        <p class="text-muted mb-0">123 Education Street<br>Sekolah Harapan, Kota 12345</p>
                    </div>
                </div>
                <div class="col" data-aos="zoom-in" data-aos-delay="200">
                    <div class="info-card text-center h-100">
                        <i class="fas fa-phone text-primary mb-3 fs-3"></i>
                        <h5 class="fw-bold mb-2">Phone</h5>
                        <p class="text-muted mb-0">(555) 123-4567<br>Sen-Jum: 08.00 - 18.00</p>
                    </div>
                </div>
                <div class="col" data-aos="zoom-in" data-aos-delay="250">
                    <div class="info-card text-center h-100">
                        <i class="fas fa-envelope text-primary mb-3 fs-3"></i>
                        <h5 class="fw-bold mb-2">Email</h5>
                        <p class="text-muted mb-0">info@schoollibrary.edu<br>support@schoollibrary.edu</p>
                    </div>
                </div>
                <div class="col" data-aos="zoom-in" data-aos-delay="300">
                    <div class="info-card text-center h-100">
                        <i class="fas fa-clock text-primary mb-3 fs-3"></i>
                        <h5 class="fw-bold mb-2">Hours</h5>
                        <p class="text-muted mb-0">Sen-Jum: 08.00 - 18.00<br>Sab: 10.00 - 16.00</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <section id="announcements" class="announcements-section py-5 scroll-target" data-aos="fade-up" data-aos-delay="100">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-5">
                <div class="text-center text-lg-start">
                    <h2 class="fw-bold">Pengumuman Terbaru</h2>
                    <p class="text-muted mb-0">Tetap up-to-date dengan informasi penting dari perpustakaan.</p>
                </div>
                <a href="{{ route('landing.pengumuman') }}" class="btn btn-primary">
                    <i class="fa-solid fa-bullhorn me-2"></i> Lihat Semua Pengumuman
                </a>
            </div>

            <div class="row row-cols-1 row-cols-md-2 g-4">
                @forelse ($latestAnnouncements as $index => $announcement)
                    <div class="col" data-aos="fade-up" data-aos-delay="{{ 150 + ($index * 50) }}">
                        <article class="announcement-card p-4 h-100 shadow-sm border-0">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $announcement->kategori->nama ?? 'Umum' }}
                                </span>
                                <small class="text-muted">
                                    <i class="fa-regular fa-calendar me-2"></i>
                                    {{ $announcement->published_at?->translatedFormat('d M Y') ?? '-' }}
                                </small>
                            </div>
                            <h5 class="fw-bold mb-2">
                                <a href="{{ route('landing.pengumuman.detail', $announcement->slug) }}"
                                   class="text-decoration-none text-dark stretched-link">
                                    {{ $announcement->judul }}
                                </a>
                            </h5>
                            <p class="text-muted mb-0">
                                {{ Str::limit(strip_tags($announcement->konten_html), 140) }}
                            </p>
                        </article>
                    </div>
                @empty
                    <div class="col">
                        <div class="announcement-card p-4 text-center">
                            <img src="https://illustrations.popsy.co/gray/searching.svg" alt="" class="mb-3" style="max-width: 180px;">
                            <h5 class="fw-bold">Belum ada pengumuman terbaru</h5>
                            <p class="text-muted mb-0">Saat informasi baru tersedia, Anda akan melihatnya di sini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.AOS) {
                AOS.init({
                    once: true,
                    duration: 800,
                    offset: 120,
                    easing: 'ease-out-cubic'
                });
            }

            const hash = window.location.hash;
            if (hash) {
                const target = document.querySelector(hash);
                if (target) {
                    setTimeout(() => {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 150);
                }
            }

            const eventModalEl = document.getElementById('eventDetailModal');
            if (eventModalEl) {
                eventModalEl.addEventListener('show.bs.modal', event => {
                    const trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }

                    const dayEl = document.getElementById('eventModalDay');
                    const monthEl = document.getElementById('eventModalMonth');
                    const titleEl = document.getElementById('eventModalTitle');
                    const dateEl = document.getElementById('eventModalDate');
                    const timeEl = document.getElementById('eventModalTime');
                    const locationEl = document.getElementById('eventModalLocation');
                    const descriptionEl = document.getElementById('eventModalDescription');
                    const posterWrapper = document.getElementById('eventModalPosterWrapper');
                    const posterEl = document.getElementById('eventModalPoster');

                    dayEl.textContent = trigger.dataset.day || '--';
                    monthEl.textContent = trigger.dataset.month || '---';
                    titleEl.textContent = trigger.dataset.title || 'Detail Acara';
                    dateEl.textContent = trigger.dataset.date || '-';
                    timeEl.textContent = trigger.dataset.time || '-';
                    locationEl.textContent = trigger.dataset.location || '-';
                    descriptionEl.textContent = trigger.dataset.description || 'Tidak ada deskripsi tambahan.';

                    if (trigger.dataset.poster) {
                        posterEl.src = trigger.dataset.poster;
                        posterWrapper.classList.remove('d-none');
                    } else {
                        posterEl.src = '';
                        posterWrapper.classList.add('d-none');
                    }
                });
            }
        });
    </script>
@endpush
