<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mazer Admin Dashboard</title>

    <link rel="icon" href="{{ asset('assets/logo.png') }}" type="image/png">


    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">
    @stack('styles')
</head>

<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}" data-navigate-once></script>
    <div id="app">
        @if (Request::routeIs('superadmin.*'))
            @include('components.layouts.partials.sidebar-super-admin-dashboard')
        @elseif (Request::routeIs('siswa.*'))
            @include('components.layouts.partials.sidebar-siswa-dashboard')
        @elseif (Request::routeIs('adminperpus.*'))
            @include('components.layouts.partials.sidebar-admin-perpus-dashboard')
        @endif
       
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <h3>{{ $title }}</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 ">
                        {{ $slot }}
                    </div>
                </section>
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>2023 &copy; Mazer</p>
                    </div>
                    <div class="float-end">
                        <p>Crafted with <span class="text-danger"><i class="bi bi-heart-fill icon-mid"></i></span>
                            by <a href="/">Tim Ukom</a></p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="{{ asset('assets/static/js/components/dark.js') }}" data-navigate-once></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}" data-navigate-once></script>


    <script src="{{ asset('assets/compiled/js/app.js') }}" data-navigate-once></script>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" data-navigate-once></script>

    <!-- Need: Apexcharts -->
    @if (Request::routeIs('*.dashboard'))
        <script src="{{ asset('assets/extensions/apexcharts/apexcharts.min.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/static/js/pages/dashboard.js') }}" data-navigate-once></script>
    @endif
    @stack('scripts')

    <script data-navigate-once>
        const applyStoredTheme = () => {
            const stored = localStorage.getItem('theme');
            if (!stored) {
                return;
            }

            if (typeof window.setTheme === 'function') {
                window.setTheme(stored, false);
            } else {
                document.body.classList.remove('light', 'dark');
                document.body.classList.add(stored);
                document.documentElement.setAttribute('data-bs-theme', stored);
            }
        };

        const bindThemeToggle = () => {
            applyStoredTheme();

            const toggler = document.getElementById('toggle-dark');
            if (!toggler) {
                return;
            }

            toggler.checked = localStorage.getItem('theme') === 'dark';

            if (!toggler.dataset.themeBound) {
                toggler.addEventListener('input', (event) => {
                    const desired = event.target.checked ? 'dark' : 'light';

                    if (typeof window.setTheme === 'function') {
                        window.setTheme(desired, true);
                    } else {
                        document.body.classList.remove('light', 'dark');
                        document.body.classList.add(desired);
                        document.documentElement.setAttribute('data-bs-theme', desired);
                        localStorage.setItem('theme', desired);
                    }
                });

                toggler.dataset.themeBound = 'true';
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            bindThemeToggle();
            initAlerts();
        });
        document.addEventListener('livewire:navigated', () => {
            bindThemeToggle();
            initAlerts();
        });

        function initAlerts() {
            const showAlert = ({ message, type = 'success' }) => {
                if (!window.Swal) {
                    return;
                }

                window.Swal.fire({
                    icon: type,
                    title: type === 'error' ? 'Terjadi Kesalahan' : 'Berhasil',
                    text: message,
                    timer: 2500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
            };

            if (window.Livewire) {
                window.Livewire.on('notify', (payload) => showAlert(payload));
            }

            const flashMessage = @json(session('message'));
            const flashError = @json(session('error'));

            if (flashMessage) {
                showAlert({ message: flashMessage, type: 'success' });
            }

            if (flashError) {
                showAlert({ message: flashError, type: 'error' });
            }
        }
    </script>

</body>

</html>
