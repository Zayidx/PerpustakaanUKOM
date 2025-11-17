<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user instanceof User) {
            $redirect = $this->resolveDashboardRedirect($user);

            if ($redirect) {
                return redirect()->to($redirect);
            }
        }

        return $next($request);
    }

    private function resolveDashboardRedirect(User $user): ?string
    {
        $roleName = optional($user->loadMissing('role')->role)->nama_role;

        return match ($roleName) {
            'SuperAdmin' => route('superadmin.dashboard'),
            'AdminPerpus' => route('adminperpus.dashboard'),
            'Siswa' => route('siswa.dashboard'),
            default => null,
        };
    }
}
