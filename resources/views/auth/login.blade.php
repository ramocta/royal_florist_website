@extends('layouts.auth')
@section('title', 'Login')

@section('content')
    <h2>Login</h2>
    <p class="sub">Masuk ke akun Royal Florist Anda</p>

    @if (session('success'))
        <div class="alert ok">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.proses') }}">
        @csrf

        <div class="row">
            <span class="ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            </span>
            <div class="fld">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="row">
            <span class="ico">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 9V7a5 5 0 0 0-10 0v2H5v12h14V9h-2Zm-8-2a3 3 0 0 1 6 0v2H9V7Zm3 11a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/></svg>
            </span>
            <div class="fld has-eye">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="button" class="eye" data-target="password" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn">
            Masuk
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
    </form>

    <div class="alt">Belum punya akun? <a href="{{ route('register') }}">Register di sini</a></div>
@endsection