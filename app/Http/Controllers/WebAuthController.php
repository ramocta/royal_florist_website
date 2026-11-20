<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    /* ----------------------------- LOGIN ----------------------------- */

    public function showLogin()
    {
        // Kalau sudah login, langsung arahkan berdasarkan role
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $email    = trim((string) $request->input('email'));
        $password = trim((string) $request->input('password'));

        if ($email === '' || $password === '') {
            return back()->withInput($request->only('email'))
                ->with('error', 'Email dan password wajib diisi.');
        }

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $request->session()->regenerate();

            return $this->redirectByRole(Auth::user());
        }

        return back()->withInput($request->only('email'))
            ->with('error', 'Email atau password salah!');
    }

    /* ---------------------------- REGISTER --------------------------- */

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nama'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'string', 'confirmed'],
            'nmr_telepon' => ['nullable', 'string', 'max:30'],
            'alamat'      => ['nullable', 'string', 'max:255'],
        ], [
            'nama.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar, silakan gunakan email lain atau langsung masuk.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        User::create([
            'nama'        => trim($data['nama']),
            'email'       => trim($data['email']),
            'password'    => Hash::make(trim($data['password'])),
            'nmr_telepon' => $data['nmr_telepon'] ?? '',
            'alamat'      => $data['alamat'] ?? '',
            'role'        => 'pelanggan', // otomatis pelanggan
            'status'      => 'aktif',     // pelanggan langsung aktif
        ]);

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil! Silakan masuk dengan akun Anda.');
    }

    /* ----------------------------- LOGOUT ---------------------------- */

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /* ------------------------------ BANTU ---------------------------- */

    private function redirectByRole($user)
    {
        $role = strtolower(trim($user->role ?? ''));

        if ($role === 'admin' || $role === 'karyawan') {
            return redirect()->route('admin.dashboard');
        }

        // TODO: ganti ke dashboard pelanggan kalau halamannya sudah dibuat di Laravel
        return redirect('/');
    }
}