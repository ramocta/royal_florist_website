<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): User
    {
        return User::create([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'nmr_telepon' => $data['nmr_telepon'],
            'alamat' => $data['alamat'] ?? null,
            'role' => 'pelanggan',
        ]);
    }

    public function login(array $data): User
    {
        $user = User::where('email', $data['email'])->first();

        if (
            !$user ||
            !Hash::check($data['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        return $user;
    }

    public function logout(): void
    {
        // Session logout ditangani oleh AuthController.
    }
}
