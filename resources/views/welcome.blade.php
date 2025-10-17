<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Sekolah - Pusat Belajar Anda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#home">
                <img src="{{ asset('storage/assets/logo.png') }}" alt="Ruang Membaca" style="height: 32px;">
                <span>Ruang Membaca</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#books">Buku</a></li>
                    <li class="nav-item"><a class="nav-link" href="#events">Acara</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Layanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Kontak</a></li>
                </ul>
                <button class="btn btn-primary ms-lg-3 mt-3 mt-lg-0">Masuk</button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Selamat Datang di Pusat Belajar Anda</h1>
                    <p class="lead mb-4">Jelajahi ribuan buku, sumber belajar, dan program yang dirancang untuk menginspirasi perjalanan belajar Anda.</p>
                    <div class="input-group mb-4" style="max-width: 400px;">
                        <input type="text" class="form-control" placeholder="Cari buku, penulis...">
                        <button class="btn btn-primary" type="button"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                    <button class="btn btn-primary btn-lg me-2">Jelajahi Sekarang</button>
                    <button class="btn btn-outline-primary btn-lg">Pelajari Lebih Lanjut</button>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="hero-image">
                        <img src="{{ asset('storage/assets/img/capybara.png') }}" alt="Ilustrasi pembaca" class="hero-illustration">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="statistics-section py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="stat-card p-4">
                        <h3 class="text-primary fw-bold">50K+</h3>
                        <p class="text-muted">Buku Tersedia</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card p-4">
                        <h3 class="text-primary fw-bold">2K+</h3>
                        <p class="text-muted">Anggota Aktif</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card p-4">
                        <h3 class="text-primary fw-bold">100+</h3>
                        <p class="text-muted">Acara Tahunan</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card p-4">
                        <h3 class="text-primary fw-bold">24/7</h3>
                        <p class="text-muted">Akses Digital</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Buku Unggulan Section -->
    <section id="books" class="featured-books py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Buku Unggulan</h2>
            <div class="row g-4">
                <div class="col-md-4 mb-4">
                    <div class="book-card">
                        <div class="book-image bg-gradient-1 d-flex align-items-center justify-content-center">
                            <img src="{{ asset('storage/assets/img/capybara.png') }}" alt="Sampul Petualangan Dimulai" class="book-thumbnail">
                        </div>
                        <div class="p-3">
                            <h5 class="fw-bold">Petualangan Dimulai</h5>
                            <p class="text-muted small">Oleh Sarah Johnson</p>
                            <div class="mb-3">
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star-half-stroke text-warning"></i>
                            </div>
                            <button class="btn btn-primary w-100 btn-sm">Pinjam Sekarang</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="book-card">
                        <div class="book-image bg-gradient-2 d-flex align-items-center justify-content-center">
                            <img src="{{ asset('storage/assets/img/capybara.png') }}" alt="Sampul Keajaiban Sains" class="book-thumbnail">
                        </div>
                        <div class="p-3">
                            <h5 class="fw-bold">Keajaiban Sains</h5>
                            <p class="text-muted small">Oleh Dr. Michael Chen</p>
                            <div class="mb-3">
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                            </div>
                            <button class="btn btn-primary w-100 btn-sm">Pinjam Sekarang</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="book-card">
                        <div class="book-image bg-gradient-3 d-flex align-items-center justify-content-center">
                            <img src="{{ asset('storage/assets/img/capybara.png') }}" alt="Sampul Kronik Sejarah" class="book-thumbnail">
                        </div>
                        <div class="p-3">
                            <h5 class="fw-bold">Kronik Sejarah</h5>
                            <p class="text-muted small">Oleh Emma Williams</p>
                            <div class="mb-3">
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star text-warning"></i>
                                <i class="fa-solid fa-star-half-stroke text-warning"></i>
                            </div>
                            <button class="btn btn-primary w-100 btn-sm">Pinjam Sekarang</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Layanan Kami</h2>
            <div class="row g-4">
                <div class="col-md-3 mb-4">
                    <div class="service-card text-center p-4">
                        <i class="fa-solid fa-book-open text-primary mb-3" style="font-size: 40px;"></i>
                        <h5 class="fw-bold">Koleksi Luas</h5>
                        <p class="text-muted">Akses ke 50.000+ buku dari berbagai genre dan mata pelajaran</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="service-card text-center p-4">
                        <i class="fa-solid fa-users text-primary mb-3" style="font-size: 40px;"></i>
                        <h5 class="fw-bold">Kelompok Belajar</h5>
                        <p class="text-muted">Ruang belajar kolaboratif untuk kerja kelompok</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="service-card text-center p-4">
                        <i class="fa-solid fa-laptop text-primary mb-3" style="font-size: 40px;"></i>
                        <h5 class="fw-bold">Sumber Digital</h5>
                        <p class="text-muted">E-book, jurnal, dan basis data daring tersedia 24/7</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="service-card text-center p-4">
                        <i class="fa-solid fa-star text-primary mb-3" style="font-size: 40px;"></i>
                        <h5 class="fw-bold">Program Membaca</h5>
                        <p class="text-muted">Program interaktif untuk meningkatkan kebiasaan membaca</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="events-section py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Acara Mendatang</h2>
            <div class="row g-4">
                <div class="col-md-6 mb-4">
                    <div class="event-card p-4">
                        <div class="row">
                            <div class="col-auto">
                                <div class="event-date bg-primary text-white p-3 rounded text-center">
                                    <div class="fw-bold" style="font-size: 24px;">15</div>
                                    <div class="small">Nov</div>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="fw-bold">Pertemuan Klub Buku</h5>
                                <p class="text-muted mb-2"><i class="fa-regular fa-clock me-2"></i>14.00 - 15.30</p>
                                <p class="text-muted"><i class="fa-solid fa-location-dot me-2"></i>Aula Perpustakaan</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="event-card p-4">
                        <div class="row">
                            <div class="col-auto">
                                <div class="event-date bg-primary text-white p-3 rounded text-center">
                                    <div class="fw-bold" style="font-size: 24px;">22</div>
                                    <div class="small">Nov</div>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="fw-bold">Jumpa Penulis</h5>
                                <p class="text-muted mb-2"><i class="fa-regular fa-clock me-2"></i>15.00 - 17.00</p>
                                <p class="text-muted"><i class="fa-solid fa-location-dot me-2"></i>Aula Utama</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="event-card p-4">
                        <div class="row">
                            <div class="col-auto">
                                <div class="event-date bg-primary text-white p-3 rounded text-center">
                                    <div class="fw-bold" style="font-size: 24px;">29</div>
                                    <div class="small">Nov</div>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="fw-bold">Peluncuran Tantangan Membaca</h5>
                                <p class="text-muted mb-2"><i class="fa-regular fa-clock me-2"></i>13.00 - 14.00</p>
                                <p class="text-muted"><i class="fa-solid fa-location-dot me-2"></i>Aula Perpustakaan</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="event-card p-4">
                        <div class="row">
                            <div class="col-auto">
                                <div class="event-date bg-primary text-white p-3 rounded text-center">
                                    <div class="fw-bold" style="font-size: 24px;">05</div>
                                    <div class="small">Des</div>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="fw-bold">Orientasi Perpustakaan</h5>
                                <p class="text-muted mb-2"><i class="fa-regular fa-clock me-2"></i>10.00 - 11.00</p>
                                <p class="text-muted"><i class="fa-solid fa-location-dot me-2"></i>Ruang Rapat</p>
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
            <h2 class="text-center fw-bold mb-5">Kata Para Siswa</h2>
            <div class="row g-4">
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4">
                        <div class="mb-3">
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"Perpustakaan sangat membantu keberhasilan akademik saya. Stafnya ramah dan koleksinya luar biasa!"</p>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Alex Johnson</h6>
                                <small class="text-muted">Kelas 10</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4">
                        <div class="mb-3">
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                        </div>
                        <p class="mb-4">"Saya menyukai program membaca dan pertemuan klub buku. Ini cara seru untuk bertemu pencinta buku lain!"</p>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Maria Garcia</h6>
                                <small class="text-muted">Kelas 11</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4">
                        <div class="mb-3">
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star text-warning"></i>
                            <i class="fa-solid fa-star-half-stroke text-warning"></i>
                        </div>
                        <p class="mb-4">"Sumber digitalnya luar biasa. Saya bisa mengakses jurnal dan e-book kapan pun dan di mana pun. Sangat direkomendasikan!"</p>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">James Chen</h6>
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
                <div class="col-lg-6 text-center">
                    <h2 class="fw-bold mb-3">Tetap Terinformasi</h2>
                    <p class="text-muted mb-4">Berlangganan buletin kami untuk rekomendasi buku, acara, dan kabar terbaru perpustakaan.</p>
                    <form class="d-flex flex-column flex-sm-row gap-2">
                        <input type="email" class="form-control" placeholder="Masukkan email Anda" required>
                        <button type="submit" class="btn btn-primary">Berlangganan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

   <!-- Library Info Section -->
    <section id="contact" class="library-info py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Library Information</h2>
            <div class="row g-4">
                <div class="col-md-3 text-center">
                    <div class="info-card p-4">
                        <i class="fas fa-map-marker-alt text-primary mb-3" style="font-size: 32px;"></i>
                        <h5 class="fw-bold">Location</h5>
                        <p class="text-muted">123 Education Street<br>School Campus, City 12345</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="info-card p-4">
                        <i class="fas fa-phone text-primary mb-3" style="font-size: 32px;"></i>
                        <h5 class="fw-bold">Phone</h5>
                        <p class="text-muted">(555) 123-4567<br>Mon-Fri: 8AM-6PM</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="info-card p-4">
                        <i class="fas fa-envelope text-primary mb-3" style="font-size: 32px;"></i>
                        <h5 class="fw-bold">Email</h5>
                        <p class="text-muted">info@schoollibrary.edu<br>support@schoollibrary.edu</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="info-card p-4">
                        <i class="fas fa-clock text-primary mb-3" style="font-size: 32px;"></i>
                        <h5 class="fw-bold">Hours</h5>
                        <p class="text-muted">Mon-Fri: 8AM-6PM<br>Sat: 10AM-4PM</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <section class="announcements-section py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Pengumuman Terbaru</h2>
            <div class="row g-4">
                <div class="col-md-6 mb-4">
                    <div class="announcement-card p-4">
                        <span class="badge bg-primary mb-3">Baru</span>
                        <h5 class="fw-bold">Basis Data Digital Baru Tersedia</h5>
                        <p class="text-muted small mb-2"><i class="fa-regular fa-calendar me-2"></i>November 10, 2024</p>
                        <p>Kami dengan senang hati mengumumkan akses ke basis data jurnal akademik baru dengan lebih dari 10.000 artikel teruji sejawat.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="announcement-card p-4">
                        <span class="badge bg-success mb-3">Pembaruan</span>
                        <h5 class="fw-bold">Jam Operasional Perpustakaan Diperpanjang</h5>
                        <p class="text-muted small mb-2"><i class="fa-regular fa-calendar me-2"></i>November 8, 2024</p>
                        <p>Mulai pekan depan, perpustakaan buka hingga pukul 19.00 pada hari kerja untuk melayani lebih banyak siswa.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="announcement-card p-4">
                        <span class="badge bg-info mb-3">Acara</span>
                        <h5 class="fw-bold">Donasi Buku Bersama</h5>
                        <p class="text-muted small mb-2"><i class="fa-regular fa-calendar me-2"></i>November 5, 2024</p>
                        <p>Bantu kami menambah koleksi! Donasikan buku bekas layak baca Anda dan dapatkan pembatas buku spesial.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="announcement-card p-4">
                        <span class="badge bg-warning mb-3">Pengingat</span>
                        <h5 class="fw-bold">Batas Pengembalian Buku</h5>
                        <p class="text-muted small mb-2"><i class="fa-regular fa-calendar me-2"></i>November 3, 2024</p>
                        <p>Jangan lupa mengembalikan semua buku yang dipinjam sebelum 15 November agar terhindar dari denda.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer text-white py-5">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <h5 class="fw-bold mb-3">Perpustakaan Sekolah</h5>
                    <p class="text-muted">Gerbang Anda menuju pengetahuan dan prestasi belajar.</p>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold mb-3">Tautan Cepat</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Jelajahi Buku</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Akun Saya</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Reservasi</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold mb-3">Sumber Daya</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Perpustakaan Digital</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Panduan Riset</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Basis Data</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">E-Book</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold mb-3">Ikuti Kami</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="text-muted"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" class="text-muted"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="text-muted"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center text-muted">
                <p>&copy; 2025 Perpustakaan Sekolah. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
