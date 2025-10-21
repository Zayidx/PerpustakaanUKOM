<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $roleName = optional($user->loadMissing('role')->role)->nama_role;
        $allowedRoles = collect($roles)
            ->filter()
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->all();

        if ($roleName === null || ! in_array($roleName, $allowedRoles, true)) {
            if ($redirect = $this->resolveDashboardRedirect($user)) {
                return redirect()
                    ->to($redirect)
                    ->with('error', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
        }

        return $next($request);
    }

    private function resolveDashboardRedirect(User $user): ?string
    {
        $roleName = optional($user->role)->nama_role;

        return match ($roleName) {
            'Administrator', 'Petugas' => route('admin.dashboard'),
            'Guru' => route('guru.dashboard'),
            'Siswa' => route('siswa.dashboard'),
            default => null,
        };
    }
}
