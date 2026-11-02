<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Royal Florist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; overflow: hidden;
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(135deg, #fdeef2 0%, #f9d9e2 100%);
            display: flex; align-items: center; justify-content: space-around;
            position: relative;
        }
        /* Bunga dekorasi */
        .bunga { position: absolute; width: 180px; height: 180px; z-index: 0; }
        .b1 { top: -30px;  left: -30px;  width: 190px; height: 190px; }
        .b2 { top: 40px;   left: 90px;   width: 340px; height: 340px; opacity: .9; }
        .b3 { top: 120px;  left: 280px;  width: 160px; height: 160px; }
        .b4 { bottom: -60px; left: 40px; width: 380px; height: 380px; }
        .b5 { bottom: 30px; left: 360px; width: 190px; height: 190px; }
        .b6 { top: -20px;  right: 260px; width: 190px; height: 190px; }
        .b7 { top: -30px;  right: -20px; width: 150px; height: 150px; }
        .b8 { top: 120px;  right: -60px; width: 330px; height: 330px; }
        .b9 { bottom: -40px; right: 100px; width: 200px; height: 200px; }
        .b10 { bottom: -30px; right: -40px; width: 330px; height: 330px; }

        .sambutan { position: relative; z-index: 1; max-width: 520px; padding: 20px; }
        .sambutan h1 {
            font-family: 'Playfair Display', serif; font-size: 56px; line-height: 1.15;
            color: #8b2045; margin: 0 0 30px;
        }
        .sambutan p {
            font-family: 'Playfair Display', serif; font-style: italic;
            font-size: 24px; color: #8b2045; margin: 0 0 24px;
        }
        .hati { display: flex; align-items: flex-end; gap: 2px; padding-left: 200px; }

        .card {
            position: relative; z-index: 1; background: #fff;
            width: 100%; max-width: 630px; padding: 56px 48px 40px;
            border-radius: 36px; box-shadow: 0 20px 50px rgba(139,32,69,.12);
        }
        .card h2 { font-family: 'Playfair Display', serif; color: #8b2045; text-align: center; font-size: 36px; margin: 0 0 6px; }
        .card .sub { text-align: center; color: #555; margin: 0 0 32px; }
        .field { position: relative; margin-bottom: 18px; }
        .field input {
            width: 100%; padding: 18px 20px; font-size: 16px; font-family: inherit;
            border: 1px solid #e6dcdf; border-radius: 16px; outline: none; background: #fff;
        }
        .field input:focus { border-color: #8b2045; box-shadow: 0 0 0 4px rgba(139,32,69,.12); }
        .mata { position: absolute; right: 18px; top: 50%; transform: translateY(-50%); background: none; border: 0; cursor: pointer; width: 28px; height: 28px; padding: 0; }
        .btn {
            width: 100%; padding: 20px; margin-top: 8px; background: #8b2045; color: #fff;
            border: 0; border-radius: 40px; font-size: 18px; font-weight: 700; cursor: pointer; font-family: inherit;
        }
        .btn:hover { background: #721a39; }
        .error { background: #fde8e8; color: #b00020; padding: 12px; border-radius: 12px; margin-bottom: 18px; text-align: center; }
        .sukses { background: #e8f6ee; color: #1e7a46; padding: 12px; border-radius: 12px; margin-bottom: 18px; text-align: center; }
        .foot { text-align: center; margin-top: 24px; color: #444; }
        .foot a { color: #8b2045; font-weight: 700; text-decoration: none; }

        @media (max-width: 1000px) {
            body { flex-direction: column; justify-content: center; overflow: auto; padding: 24px; }
            .sambutan h1 { font-size: 34px; }
            .sambutan p { font-size: 18px; }
            .hati { padding-left: 0; }
            .card { padding: 36px 24px; }
        }
    </style>
</head>
<body>

<!-- Definisi bunga (dipakai berulang) -->
<svg width="0" height="0" style="position:absolute">
    <defs>
        <symbol id="bunga" viewBox="0 0 200 200">
            <g fill="#f7a8c0">
                <circle cx="100" cy="45"  r="42"/>
                <circle cx="152" cy="82"  r="42"/>
                <circle cx="132" cy="146" r="42"/>
                <circle cx="68"  cy="146" r="42"/>
                <circle cx="48"  cy="82"  r="42"/>
            </g>
            <circle cx="100" cy="100" r="52" fill="#f293b1"/>
            <circle cx="100" cy="100" r="26" fill="#fff"/>
            <circle cx="100" cy="100" r="13" fill="#f7a8c0"/>
        </symbol>
        <symbol id="hati" viewBox="0 0 24 24">
            <path d="M12 21s-8-5.4-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 5.6-8 11-8 11z"/>
        </symbol>
    </defs>
</svg>

@foreach (range(1, 10) as $n)
    <svg class="bunga b{{ $n }}"><use href="#bunga"/></svg>
@endforeach

<div class="sambutan">
    <h1>Selamat Datang<br>di Royal Florist</h1>
    <p>Bunga indah untuk setiap<br>momen spesialmu.</p>
    <div class="hati">
        <svg width="38" height="38" fill="#8b2045"><use href="#hati"/></svg>
        <svg width="26" height="26" fill="#c2335f"><use href="#hati"/></svg>
    </div>
</div>

<div class="card">
    <h2>Login</h2>
    <p class="sub">Masuk ke akun Royal Florist Anda</p>

    @if (session('success'))
        <div class="sukses">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('login.proses') }}">
        @csrf
        <div class="field">
            <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
        </div>
        <div class="field">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <button type="button" class="mata" id="togglePw" aria-label="Tampilkan password">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#555" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
        <button type="submit" class="btn">Masuk</button>
    </form>

    <div class="foot">Belum punya akun? <a href="{{ route('register') }}">Register di sini</a></div>
</div>

<script>
    document.getElementById('togglePw').addEventListener('click', function () {
        var p = document.getElementById('password');
        p.type = (p.type === 'password') ? 'text' : 'password';
    });
</script>
</body>
</html>