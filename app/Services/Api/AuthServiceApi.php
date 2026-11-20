<?php

namespace App\Services\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthServiceApi
{
    public function loginApi(array $data): array
    {
        $user = User::where('email', $data['email'])
            ->where('role', 'karyawan')
            ->first();

        if (
            !$user ||
            !Hash::check($data['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        return [
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }
}
