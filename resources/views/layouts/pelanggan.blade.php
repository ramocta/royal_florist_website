<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#6a1730">
    <title>@yield('title', 'Royal Florist')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { --maroon:#7b1a33; --maroon-dk:#531a35; --maroon-lt:#9b2c4a; --blush:#f9eef0; --blush-dk:#f6dfe4; --rose:#e9a4b8; --ink:#3a1020; --muted:#8a7078; --line:#ecd9de; --nav-h:68px; }
        *, *::before, *::after { box-sizing:border-box; }
        html { scroll-behavior:smooth; -webkit-text-size-adjust:100%; }
        body { margin:0; min-height:100vh; display:flex; flex-direction:column; font-family:'Inter', system-ui, sans-serif; color:var(--ink); background:linear-gradient(180deg, #fcf3f5 0%, #f6dfe5 100%) fixed; -webkit-tap-highlight-color:transparent; }
        a { color:inherit; }
        button { font-family:inherit; }
        :focus-visible { outline:2px solid var(--maroon); outline-offset:2px; }

        .nav { position:sticky; top:0; z-index:60; background:linear-gradient(90deg, #6a1730, #8a2543); color:#fff; box-shadow:0 6px 24px rgba(83,26,53,.25); }
        .nav-in { max-width:1240px; margin:0 auto; padding:0 24px; height:var(--nav-h); display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; font-family:'Playfair Display', serif; font-weight:700; font-size:21px; }
        .links { display:flex; gap:6px; }
        .links a { text-decoration:none; font-size:14px; font-weight:500; padding:9px 18px; border-radius:99px; color:rgba(255,255,255,.88); transition:background .15s; }
        .links a:hover { background:rgba(255,255,255,.12); }
        .links a.on { background:#fff; color:var(--maroon); font-weight:600; }
        .nav-r { display:flex; align-items:center; gap:12px; }
        .cart { position:relative; width:42px; height:42px; border-radius:50%; border:0; background:rgba(255,255,255,.14); color:#fff; display:grid; place-items:center; cursor:pointer; }
        .cart:hover { background:rgba(255,255,255,.24); }
        .cart b { position:absolute; top:-3px; right:-3px; min-width:18px; height:18px; padding:0 5px; border-radius:99px; background:#fff; color:var(--maroon); font-size:11px; display:none; place-items:center; }
        .cart.bump { animation:bump .45s; }
        .cart b.pop { animation:pop .35s; }
        @keyframes bump { 30% { transform:scale(1.25) rotate(-8deg); } 60% { transform:scale(.92) rotate(4deg); } }
        @keyframes pop { 40% { transform:scale(1.5); } }
        .fly { position:fixed; z-index:200; width:64px; height:64px; border-radius:50%; background:#f6dfe4 center/cover; border:3px solid #fff; box-shadow:0 8px 22px rgba(83,26,53,.35); pointer-events:none; display:grid; place-items:center; overflow:hidden; }
        .fly svg { width:62%; height:auto; }
        .profile { display:inline-flex; align-items:center; gap:8px; background:#fff; color:var(--maroon); text-decoration:none; font-size:13px; font-weight:600; padding:10px 18px; border-radius:99px; }

        main { flex:1; width:100%; max-width:1240px; margin:0 auto; padding:28px 24px 56px; }

        .foot { margin-top:24px; color:#fff; background:linear-gradient(135deg, #6a1730, #8a2543); }
        .foot-in { max-width:1240px; margin:0 auto; padding:36px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px 32px; }
        .foot-brand p { margin:8px 0 0; font-size:13px; letter-spacing:.04em; color:rgba(255,255,255,.72); }
        .foot small { font-size:12px; color:rgba(255,255,255,.7); }

        .tabbar { display:none; }

        .toast { position:fixed; right:20px; bottom:20px; background:var(--maroon-dk); color:#fff; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:500; z-index:100; opacity:0; transform:translateY(8px); transition:.2s; pointer-events:none; box-shadow:0 10px 30px rgba(83,26,53,.35); }
        .toast.show { opacity:1; transform:none; }

        @media (max-width:860px) {
            :root { --nav-h:60px; }
            .nav-in { padding:0 16px; }
            .brand { font-size:19px; }
            .links, .profile { display:none; }
            main { padding:16px 16px 40px; }
            .foot { margin-top:8px; padding-bottom:calc(68px + env(safe-area-inset-bottom)); }
            .foot-in { padding:28px 20px 20px; flex-direction:column; text-align:center; gap:14px; }

            .tabbar { display:grid; grid-template-columns:repeat(5, 1fr); position:fixed; left:0; right:0; bottom:0; z-index:70; background:rgba(255,255,255,.96); backdrop-filter:blur(12px); border-top:1px solid var(--line); padding:6px 4px calc(6px + env(safe-area-inset-bottom)); box-shadow:0 -8px 24px rgba(83,26,53,.08); }
            .tab { display:flex; flex-direction:column; align-items:center; gap:3px; padding:6px 2px; text-decoration:none; font-size:11px; font-weight:500; color:var(--muted); border-radius:12px; }
            .tab svg { width:22px; height:22px; }
            .tab.on { color:var(--maroon); font-weight:700; }
            .tab.on svg { stroke-width:2.4; }
            .toast { left:16px; right:16px; bottom:calc(84px + env(safe-area-inset-bottom)); text-align:center; }
        }
        @media (prefers-reduced-motion:reduce) { html { scroll-behavior:auto; } * { transition:none !important; animation:none !important; } }
    </style>
    @stack('head')
</head>
<body>
    <header class="nav">
        <div class="nav-in">
            <a class="brand" href="{{ url('/') }}">
                <svg width="30" height="30" viewBox="0 0 32 32" fill="none" aria-hidden="true"><circle cx="16" cy="9" r="6" fill="#f4b6c6"/><circle cx="9" cy="17" r="6" fill="#e9a4b8"/><circle cx="23" cy="17" r="6" fill="#f4b6c6"/><circle cx="16" cy="23" r="6" fill="#e9a4b8"/><circle cx="16" cy="16" r="4" fill="#fff"/></svg>
                Royal Florist
            </a>
            <nav class="links" aria-label="Menu utama">
                <a href="{{ url('/') }}">Beranda</a>
                <a href="{{ route('pelanggan.katalog') }}" class="{{ request()->routeIs('pelanggan.katalog') ? 'on' : '' }}">Katalog</a>
                <a href="#">Custom Buket</a>
                <a href="#">Riwayat Pesanan</a>
            </nav>
            <div class="nav-r">
                <button class="cart" id="cartBtn" type="button" aria-label="Keranjang">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                    <b id="cartCount">0</b>
                </button>
                <a class="profile" href="#">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    Profil Saya
                </a>
            </div>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="foot">
        <div class="foot-in">
            <div class="foot-brand">
                <a class="brand" href="{{ url('/') }}">
                    <svg width="30" height="30" viewBox="0 0 32 32" fill="none" aria-hidden="true"><circle cx="16" cy="9" r="6" fill="#f4b6c6"/><circle cx="9" cy="17" r="6" fill="#e9a4b8"/><circle cx="23" cy="17" r="6" fill="#f4b6c6"/><circle cx="16" cy="23" r="6" fill="#e9a4b8"/><circle cx="16" cy="16" r="4" fill="#fff"/></svg>
                    Royal Florist
                </a>
                <p>Fresh Flower &bull; Special Moment</p>
            </div>
            <small>&copy; {{ date('Y') }} Royal Florist. All Rights Reserved.</small>
        </div>
    </footer>

    <nav class="tabbar" aria-label="Menu utama">
        <a class="tab" href="{{ url('/') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>Beranda
        </a>
        <a class="tab {{ request()->routeIs('pelanggan.katalog') ? 'on' : '' }}" href="{{ route('pelanggan.katalog') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>Katalog
        </a>
        <a class="tab" href="#">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/></svg>Custom
        </a>
        <a class="tab" href="#">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2z"/><path d="M9 7h6M9 11h6"/></svg>Pesanan
        </a>
        <a class="tab" href="#">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>Profil
        </a>
    </nav>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <script>
        // Keranjang sementara di browser
        window.RF = (() => {
            const KEY = 'rf_cart';
            const read = () => { try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; } };
            const write = c => { try { localStorage.setItem(KEY, JSON.stringify(c)); } catch (e) {} };
            const count = () => Object.values(read()).reduce((a, n) => a + n, 0);
            let prev = count();

            const badge = () => {
                const b = document.getElementById('cartCount'), n = count();
                b.textContent = n > 99 ? '99+' : n;
                b.style.display = n ? 'grid' : 'none';
                if (n > prev) { b.classList.remove('pop'); void b.offsetWidth; b.classList.add('pop'); }
                prev = n;
            };

            let t;
            const toast = msg => {
                const el = document.getElementById('toast');
                el.textContent = msg; el.classList.add('show');
                clearTimeout(t); t = setTimeout(() => el.classList.remove('show'), 2600);
            };

            // Gambar produk terbang ke ikon keranjang
            const fly = (src, done) => {
                const cart = document.getElementById('cartBtn');
                if (!src || !cart || matchMedia('(prefers-reduced-motion: reduce)').matches) return done();

                const a = src.getBoundingClientRect(), b = cart.getBoundingClientRect();
                const x0 = a.left + a.width / 2, y0 = a.top + a.height / 2;
                const dx = b.left + b.width / 2 - x0, dy = b.top + b.height / 2 - y0;

                const el = document.createElement('div');
                el.className = 'fly';
                el.style.left = (x0 - 32) + 'px';
                el.style.top = (y0 - 32) + 'px';
                if (src.tagName === 'IMG') el.style.backgroundImage = `url("${src.currentSrc || src.src}")`;
                else el.appendChild(src.cloneNode(true));
                document.body.appendChild(el);

                el.animate([
                    { transform: 'translate(0, 0) scale(1)', opacity: 1 },
                    { transform: `translate(${dx * .5}px, ${dy * .5 - 90}px) scale(.75)`, opacity: 1, offset: .5 },
                    { transform: `translate(${dx}px, ${dy}px) scale(.2)`, opacity: .7 }
                ], { duration: 750, easing: 'cubic-bezier(.45, 0, .55, 1)' }).onfinish = () => {
                    el.remove();
                    done();
                    cart.classList.remove('bump'); void cart.offsetWidth; cart.classList.add('bump');
                };
            };

            const add = (id, max = Infinity, src = null) => {
                const c = read(), n = (c[id] || 0) + 1;
                if (n > max) { toast('Jumlah melebihi stok yang tersedia.'); return false; }
                c[id] = n; write(c);
                fly(src, badge);
                return true;
            };

            badge();
            return { add, toast, count };
        })();

        document.getElementById('cartBtn').addEventListener('click', () => RF.toast(RF.count() ? `${RF.count()} item di keranjang. Halaman keranjang segera hadir.` : 'Keranjang masih kosong.'));
    </script>
    @stack('scripts')
</body>
</html>