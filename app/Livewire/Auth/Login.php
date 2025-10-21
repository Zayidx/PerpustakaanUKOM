<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Halaman Login')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public function mount(): void
    {
        if (! Auth::check()) {
            return;
        }

        $redirectUrl = $this->resolveDashboardRedirect(Auth::user());

        if ($redirectUrl) {
            $this->redirect($redirectUrl, navigate: true);
            return;
        }

        $this->forceLogoutWithError('Akun Anda tidak memiliki hak akses yang valid.');
    }

    #[Layout('components.layouts.auth-layouts')]
    public function render()
    {
        return view('livewire.auth.login');
    }

    public function attemptLogin(): void
    {
        $this->validate();

        $credentials = [
            'email_user' => $this->email,
            'password' => $this->password,
        ];

        if (! Auth::attempt($credentials)) {
            $this->addError('credentials', 'Gagal masuk, email atau password salah!');
            return;
        }

        request()->session()->regenerate();

        $redirectUrl = $this->resolveDashboardRedirect(Auth::user());

        if ($redirectUrl) {
            $this->redirect($redirectUrl, navigate: true);
            return;
        }

        $this->forceLogoutWithError('Anda tidak memiliki hak akses untuk masuk.');
    }

    private function resolveDashboardRedirect(User $user): ?string
    {
        $roleName = optional($user->loadMissing('role')->role)->nama_role;

        return match ($roleName) {
            'Administrator', 'Petugas' => route('admin.dashboard'),
            'Guru' => route('guru.dashboard'),
            'Siswa' => route('siswa.dashboard'),
            default => null,
        };
    }

    private function forceLogoutWithError(string $message): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->addError('credentials', $message);
    }
}
