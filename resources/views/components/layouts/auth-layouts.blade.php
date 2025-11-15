<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Form Register' }}</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('assets/logo.png') }}" type="image/png">
    <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('assets/compiled/css/auth.css') }}">
    <style>
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
           
        }
        body {
            background-color: var(--bs-body-bg, #f8fafc);
            color: var(--bs-body-color, #333);
        }
        #auth-right {
            background: linear-gradient(90deg,#2d499d,#3f5491);
        }
        html[data-bs-theme=dark] body {
            background-color: var(--bs-body-bg, #161B22);
            color: var(--bs-body-color, #c9d1d9);
        }
        html[data-bs-theme=dark] #auth-right {
            background: linear-gradient(90deg,#232946,#161B22);
        }
    </style>
</head>
<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    <script>
        document.addEventListener('livewire:navigated', function() {
            if (typeof initTheme === 'function') {
                initTheme();
            }
        });
    </script>
    <div id="auth">

        <div class="row h-100">
            <div class="col-lg-5 col-12">
                {{ $slot }}
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">

                </div>
            </div>
        </div>

    </div>
</body>

</html>
