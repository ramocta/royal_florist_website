<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KaryawanAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $valid = $user
            && strtolower(trim((string) $user->role)) === 'karyawan'
            && strtolower(trim((string) $user->status)) === 'aktif';

        if (! $valid) {
            $token = $user?->currentAccessToken();
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }

            return response()->json(['message' => 'Akun tidak aktif atau bukan karyawan.'], 401);
        }

        return $next($request);
    }
}
