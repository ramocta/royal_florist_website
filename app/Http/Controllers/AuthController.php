<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const ROLE_ADMIN_AREA = ['admin', 'karyawan'];
    private const ROLE_DIIZINKAN  = ['admin', 'karyawan', 'pelanggan'];

    public function __construct(protected AuthService $authService)
    {
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $this->authService->register($request->validated());

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function login(LoginRequest $request)
    {
        $user = $this->authService->login($request->validated());

        if (! in_array($user->role, self::ROLE_DIIZINKAN, true)) {
            throw ValidationException::withMessages([
                'email' => ['Akun ini tidak dapat login melalui halaman ini.'],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        if (in_array($user->role, self::ROLE_ADMIN_AREA, true)) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('pelanggan.katalog');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Berhasil logout.');
    }
}
