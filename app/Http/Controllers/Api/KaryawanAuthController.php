<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Login/logout mobile karyawan (token Sanctum).
 * Dipisah dari AuthController web karena yang web memakai session + redirect.
 */
class KaryawanAuthController extends Controller
{
    // Role yang boleh login di aplikasi mobile
    private const ROLE_DIIZINKAN = ['karyawan', 'admin'];

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'required' => ':attribute wajib diisi.',
            'email'    => 'Format email tidak valid.',
        ], [
            'email'    => 'Email',
            'password' => 'Password',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! in_array($user->role, self::ROLE_DIIZINKAN, true)) {
            throw ValidationException::withMessages([
                'email' => ['Akun ini tidak dapat login di aplikasi karyawan.'],
            ]);
        }

        if ($user->status !== 'aktif') {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda nonaktif. Hubungi admin.'],
            ]);
        }

        // Satu perangkat satu token: hapus token mobile lama
        $user->tokens()->where('name', 'mobile')->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => [
                'id_user'     => $user->id_user,
                'nama'        => $user->nama,
                'email'       => $user->email,
                'role'        => $user->role,
                'nmr_telepon' => $user->nmr_telepon,
                'alamat'      => $user->alamat,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }
}
