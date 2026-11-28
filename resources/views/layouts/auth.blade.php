<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title') - Royal Florist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --maroon:#8b0f33; --maroon-dk:#6a0b27; --gold:#e3b79a; --ink:#4a1a28; --muted:#9a7480; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            min-height:100vh; font-family:'Poppins', system-ui, sans-serif; color:var(--ink);
            background:linear-gradient(135deg,#fdeef2 0%,#f9d3de 100%) fixed;
            display:flex; justify-content:center; align-items:center; position:relative; overflow-x:hidden;
        }

        /* Bunga dekorasi di keempat sudut */
        .fl { position:fixed; pointer-events:none; z-index:0; height:auto; }
        .fl-tl { top:0; left:0; width:min(36vw,480px); }
        .fl-tr { top:0; right:0; width:min(32vw,420px); }
        .fl-bl { bottom:0; left:0; width:min(36vw,480px); }
        .fl-br { bottom:0; right:0; width:min(32vw,420px); }

        .wrap { position:relative; z-index:1; width:100%; max-width:460px; padding:36px 22px 32px; display:flex; flex-direction:column; align-items:center; }
        .logo { width:150px; height:130px; display:flex; justify-content:center; align-items:center; margin-bottom:14px; }
        .logo img, .logo svg { max-width:100%; max-height:100%; width:auto; height:auto; object-fit:contain; display:block; }
        .brand { font-family:'Cormorant Garamond', serif; font-weight:700; font-size:2.1rem; letter-spacing:.06em; color:var(--maroon); text-transform:uppercase; margin-top:0; line-height:1.1; position:relative; z-index:1; }
        .divider { display:flex; align-items:center; gap:10px; width:84%; margin:12px 0 10px; color:var(--maroon); }
        .divider::before, .divider::after { content:""; flex:1; height:1px; background:var(--maroon); opacity:.7; }
        .divider svg { width:14px; height:14px; }
        .tagline { font-family:'Cormorant Garamond', serif; font-style:italic; font-weight:600; font-size:1.4rem; color:var(--maroon); text-align:center; line-height:1.2; }
        .tagline-sub { font-family:'Cormorant Garamond', serif; font-style:italic; font-size:1.05rem; color:var(--maroon); text-align:center; margin-top:4px; opacity:.85; }

        .card { width:100%; margin-top:22px; padding:24px 20px 20px; border:1px solid var(--gold); border-radius:24px; background:rgba(255,255,255,.82); box-shadow:0 18px 50px rgba(139,15,51,.16); }
        .card h2 { font-family:'Cormorant Garamond', serif; font-weight:700; font-size:1.8rem; color:var(--maroon); text-align:center; line-height:1.1; }
        .card .sub { text-align:center; font-size:.78rem; color:var(--muted); margin:4px 0 18px; }

        .alert { border-radius:12px; padding:10px 12px; font-size:.78rem; margin-bottom:14px; text-align:center; }
        .alert.error { background:#fdecef; color:#a31d3f; border:1px solid #f5c2cd; }
        .alert.ok { background:#e8f6ee; color:#1e7a46; border:1px solid #bfe3cd; }
        .alert div + div { margin-top:4px; }

        form { display:flex; flex-direction:column; gap:14px; }
        .row { display:flex; align-items:flex-end; gap:12px; }
        .ico { flex:none; width:46px; height:46px; border-radius:50%; background:linear-gradient(180deg,#fbd5de,#f5b9c8); border:1px solid #f0a9bb; display:grid; place-items:center; color:var(--maroon); }
        .ico svg { width:20px; height:20px; }
        .fld { flex:1; min-width:0; position:relative; }
        .fld label { display:block; font-size:.72rem; font-weight:600; color:var(--maroon); margin:0 0 5px 6px; }
        .fld input { width:100%; height:46px; border:1px solid #f0b6c5; border-radius:999px; padding:0 18px; font:inherit; font-size:.85rem; color:var(--ink); background:#fff; outline:none; transition:border-color .15s, box-shadow .15s; }
        .fld input::placeholder { color:#b9a0a8; }
        .fld input:focus { border-color:var(--maroon); box-shadow:0 0 0 3px rgba(139,15,51,.12); }
        .fld.has-eye input { padding-right:46px; }
        .eye { position:absolute; right:8px; bottom:7px; width:32px; height:32px; border:0; background:none; cursor:pointer; color:var(--ink); display:grid; place-items:center; border-radius:50%; }
        .eye svg { width:20px; height:20px; }
        .check { display:flex; align-items:center; gap:8px; font-size:.74rem; color:var(--muted); cursor:pointer; padding-left:4px; }
        .check input { width:16px; height:16px; accent-color:var(--maroon); }
        .check a { color:var(--maroon); font-weight:600; text-decoration:none; }

        .btn { margin-top:6px; height:52px; border:0; border-radius:999px; cursor:pointer; color:#fff; font:inherit; font-weight:600; font-size:1.02rem; display:flex; align-items:center; justify-content:center; gap:10px; background:linear-gradient(180deg,#a5183f 0%,var(--maroon-dk) 100%); box-shadow:0 10px 22px rgba(139,15,51,.35); transition:transform .1s, filter .15s; }
        .btn:hover { filter:brightness(1.08); }
        .btn:active { transform:translateY(1px); }
        .btn svg { width:20px; height:20px; }

        .alt { text-align:center; font-size:.8rem; color:var(--muted); margin-top:16px; }
        .alt a { color:var(--maroon); font-weight:600; text-decoration:none; }
        .alt a:hover { text-decoration:underline; }

        .foot { display:flex; align-items:center; gap:10px; width:84%; margin-top:22px; color:var(--maroon); }
        .foot::before, .foot::after { content:""; flex:1; height:1px; background:var(--maroon); opacity:.6; }
        .foot span { font-family:'Cormorant Garamond', serif; font-style:italic; font-size:1rem; }

        @media (max-width:700px) {
            body { align-items:flex-start; }
            .fl { opacity:.6; }
            .fl-tl, .fl-bl { width:46vw; }
            .fl-tr, .fl-br { width:42vw; }
            .wrap { padding-top:28px; }
        }
    </style>
    @stack('head')
</head>
<body>
    {{-- Definisi bunga dan daun --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <defs>
            <symbol id="flower" viewBox="0 0 100 100">
                <g fill="#f6a9c0"><circle cx="50" cy="24" r="21"/><circle cx="75" cy="42" r="21"/><circle cx="66" cy="72" r="21"/><circle cx="34" cy="72" r="21"/><circle cx="25" cy="42" r="21"/></g>
                <circle cx="50" cy="52" r="20" fill="#f08bab" opacity=".55"/>
                <circle cx="50" cy="52" r="11" fill="#fff"/><circle cx="50" cy="52" r="6" fill="#f6a9c0"/>
            </symbol>
            <symbol id="leaf" viewBox="0 0 60 100"><path d="M30 0C55 25 60 70 30 100C0 70 5 25 30 0Z" fill="#fff" opacity=".7"/></symbol>
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

    <div class="wrap">
        {{-- Logo: public/images/image-removebg-preview.png. Kalau tidak ditemukan, tampil logo cadangan --}}
        <div class="logo">
            <img src="{{ asset('images/image-removebg-preview.png') }}" alt="Royal Florist"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <svg style="display:none" viewBox="0 0 100 100" aria-hidden="true">
                <circle cx="50" cy="38" r="22" fill="#c8102e"/>
                <circle cx="50" cy="38" r="14" fill="#a00d25"/>
                <circle cx="50" cy="38" r="6" fill="#c8102e"/>
                <path d="M22 62c12-6 20 0 22 10-12 6-20 0-22-10Z" fill="#2e8b3d"/>
                <text x="50" y="92" text-anchor="middle" font-family="serif" font-weight="700" font-size="36" fill="#111">R</text>
            </svg>
        </div>

        <div class="brand">Royal Florist</div>
        <div class="divider">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="7" r="4"/><circle cx="7" cy="13" r="4"/><circle cx="17" cy="13" r="4"/><circle cx="12" cy="17" r="4"/><circle cx="12" cy="12" r="2.5" fill="#fbd5de"/></svg>
        </div>
        <div class="tagline">Selamat Datang di Royal Florist</div>
        <div class="tagline-sub">Bunga indah untuk setiap momen spesialmu.</div>

        <div class="card">
            @yield('content')
        </div>

        <div class="foot"><span>Royal Florist</span></div>
    </div>

    <script>
        // Tombol mata untuk menampilkan/menyembunyikan password
        document.querySelectorAll('.eye').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.target);
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
                btn.style.opacity = show ? '.55' : '1';
            });
        });
    </script>
    @stack('scripts')
</body>
</html>