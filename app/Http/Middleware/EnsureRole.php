<?php

namespace App\Http\Middleware;

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
            return redirect()->route('auth.login');
        }

        $roleName = optional($user->loadMissing('role')->role)->nama_role;
        $allowedRoles = collect($roles)
            ->filter()
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->all();

        if ($roleName === null || ! in_array($roleName, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
