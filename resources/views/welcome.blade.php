<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Perpustakaan sekolah dengan koleksi buku unggulan, program belajar, dan layanan digital terbaik untuk siswa.">
    <title>Perpustakaan Sekolah - Pusat Belajar Anda</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-2">
        <div class="container px-3 px-lg-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#home">
                <img src="{{ asset('assets/logo.png') }}" alt="Ruang Membaca" style="height: 32px;" width="32" height="32" loading="lazy" decoding="async">
                <span>Ruang Membaca</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#books">Buku</a></li>
                    <li class="nav-item"><a class="nav-link" href="#events">Acara</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Layanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Kontak</a></li>
                </ul>
                <a class="btn btn-primary ms-lg-3 mt-3 mt-lg-0 d-inline-flex align-items-center justify-content-center px-4" href="#">Masuk</a>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        <!-- Hero Section -->
        <section id="home" class="hero-section py-5 overflow-hidden">
            <div class="container">
                <div class="row align-items-center gy-5">
                    <div class="col-12 col-lg-6 text-center text-lg-start">
                        <h1 class="display-4 fw-bold mb-4">Selamat Datang di Pusat Belajar Anda</h1>
                        <p class="lead mb-4">Jelajahi ribuan buku, sumber belajar, dan program yang dirancang untuk menginspirasi perjalanan belajar Anda.</p>
                        <form class="input-group input-group-lg mb-4 mx-auto mx-lg-0 shadow-sm" style="max-width: 420px;" role="search">
                            <label for="search-books" class="visually-hidden">Cari buku atau penulis</label>
                            <input id="search-books" type="search" class="form-control" placeholder="Cari buku, penulis..." aria-label="Cari buku atau penulis">
                            <button class="btn btn-primary" type="submit" aria-label="Cari">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </form>
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center justify-content-lg-start">
                            <a href="#books" class="btn btn-primary btn-lg px-4">Jelajahi Sekarang</a>
                            <a href="#services" class="btn btn-outline-primary btn-lg px-4">Pelajari Lebih Lanjut</a>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 text-center">
                        <div class="hero-image position-relative mx-auto">
                            <img src="{{ asset('assets/img/capybara.png') }}" alt="Ilustrasi pembaca" class="hero-illustration img-fluid" decoding="async" fetchpriority="high" width="480" height="480">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="statistics-section py-5 bg-light">
            <div class="container">
                <div class="row row-cols-2 row-cols-md-4 g-4 text-center justify-content-center">
                    <div class="col">
                        <div class="stat-card p-4 h-100">
                            <h3 class="text-primary fw-bold mb-2">50K+</h3>
                            <p class="text-muted mb-0">Buku Tersedia</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card p-4 h-100">
                            <h3 class="text-primary fw-bold mb-2">2K+</h3>
                            <p class="text-muted mb-0">Anggota Aktif</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card p-4 h-100">
                            <h3 class="text-primary fw-bold mb-2">100+</h3>
                            <p class="text-muted mb-0">Acara Tahunan</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card p-4 h-100">
                            <h3 class="text-primary fw-bold mb-2">24/7</h3>
                            <p class="text-muted mb-0">Akses Digital</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Buku Unggulan Section -->
        <section id="books" class="featured-books py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Buku Unggulan</h2>
                    <p class="text-muted mb-0">Pilihan terbaik untuk menemani perjalanan belajar Anda.</p>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                    <div class="col">
                        <div class="book-card h-100 d-flex flex-column">
                            <div class="book-image bg-gradient-1">
                                <img src="{{ asset('assets/img/capybara.png') }}" alt="Sampul Misteri Senja" class="book-thumbnail" loading="lazy" decoding="async" width="320" height="420">
                            </div>
                            <div class="p-3 d-flex flex-column gap-2 flex-grow-1">
                                <div>
                                    <h5 class="fw-bold mb-1">Misteri Senja</h5>
                                    <p class="text-muted small mb-2">Oleh Sarah Johnson</p>
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
                    <div class="col">
                        <div class="book-card h-100 d-flex flex-column">
                            <div class="book-image bg-gradient-2">
                                <img src="{{ asset('assets/img/capybara.png') }}" alt="Sampul Petualangan Pengetahuan" class="book-thumbnail" loading="lazy" decoding="async" width="320" height="420">
                            </div>
                            <div class="p-3 d-flex flex-column gap-2 flex-grow-1">
                                <div>
                                    <h5 class="fw-bold mb-1">Petualangan Pengetahuan</h5>
                                    <p class="text-muted small mb-2">Oleh Daniel Smith</p>
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
                    <div class="col">
                        <div class="book-card h-100 d-flex flex-column">
                            <div class="book-image bg-gradient-3">
                                <img src="{{ asset('assets/img/capybara.png') }}" alt="Sampul Kronik Sejarah" class="book-thumbnail" loading="lazy" decoding="async" width="320" height="420">
                            </div>
                            <div class="p-3 d-flex flex-column gap-2 flex-grow-1">
                                <div>
                                    <h5 class="fw-bold mb-1">Kronik Sejarah</h5>
                                    <p class="text-muted small mb-2">Oleh Emma Williams</p>
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
        <section id="services" class="services-section py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Layanan Kami</h2>
                    <p class="text-muted mb-0">Fasilitas lengkap untuk mendukung pengalaman belajar terbaik.</p>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4">
                    <div class="col">
                        <div class="service-card text-center p-4 h-100">
                            <div class="service-icon">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Koleksi Luas</h5>
                            <p class="text-muted mb-0">Akses ke 50.000+ buku dari berbagai genre dan mata pelajaran.</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="service-card text-center p-4 h-100">
                            <div class="service-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Kelompok Belajar</h5>
                            <p class="text-muted mb-0">Ruang belajar kolaboratif untuk kerja kelompok.</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="service-card text-center p-4 h-100">
                            <div class="service-icon">
                                <i class="fa-solid fa-laptop"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Sumber Digital</h5>
                            <p class="text-muted mb-0">E-book, jurnal, dan basis data daring tersedia 24/7.</p>
                        </div>
                    </div>
                    <div class="col">
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
        <section id="events" class="events-section py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Acara Mendatang</h2>
                    <p class="text-muted mb-0">Ikuti kegiatan terbaru dan perluas jejaring belajar Anda.</p>
                </div>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                        <div class="event-card p-4 h-100">
                            <div class="d-flex gap-3">
                                <div class="event-date rounded-3 text-center px-3 py-2">
                                    <div class="fw-bold fs-4">15</div>
                                    <div class="small">Nov</div>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-2">Pertemuan Klub Buku</h5>
                                    <p class="text-muted mb-1"><i class="fa-regular fa-clock me-2"></i>14.00 - 15.30</p>
                                    <p class="text-muted mb-0"><i class="fa-solid fa-location-dot me-2"></i>Aula Perpustakaan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="event-card p-4 h-100">
                            <div class="d-flex gap-3">
                                <div class="event-date rounded-3 text-center px-3 py-2">
                                    <div class="fw-bold fs-4">22</div>
                                    <div class="small">Nov</div>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-2">Jumpa Penulis</h5>
                                    <p class="text-muted mb-1"><i class="fa-regular fa-clock me-2"></i>15.00 - 17.00</p>
                                    <p class="text-muted mb-0"><i class="fa-solid fa-location-dot me-2"></i>Aula Utama</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="event-card p-4 h-100">
                            <div class="d-flex gap-3">
                                <div class="event-date rounded-3 text-center px-3 py-2">
                                    <div class="fw-bold fs-4">29</div>
                                    <div class="small">Nov</div>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-2">Peluncuran Tantangan Membaca</h5>
                                    <p class="text-muted mb-1"><i class="fa-regular fa-clock me-2"></i>13.00 - 14.00</p>
                                    <p class="text-muted mb-0"><i class="fa-solid fa-location-dot me-2"></i>Aula Perpustakaan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="event-card p-4 h-100">
                            <div class="d-flex gap-3">
                                <div class="event-date rounded-3 text-center px-3 py-2">
                                    <div class="fw-bold fs-4">05</div>
                                    <div class="small">Des</div>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-2">Orientasi Perpustakaan</h5>
                                    <p class="text-muted mb-1"><i class="fa-regular fa-clock me-2"></i>10.00 - 11.00</p>
                                    <p class="text-muted mb-0"><i class="fa-solid fa-location-dot me-2"></i>Ruang Rapat</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Kata Para Siswa</h2>
                    <p class="text-muted mb-0">Pengalaman nyata dari para pengunjung Ruang Membaca.</p>
                </div>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <div class="col">
                        <div class="testimonial-card h-100">
                            <span class="quote-icon"><i class="fa-solid fa-quote-right"></i></span>
                            <div class="mb-3 text-warning">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <p class="mb-4">"Perpustakaan sangat membantu keberhasilan akademik saya. Stafnya ramah dan koleksinya luar biasa!"</p>
                            <div class="author">
                                <div class="avatar">
                                    <i class="fa-solid fa-user-graduate"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Alicia Putri</h6>
                                    <small class="text-muted">Kelas 12</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="testimonial-card h-100">
                            <span class="quote-icon"><i class="fa-solid fa-quote-right"></i></span>
                            <div class="mb-3 text-warning">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star-half-stroke"></i>
                            </div>
                            <p class="mb-4">"Program membaca mingguan membuat saya semakin disiplin belajar. Ada banyak buku baru setiap minggu!"</p>
                            <div class="author">
                                <div class="avatar">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Budi Santoso</h6>
                                    <small class="text-muted">Kelas 10</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
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
        <section class="newsletter-section py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
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
        <section id="contact" class="contact-section py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Library Information</h2>
                    <p class="text-muted mb-0">Hubungi kami melalui kanal berikut untuk bantuan dan informasi terbaru.</p>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
                    <div class="col">
                        <div class="info-card text-center h-100">
                            <i class="fas fa-map-marker-alt text-primary mb-3 fs-3"></i>
                            <h5 class="fw-bold mb-2">Location</h5>
                            <p class="text-muted mb-0">123 Education Street<br>Sekolah Harapan, Kota 12345</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="info-card text-center h-100">
                            <i class="fas fa-phone text-primary mb-3 fs-3"></i>
                            <h5 class="fw-bold mb-2">Phone</h5>
                            <p class="text-muted mb-0">(555) 123-4567<br>Sen-Jum: 08.00 - 18.00</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="info-card text-center h-100">
                            <i class="fas fa-envelope text-primary mb-3 fs-3"></i>
                            <h5 class="fw-bold mb-2">Email</h5>
                            <p class="text-muted mb-0">info@schoollibrary.edu<br>support@schoollibrary.edu</p>
                        </div>
                    </div>
                    <div class="col">
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
        <section class="announcements-section py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Pengumuman Terbaru</h2>
                    <p class="text-muted mb-0">Tetap up-to-date dengan informasi penting dari perpustakaan.</p>
                </div>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                        <article class="announcement-card p-4 h-100">
                            <span class="badge bg-primary mb-3">Baru</span>
                            <h5 class="fw-bold">Basis Data Digital Baru Tersedia</h5>
                            <p class="text-muted small mb-2"><i class="fa-regular fa-calendar me-2"></i>10 November 2024</p>
                            <p class="mb-0">Kami dengan senang hati mengumumkan akses ke basis data jurnal akademik baru dengan lebih dari 10.000 artikel teruji sejawat.</p>
                        </article>
                    </div>
                    <div class="col">
                        <article class="announcement-card p-4 h-100">
                            <span class="badge bg-success mb-3">Pembaruan</span>
                            <h5 class="fw-bold">Jam Operasional Perpustakaan Diperpanjang</h5>
                            <p class="text-muted small mb-2"><i class="fa-regular fa-calendar me-2"></i>8 November 2024</p>
                            <p class="mb-0">Mulai pekan depan, perpustakaan buka hingga pukul 19.00 pada hari kerja untuk melayani lebih banyak siswa.</p>
                        </article>
                    </div>
                    <div class="col">
                        <article class="announcement-card p-4 h-100">
                            <span class="badge bg-info mb-3 text-dark">Acara</span>
                            <h5 class="fw-bold">Donasi Buku Bersama</h5>
                            <p class="text-muted small mb-2"><i class="fa-regular fa-calendar me-2"></i>5 November 2024</p>
                            <p class="mb-0">Bantu kami menambah koleksi! Donasikan buku bekas layak baca Anda dan dapatkan pembatas buku spesial.</p>
                        </article>
                    </div>
                    <div class="col">
                        <article class="announcement-card p-4 h-100">
                            <span class="badge bg-warning text-dark mb-3">Pengingat</span>
                            <h5 class="fw-bold">Batas Pengembalian Buku</h5>
                            <p class="text-muted small mb-2"><i class="fa-regular fa-calendar me-2"></i>3 November 2024</p>
                            <p class="mb-0">Jangan lupa mengembalikan semua buku yang dipinjam sebelum 15 November agar terhindar dari denda.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer text-white py-5">
        <div class="container">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-4">
                <div class="col">
                    <h5 class="fw-bold mb-3">Perpustakaan Sekolah</h5>
                    <p class="text-muted mb-0">Gerbang Anda menuju pengetahuan dan prestasi belajar.</p>
                </div>
                <div class="col">
                    <h6 class="fw-bold mb-3">Tautan Cepat</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="#books" class="text-muted text-decoration-none">Jelajahi Buku</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Akun Saya</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Reservasi</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col">
                    <h6 class="fw-bold mb-3">Sumber Daya</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="#" class="text-muted text-decoration-none">Perpustakaan Digital</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Panduan Riset</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Basis Data</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">E-Book</a></li>
                    </ul>
                </div>
                <div class="col">
                    <h6 class="fw-bold mb-3">Ikuti Kami</h6>
                    <div class="social-links d-flex gap-3">
                        <a href="#" class="text-muted"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="text-muted"><i class="fa-brands fa-youtube"></i></a>
                        <a href="#" class="text-muted"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="text-muted"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-secondary opacity-50">
            <div class="text-center text-muted">
                <p class="mb-0">&copy; 2025 Perpustakaan Sekolah. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
