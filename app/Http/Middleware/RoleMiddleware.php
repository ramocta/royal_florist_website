<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            // Role tidak cocok: arahkan ke area yang sesuai, bukan 404
            return redirect()->route(
                in_array($user->role, ['admin', 'karyawan'], true)
                    ? 'admin.dashboard'
                    : 'pelanggan.katalog'
            );
        }

        return $next($request);
    }
}
