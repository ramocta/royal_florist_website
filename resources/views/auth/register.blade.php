<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <title>Royal Florist - Registrasi Pelanggan</title>
    <style>
        :root { --maroon:#8b2346; --maroon-dark:#6f1a37; --ink:#3d1f2a; --muted:#8a6b75; --line:#ecd3da; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; color: var(--ink); min-height: 100vh; position: relative; overflow-x: hidden; background: linear-gradient(135deg, #fdf1f4, #f5d6de); }

        .fl { position: fixed; pointer-events: none; z-index: 0; height: auto; }
        .fl-tl { top: 0; left: 0; width: min(34vw, 420px); }
        .fl-tr { top: 0; right: 0; width: min(30vw, 380px); }
        .fl-bl { bottom: 0; left: 0; width: min(34vw, 420px); }
        .fl-br { bottom: 0; right: 0; width: min(30vw, 380px); }

        .auth { position: relative; z-index: 1; min-height: 100vh; display: grid; grid-template-columns: 1fr 1fr; align-items: center; max-width: 1400px; margin: 0 auto; padding: 40px 5vw; gap: 40px; }
        .welcome { text-align: center; }
        .welcome h1 { font-family: 'Playfair Display', serif; font-weight: 700; color: var(--maroon); font-size: clamp(2rem, 3.4vw, 3rem); line-height: 1.25; }
        .welcome p { font-family: 'Playfair Display', serif; font-style: italic; color: var(--maroon); font-size: clamp(1rem, 1.4vw, 1.25rem); line-height: 1.6; margin-top: 28px; }
        .welcome .hearts { margin-top: 22px; width: 44px; height: auto; }

        .login-card { background: #fff; border-radius: 18px; padding: 36px 32px; width: 100%; max-width: 420px; justify-self: center; box-shadow: 0 18px 50px rgba(139, 35, 70, .14); }
        .brand-title { font-family: 'Playfair Display', serif; text-align: center; color: var(--maroon); font-size: 1.5rem; font-weight: 700; }
        .brand-subtitle { text-align: center; font-size: .8rem; color: var(--muted); margin-top: 4px; }

        form { margin-top: 24px; display: flex; flex-direction: column; gap: 13px; }
        .form-group { position: relative; }
        .form-group label { display: block; font-size: .72rem; font-weight: 700; margin-bottom: 6px; }
        .form-group input { width: 100%; height: 44px; border: 1px solid var(--line); border-radius: 10px; padding: 0 14px; font: inherit; font-size: .82rem; color: var(--ink); background: #fff; outline: none; transition: border-color .15s, box-shadow .15s; }
        .form-group input::placeholder { color: #b9a0a8; }
        .form-group input:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(139, 35, 70, .12); }
        .form-group.has-eye input { padding-right: 44px; }
        .eye { position: absolute; right: 6px; bottom: 6px; width: 32px; height: 32px; border: 0; background: none; cursor: pointer; color: var(--muted); display: grid; place-items: center; border-radius: 8px; }
        .eye:hover { color: var(--maroon); }
        .eye:focus-visible { outline: 2px solid var(--maroon); }
        .eye svg { width: 18px; height: 18px; }

        .check { display: flex; align-items: center; gap: 8px; font-size: .72rem; color: var(--muted); cursor: pointer; }
        .check input { width: 15px; height: 15px; accent-color: var(--maroon); }
        a { color: var(--maroon); font-weight: 700; text-decoration: none; }
        a:hover { text-decoration: underline; }

        .btn-submit { height: 46px; border: 0; border-radius: 999px; background: var(--maroon); color: #fff; font: inherit; font-weight: 600; font-size: .88rem; cursor: pointer; margin-top: 6px; transition: background .15s; }
        .btn-submit:hover { background: var(--maroon-dark); }
        .btn-submit:focus-visible { outline: 3px solid rgba(139, 35, 70, .35); outline-offset: 2px; }

        .link-reg { display: block; text-align: center; font-size: .72rem; font-weight: 400; color: var(--muted); margin-top: 20px; }
        .link-reg b { color: var(--maroon); }
        .link-reg:hover { text-decoration: none; }
        .link-reg:hover b { text-decoration: underline; }

        .alert { background: #fdecef; color: #a31d3f; border: 1px solid #f5c2cd; border-radius: 10px; padding: 10px 12px; font-size: .78rem; margin-top: 18px; }
        .alert div + div { margin-top: 4px; }

        @media (max-width: 860px) {
            .auth { grid-template-columns: 1fr; padding: 32px 20px; gap: 24px; }
            .welcome p, .welcome .hearts { display: none; }
            .welcome h1 { font-size: 1.7rem; }
            .fl-tl, .fl-bl { width: 42vw; } .fl-tr, .fl-br { width: 38vw; }
        }

        /* ---------- Responsif ---------- */
        @media (max-width: 860px) {
            .fl { opacity: .55; }
            .form-group input { font-size: 16px; }
        }
        @media (max-width: 480px) {
            .auth { padding: 20px 14px; gap: 16px; }
            .login-card { padding: 26px 18px; border-radius: 16px; }
            .welcome h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <!-- Dekorasi bunga -->
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <defs>
            <symbol id="flower" viewBox="0 0 100 100">
                <g fill="#f6a9c0"><circle cx="50" cy="24" r="21"/><circle cx="75" cy="42" r="21"/><circle cx="66" cy="72" r="21"/><circle cx="34" cy="72" r="21"/><circle cx="25" cy="42" r="21"/></g>
                <circle cx="50" cy="52" r="20" fill="#f08bab" opacity=".55"/>
                <circle cx="50" cy="52" r="11" fill="#fff"/><circle cx="50" cy="52" r="6" fill="#f6a9c0"/>
            </symbol>
            <symbol id="leaf" viewBox="0 0 60 100"><path d="M30 0C55 25 60 70 30 100C0 70 5 25 30 0Z" fill="#fff"/></symbol>
            <symbol id="eye-open" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/></symbol>
            <symbol id="eye-off" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 5.1A9.7 9.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4M6.5 6.6C3.8 8.4 2 12 2 12s3.6 7 10 7c1.6 0 3-.4 4.3-1M9.9 9.9a3 3 0 0 0 4.2 4.2"/></symbol>
        </defs>
    </svg>
    <svg class="fl fl-tl" viewBox="0 0 300 300" aria-hidden="true">
        <use href="#leaf" x="-5" y="150" width="60" height="100" transform="rotate(35 25 200)"/>
        <use href="#flower" x="40" y="30" width="140" height="140"/><use href="#flower" x="-10" y="-25" width="90" height="90"/>
        <use href="#flower" x="130" y="-5" width="80" height="80"/><use href="#flower" x="20" y="150" width="60" height="60"/>
        <use href="#flower" x="105" y="150" width="45" height="45"/>
    </svg>
    <svg class="fl fl-tr" viewBox="0 0 300 300" aria-hidden="true">
        <use href="#leaf" x="20" y="120" width="60" height="100" transform="rotate(-30 50 170)"/>
        <use href="#flower" x="130" y="40" width="160" height="160"/><use href="#flower" x="40" y="-20" width="100" height="100"/>
        <use href="#flower" x="230" y="-20" width="90" height="90"/><use href="#flower" x="90" y="110" width="45" height="45"/>
    </svg>
    <svg class="fl fl-bl" viewBox="0 0 300 300" aria-hidden="true">
        <use href="#leaf" x="120" y="60" width="60" height="100" transform="rotate(30 150 110)"/>
        <use href="#leaf" x="-10" y="40" width="55" height="90" transform="rotate(-25 18 85)"/>
        <use href="#flower" x="30" y="140" width="160" height="160"/><use href="#flower" x="-15" y="215" width="95" height="95"/>
        <use href="#flower" x="150" y="215" width="90" height="90"/><use href="#flower" x="10" y="100" width="55" height="55"/>
    </svg>
    <svg class="fl fl-br" viewBox="0 0 300 300" aria-hidden="true">
        <use href="#leaf" x="60" y="40" width="60" height="100" transform="rotate(-30 90 90)"/>
        <use href="#flower" x="120" y="120" width="170" height="170"/><use href="#flower" x="30" y="200" width="100" height="100"/>
        <use href="#flower" x="215" y="55" width="85" height="85"/><use href="#flower" x="85" y="110" width="45" height="45"/>
    </svg>

    <main class="auth">
        <section class="welcome">
            <h1>Selamat Datang<br>di Royal Florist</h1>
            <p>Bunga indah untuk setiap<br>momen spesialmu.</p>
            <svg class="hearts" viewBox="0 0 44 36" aria-hidden="true">
                <path d="M14 8c-3-5-11-3-11 4 0 6 8 11 11 14 3-3 11-8 11-14 0-7-8-9-11-4Z" fill="#8b2346"/>
                <path d="M31 14c-2-4-8-2-8 3 0 4 6 8 8 10 2-2 8-6 8-10 0-5-6-7-8-3Z" fill="#b63a5f"/>
            </svg>
        </section>

        <div class="login-card">
            <div class="brand-title">Register Pelanggan</div>
            <div class="brand-subtitle">Buat akun untuk mulai berbelanja</div>

            @if ($errors->any())
                <div class="alert" role="alert">
                    @foreach ($errors->all() as $pesan)
                        <div>{{ $pesan }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.proses') }}">
                @csrf
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama lengkap Anda" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contoh@email.com" required>
                </div>

                <div class="form-group">
                    <label for="nmr_telepon">Nomor Telepon / WA</label>
                    <input type="text" id="nmr_telepon" name="nmr_telepon" value="{{ old('nmr_telepon') }}" placeholder="081234567890">
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" id="alamat" name="alamat" value="{{ old('alamat') }}" placeholder="Masukkan alamat pengiriman">
                </div>

                <div class="form-group has-eye">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Buat password aman" required>
                    <button type="button" class="eye" data-target="password" aria-label="Tampilkan password"><svg><use href="#eye-open"/></svg></button>
                </div>

                <div class="form-group has-eye">
                    <label for="konfirmasi">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi" name="password_confirmation" placeholder="Ulangi password" required>
                    <button type="button" class="eye" data-target="konfirmasi" aria-label="Tampilkan password"><svg><use href="#eye-open"/></svg></button>
                </div>

                <label class="check"><input type="checkbox" required> Saya menyetujui <a href="#">Syarat &amp; Ketentuan</a> yang berlaku</label>

                <button type="submit" class="btn-submit">Daftar Sekarang</button>
            </form>

            <a href="{{ route('login') }}" class="link-reg">Sudah punya akun? <b>Login di sini</b></a>
        </div>
    </main>

    <script>
        document.querySelectorAll('.eye').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.target);
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('use').setAttribute('href', show ? '#eye-off' : '#eye-open');
                btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            });
        });
    </script>
    <script>
        // Cek cepat di browser; validasi resmi tetap dilakukan server (Laravel)
        var pw = document.getElementById('password'), kf = document.getElementById('konfirmasi');
        function cek() { kf.setCustomValidity(kf.value !== pw.value ? 'Konfirmasi password tidak sama.' : ''); }
        pw.addEventListener('input', cek); kf.addEventListener('input', cek);
    </script>
</body>
</html>