    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-2 auto-hide-navbar">
        <div class="container px-3 px-lg-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/#home">
                <img src="{{ asset('assets/logo.png') }}" alt="Ruang Membaca" style="height: 5rem;"  loading="lazy" decoding="async">

            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="{{ route('welcome') }}#home">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('welcome') }}#books">Buku</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('welcome') }}#events">Acara</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('welcome') }}#services">Layanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('welcome') }}#contact">Kontak</a></li>
                </ul>
                @php
                    $dashboardRoute = null;
                    if (auth()->check()) {
                        $roleName = auth()->user()->role?->nama_role;
                        $dashboardRoute = match ($roleName) {
                            'Administrator' => route('admin.dashboard'),
                            'Guru' => route('guru.dashboard'),
                            'Siswa' => route('siswa.dashboard'),
                            default => null,
                        };
                    }
                @endphp
                <a class="btn btn-primary ms-lg-3 mt-3 mt-lg-0 d-inline-flex align-items-center justify-content-center px-4"
                    href="{{ $dashboardRoute ?? route('login') }}">
                    {{ $dashboardRoute ? 'Dashboard' : 'Masuk' }}
                </a>
            </div>
        </div>
    </nav>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const navbar = document.querySelector('.auto-hide-navbar');
                if (!navbar) {
                    return;
                }

                const navCollapse = document.getElementById('navbarNav');
                let lastScrollY = window.pageYOffset || document.documentElement.scrollTop;
                let menuOpen = false;

                const showNavbar = () => {
                    navbar.classList.remove('is-hidden');
                };

                const hideNavbar = () => {
                    if (!menuOpen) {
                        navbar.classList.add('is-hidden');
                    }
                };

                const handleScroll = () => {
                    const currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
                    const isAtTop = currentScrollY <= 0;

                    if (isAtTop || currentScrollY < lastScrollY) {
                        showNavbar();
                    } else if (currentScrollY > lastScrollY) {
                        hideNavbar();
                    }

                    lastScrollY = currentScrollY;
                };

                window.addEventListener('scroll', handleScroll, { passive: true });

                if (navCollapse) {
                    navCollapse.addEventListener('shown.bs.collapse', () => {
                        menuOpen = true;
                        showNavbar();
                    });

                    navCollapse.addEventListener('hidden.bs.collapse', () => {
                        menuOpen = false;
                        handleScroll();
                    });
                }

                handleScroll();
            });
        </script>
    @endpush
