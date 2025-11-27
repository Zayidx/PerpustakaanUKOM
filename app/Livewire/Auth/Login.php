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
        // Jika sudah login, arahkan ke dashboard sesuai peran.
        if (! Auth::check()) { 
            return;
        }

        $redirectUrl = $this->resolveDashboardRedirect(Auth::user()); 

        if ($redirectUrl) {
            $this->redirect($redirectUrl); 
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

        // Mapping ke kolom email_user yang digunakan tabel users.
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
            $this->redirect($redirectUrl); 
            return;
        }

        $this->forceLogoutWithError('Anda tidak memiliki hak akses untuk masuk.');
    } 

    private function resolveDashboardRedirect(User $user): ?string
    {
        // Cari nama role untuk menentukan tujuan dashboard.
        $roleName = optional($user->loadMissing('role')->role)->nama_role; 

        return match ($roleName) { 
            'SuperAdmin' => route('superadmin.dashboard'),
            'AdminPerpus' => route('adminperpus.dashboard'),
            'Siswa' => route('siswa.dashboard'),
            default => null, 
        };
    } 

    private function forceLogoutWithError(string $message): void
    {
        // Paksa logout dan tampilkan pesan error umum.
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->addError('credentials', $message); 
    } 
}
