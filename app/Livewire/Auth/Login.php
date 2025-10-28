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
        if (! Auth::check()) { // Jika pengguna belum login, lanjutkan
            return;
        }

        $redirectUrl = $this->resolveDashboardRedirect(Auth::user()); // Ambil URL dashboard berdasarkan role

        if ($redirectUrl) {
            $this->redirect($redirectUrl, navigate: true); // Redirect ke dashboard yang sesuai
            return;
        }

        $this->forceLogoutWithError('Akun Anda tidak memiliki hak akses yang valid.');
    } // Cek saat komponen dimuat, untuk mengalihkan pengguna yang sudah login ke dashboard masing-masing

    #[Layout('components.layouts.auth-layouts')]
    public function render()
    {
        return view('livewire.auth.login');
    }

    public function attemptLogin(): void
    {
        $this->validate(); // Jalankan validasi form sebelum mencoba login

        $credentials = [
            'email_user' => $this->email,
            'password' => $this->password,
        ];

        if (! Auth::attempt($credentials)) { // Coba login dengan kredensial
            $this->addError('credentials', 'Gagal masuk, email atau password salah!');
            return;
        }

        request()->session()->regenerate(); // Regenerasi session untuk keamanan

        $redirectUrl = $this->resolveDashboardRedirect(Auth::user()); // Ambil URL dashboard berdasarkan role

        if ($redirectUrl) {
            $this->redirect($redirectUrl, navigate: true); // Redirect ke dashboard yang sesuai
            return;
        }

        $this->forceLogoutWithError('Anda tidak memiliki hak akses untuk masuk.');
    } // Menangani proses login pengguna ke sistem

    private function resolveDashboardRedirect(User $user): ?string
    {
        $roleName = optional($user->loadMissing('role')->role)->nama_role; // Ambil nama role pengguna

        return match ($roleName) { // Kembalikan route berdasarkan role pengguna
            'Administrator', 'Admin' => route('admin.dashboard'),
            'Guru' => route('guru.dashboard'),
            'Siswa' => route('siswa.dashboard'),
            default => null, // Jika role tidak dikenal, kembalikan null
        };
    } // Ambil dashboard route berdasarkan role pengguna

    private function forceLogoutWithError(string $message): void
    {
        Auth::logout(); // Logout pengguna dari sistem
        request()->session()->invalidate(); // Hapus session
        request()->session()->regenerateToken(); // Regenerasi token CSRF

        $this->addError('credentials', $message); // Tambahkan pesan error ke form
    } // Force logout pengguna dan tambah pesan error
}
