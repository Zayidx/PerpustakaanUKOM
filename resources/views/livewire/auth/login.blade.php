<div id="auth-left">
    <a href="#" class="mb-4 logo">
        <img src="{{ asset('assets/logo.png') }}" width="50" alt=""> Ruang Belajar
    </a>
    
    <h1 class="mt-5 auth-title">Selamat datang di Ruang Belajar</h1>
    <p class="mb-5 auth-subtitle pe-5">
        Login menggunakan email & password yang benar 👋
    </p>
    
    
    <form wire:submit.prevent='attemptLogin' novalidate>
        @if ($errors->has('credentials'))
            <div class="mb-3 alert alert-danger">{{ $errors->first('credentials') }}</div>
        @endif

        <div class="mb-4 form-group position-relative has-icon-left">
            <input required type="email" wire:model.defer='email' autocomplete="email"
                class="form-control form-control-xl @error('email') is-invalid @enderror" placeholder="Email">
            <div class="form-control-icon"><i class="bi bi-person"></i></div>
            @error('email')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
        </div>

        <div x-data="{ show: false }" class="mb-3 form-group position-relative has-icon-left">
            <input required :type="show ? 'text' : 'password'" autocomplete="current-password"
                class="form-control form-control-xl @error('password') is-invalid @enderror" placeholder="Password"
                wire:model.defer='password'>
            <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
            <div class="form-control-icon" style="left:auto; right:0; cursor: pointer;" @click="show = !show">
                <i :class="!show ? 'bi-eye-slash' : 'bi-eye'"></i>
            </div>
             @error('password')<div class="invalid-feedback"><i class="bx bx-radio-circle"></i> {{ $message }}</div>@enderror
        </div>

        <button type="submit" class="mt-3 shadow-lg btn btn-primary btn-block btn-lg">
            Log in
        </button>
        
        
    </form>
</div>
