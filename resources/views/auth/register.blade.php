@extends('layouts.auth')
@section('title', 'Registrasi Pelanggan')

@section('content')
    <h2>Register Pelanggan</h2>
    <p class="sub">Buat akun untuk mulai berbelanja</p>

    @if ($errors->any())
        <div class="alert error" role="alert">
            @foreach ($errors->all() as $pesan)
                <div>{{ $pesan }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.proses') }}">
        @csrf

        <div class="row">
            <span class="ico"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0Z"/></svg></span>
            <div class="fld">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama lengkap Anda" required>
            </div>
        </div>

        <div class="row">
            <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
            <div class="fld">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contoh@email.com" required>
            </div>
        </div>

        <div class="row">
            <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/></svg></span>
            <div class="fld">
                <label for="nmr_telepon">Nomor Telepon / WA</label>
                <input type="text" id="nmr_telepon" name="nmr_telepon" value="{{ old('nmr_telepon') }}" placeholder="081234567890">
            </div>
        </div>

        <div class="row">
            <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
            <div class="fld">
                <label for="alamat">Alamat</label>
                <input type="text" id="alamat" name="alamat" value="{{ old('alamat') }}" placeholder="Masukkan alamat pengiriman">
            </div>
        </div>

        <div class="row">
            <span class="ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 9V7a5 5 0 0 0-10 0v2H5v12h14V9h-2Zm-8-2a3 3 0 0 1 6 0v2H9V7Zm3 11a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/></svg></span>
            <div class="fld has-eye">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Buat password aman" required>
                <button type="button" class="eye" data-target="password" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <div class="row">
            <span class="ico"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 9V7a5 5 0 0 0-10 0v2H5v12h14V9h-2Zm-8-2a3 3 0 0 1 6 0v2H9V7Zm3 11a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/></svg></span>
            <div class="fld has-eye">
                <label for="konfirmasi">Konfirmasi Password</label>
                <input type="password" id="konfirmasi" name="password_confirmation" placeholder="Ulangi password" required>
                <button type="button" class="eye" data-target="konfirmasi" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <label class="check">
            <input type="checkbox" required> Saya menyetujui <a href="#">Syarat &amp; Ketentuan</a> yang berlaku
        </label>

        <button type="submit" class="btn">
            Daftar Sekarang
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
    </form>

    <div class="alt">Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></div>
@endsection

@push('scripts')
<script>
    // Cek cepat di browser; validasi resmi tetap dilakukan server (Laravel)
    var pw = document.getElementById('password'), kf = document.getElementById('konfirmasi');
    function cek() { kf.setCustomValidity(kf.value !== pw.value ? 'Konfirmasi password tidak sama.' : ''); }
    pw.addEventListener('input', cek); kf.addEventListener('input', cek);
</script>
@endpush