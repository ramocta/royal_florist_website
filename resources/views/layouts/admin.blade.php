{{--
  LAYOUT ADMIN: topbar + sidebar + area isi + footer. Ditulis sekali, dipakai semua halaman admin.
  Tampilannya diambil dari layout dashboard buatan temanmu (lebih akurat dengan Figma).

  Cara pakai di halaman:
    @extends('layouts.admin')
    @section('title', 'Judul Tab Browser')
    @section('content')
        ...isi halaman...
    @endsection

  Tambahan opsional:
    @push('styles')  <style>...</style>   @endpush
    @push('scripts') <script>...</script> @endpush
    @section('top-tanggal') ... @endsection
--}}
@php
    // Helper ikon milik temanmu
    $ikon = fn (string $n, int $u = 18): string => \App\Support\Ikon::svg($n, $u);

    // [label, ikon, route tujuan, pola route untuk menandai menu aktif]
    $menu = [
        ['Dashboard',          'home',     'admin.dashboard',        'admin.dashboard'],
        ['Laporan Penjualan',  'laporan',  'admin.laporan.index',    'admin.laporan.*'],
        ['Pelanggan',          'user',     'admin.pelanggan.index',  'admin.pelanggan.*'],
        ['Pesanan',            'cart',     'admin.pesanan.index',    'admin.pesanan.*'],
        ['Produk',             'box',      'admin.produk.index',     'admin.produk.*'],
        ['Stok',               'database', 'admin.stok.index',       'admin.stok.*'],
        ['Manajemen Staff',    'users',    'admin.karyawan.index',   'admin.karyawan.*'],
        ['Manajemen Progress', 'progress', 'admin.progress.index',   'admin.progress.*'],
        ['Profil', 'user', 'admin.profil', 'admin.profil'],
        ['Pengaturan',         'settings', 'admin.pengaturan.index', 'admin.pengaturan.*'],
    ];

    $namaAdmin = auth()->user()?->nama ?? 'Admin';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - Royal Florist</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&family=Lora:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')

    <style>
        :root {
            --maroon: #7B1830;
            --pink-bg: #FBE9ED;
            --pink-kartu: #FAE6EB;
            --pink-garis: #F3CCD6;
            --teks: #2B2B2B;
            --abu: #6F6F6F;
            --hijau: #2E8B57;
            --merah: #C0392B;
        }

        body {
            background: #fff;
            color: var(--teks);
            min-height: 100vh;
        }

        .ikon {
            flex-shrink: 0;
            display: block;
        }

        .container {
            max-width: none;
        }

        /* ---------- Header atas ---------- */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 72px;
            z-index: 20;
            background: var(--pink-bg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px 0 24px;
            border-bottom: 1px solid var(--pink-garis);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--maroon);
        }

        .brand svg.logo {
            width: 44px;
            height: 44px;
        }

        .brand-teks .nama {
            font-family: 'Cormorant Garamond', serif;
            font-size: 30px;
            font-weight: 500;
            line-height: 1;
            letter-spacing: .3px;
        }

        .brand-teks .slogan {
            font-size: 8.5px;
            color: #5d3a45;
            margin-top: 3px;
            letter-spacing: .2px;
        }

        .top-kanan {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .date-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            border: 1px solid var(--pink-garis);
            border-radius: 10px;
            padding: 6px 12px;
            color: var(--maroon);
            font-size: 12.5px;
        }

        .date-pill input[type=date] {
            border: none;
            background: transparent;
            font-size: 12.5px;
            color: var(--teks);
            outline: none;
            cursor: pointer;
        }

        /* ---------- Lonceng notifikasi (popup) ---------- */
        .notif-menu {
            position: relative;
        }

        .notif-menu summary {
            list-style: none;
            position: relative;
            display: flex;
            cursor: pointer;
            color: var(--maroon);
        }

        .notif-menu summary::-webkit-details-marker {
            display: none;
        }

        /* Badge angka; hanya dirender kalau ada notifikasi */
        .bell-badge {
            position: absolute;
            top: -7px;
            right: -8px;
            min-width: 17px;
            height: 17px;
            padding: 0 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0446a;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            border-radius: 999px;
            border: 1.5px solid var(--pink-bg);
        }

        .notif-panel {
            position: absolute;
            right: -8px;
            top: 40px;
            z-index: 60;
            width: 340px;
            max-width: calc(100vw - 24px);
            background: #fff;
            border: 1px solid var(--pink-garis);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(123, 24, 48, .16);
            overflow: hidden;
        }

        .notif-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            border-bottom: 1px solid var(--pink-garis);
            font-size: 13px;
            font-weight: 600;
            color: var(--maroon);
        }

        .notif-head small {
            font-size: 11px;
            font-weight: 500;
            color: var(--abu);
        }

        .notif-list {
            max-height: 360px;
            overflow-y: auto;
        }

        .notif-item {
            display: flex;
            gap: 10px;
            padding: 11px 14px;
            border-bottom: 1px solid var(--pink-bg);
            color: var(--teks);
            text-decoration: none;
        }

        .notif-item:last-child {
            border-bottom: none;
        }

        .notif-item:hover {
            background: var(--pink-bg);
        }

        .notif-ikon {
            flex: none;
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--pink-kartu);
            color: var(--maroon);
        }

        .notif-ikon.habis    { background: #fbe3e0; color: var(--merah); }
        .notif-ikon.menipis  { background: #fdf0d5; color: #b7791f; }

        .notif-isi {
            min-width: 0;
        }

        .notif-isi b {
            display: block;
            font-size: 12.5px;
            color: var(--maroon);
        }

        .notif-isi span {
            display: block;
            margin-top: 1px;
            font-size: 12px;
            color: var(--abu);
            overflow-wrap: anywhere;
        }

        .notif-isi time {
            display: block;
            margin-top: 3px;
            font-size: 10.5px;
            color: #9a9a9a;
        }

        .notif-kosong {
            padding: 26px 14px;
            text-align: center;
            font-size: 12.5px;
            color: var(--abu);
        }

        .admin-menu {
            position: relative;
        }

        .admin-menu summary {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--maroon);
        }

        .admin-menu summary::-webkit-details-marker {
            display: none;
        }

        .avatar {
            width: 38px;
            height: 38px;
            background: var(--maroon);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dropdown {
            position: absolute;
            right: 0;
            top: 48px;
            min-width: 160px;
            background: #fff;
            border: 1px solid var(--pink-garis);
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(123, 24, 48, .12);
            padding: 6px;
        }

        .dropdown a,
        .dropdown button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            width: 100%;
            color: var(--maroon);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .dropdown a:hover,
        .dropdown button:hover {
            background: var(--pink-bg);
        }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed;
            top: 72px;
            bottom: 0;
            left: 0;
            width: 240px;
            background: var(--maroon);
            color: #fff;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 10;
        }

        .sidebar-menu {
            list-style: none;
            padding: 18px 0 0;
            position: relative;
            z-index: 2;
        }

        .sidebar-menu li {
            margin: 2px 12px;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 11px 16px;
            border-radius: 12px;
            color: rgba(255, 255, 255, .78);
            text-decoration: none;
            font-size: 13px;
            transition: .2s;
        }

        .sidebar-menu li a:hover {
            background: rgba(255, 255, 255, .1);
            color: #fff;
        }

        .sidebar-menu li.active a {
            background: #FCEEF1;
            color: var(--maroon);
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .12);
        }

        .hiasan {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 270px;
            z-index: 1;
            pointer-events: none;
        }

        /* ---------- Area isi ---------- */
        .main-content {
            margin: 72px 0 0 240px;
            min-height: calc(100vh - 72px);
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .area-konten {
            padding: 24px 28px;
            flex: 1;
        }

        /* ---------- Footer ---------- */
        .footer-admin {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 28px 20px;
            font-size: 11px;
            color: var(--abu);
        }

        .footer-admin .motto {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 15px;
            color: #B24A66;
        }

        /* ---------- Tombol menu (HP) ---------- */
        .menu-toggle {
            display: none;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #fff;
            border: 1px solid var(--pink-garis);
            border-radius: 10px;
            color: var(--maroon);
            cursor: pointer;
        }

        .sidebar-backdrop {
            display: none;
        }

        /* ---------- Responsif (tablet & HP) ---------- */
        @media screen and (max-width: 900px) {
            body.menu-open {
                overflow: hidden;
            }

            .topbar {
                position: sticky;
                height: auto;
                min-height: 60px;
                padding: 8px 14px;
                flex-wrap: wrap;
                gap: 8px 10px;
            }

            .top-kanan {
                display: contents;
            }

            .menu-toggle {
                display: flex;
                order: 0;
            }

            .brand {
                order: 1;
                margin-right: auto;
            }

            .notif-menu {
                order: 2;
                position: static;   /* supaya popup menempel ke header, bukan ke ikon lonceng */
            }

            .notif-panel {
                left: 12px;
                right: 12px;
                top: 100%;
                width: auto;
                max-width: none;
                margin-top: 4px;
            }

            .admin-menu {
                order: 3;
            }

            .date-pill {
                order: 4;
                flex: 1 1 100%;
                flex-wrap: wrap;
                justify-content: center;
            }

            .date-pill input[type=date] {
                flex: 1 1 110px;
                min-width: 0;
                width: auto;
                font-size: 14px;
            }

            .brand-teks .slogan {
                display: none;
            }

            .brand-teks .nama {
                font-size: 24px;
            }

            .brand svg.logo {
                width: 34px;
                height: 34px;
            }

            .sidebar {
                top: 0;
                width: 260px;
                max-width: 84vw;
                padding-top: 8px;
                z-index: 50;
                transform: translateX(-105%);
                transition: transform .25s ease;
            }

            body.menu-open .sidebar {
                transform: none;
                box-shadow: 6px 0 24px rgba(0, 0, 0, .25);
            }

            .sidebar-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 45;
                background: rgba(43, 12, 20, .45);
                opacity: 0;
                pointer-events: none;
                transition: opacity .25s;
            }

            body.menu-open .sidebar-backdrop {
                opacity: 1;
                pointer-events: auto;
            }

            .main-content {
                margin: 0;
                min-height: 0;
            }

            .area-konten {
                padding: 16px 14px 24px;
            }

            .footer-admin {
                flex-direction: column;
                gap: 4px;
                text-align: center;
                padding: 12px 14px 20px;
            }
        }

        @media screen and (max-width: 600px) {
            .admin-menu summary span:not(.avatar) {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Aset gambar bunga -->
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <defs>
            <symbol id="bunga" viewBox="0 0 100 100">
                <g fill="currentColor">
                    <ellipse cx="50" cy="25" rx="11" ry="20"/>
                    <ellipse cx="50" cy="25" rx="11" ry="20" transform="rotate(72 50 50)"/>
                    <ellipse cx="50" cy="25" rx="11" ry="20" transform="rotate(144 50 50)"/>
                    <ellipse cx="50" cy="25" rx="11" ry="20" transform="rotate(216 50 50)"/>
                    <ellipse cx="50" cy="25" rx="11" ry="20" transform="rotate(288 50 50)"/>
                    <circle cx="50" cy="50" r="7" fill="#fff" opacity=".55"/>
                </g>
            </symbol>
        </defs>
    </svg>

    <!-- TOP NAVBAR -->
    <header class="topbar">
        <button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka menu" aria-expanded="false">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                <line x1="4" y1="7" x2="20" y2="7"/>
                <line x1="4" y1="12" x2="20" y2="12"/>
                <line x1="4" y1="17" x2="20" y2="17"/>
            </svg>
        </button>

        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="brand">
            <svg class="logo" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M24 8c-5 0-9 3.5-9 8 0 3.5 2.3 6 5 7.2M24 8c5 0 9 3.5 9 8 0 3.5-2.3 6-5 7.2"/>
                <path d="M24 8c-2.2 2.5-2.2 6 0 9 2.2-3 2.2-6.5 0-9z"/>
                <path d="M20 23.2c1.2.5 2.5.8 4 .8s2.8-.3 4-.8"/>
                <path d="M24 24v18"/>
                <path d="M24 34c-4.5-.5-7.5-3-8.5-6.5 4 0 7.5 2 8.5 6.5z"/>
                <path d="M24 38c4-.5 7-2.8 8-6-3.8 0-7 1.8-8 6z"/>
            </svg>

            <div class="brand-teks">
                <div class="nama">Royal Florist</div>
                <div class="slogan">Fresh Flower • Special Moment</div>
            </div>
        </a>

        <div class="top-kanan">
            {{-- Chip tanggal --}}
            @hasSection('top-tanggal')
                @yield('top-tanggal')
            @else
                <div class="date-pill">
                    {!! $ikon('calendar', 18) !!}
                    <span>{{ now()->startOfWeek()->translatedFormat('d') }} - {{ now()->endOfWeek()->translatedFormat('d F Y') }}</span>
                </div>
            @endif

            {{-- Lonceng notifikasi: klik untuk membuka popup. Datanya ($notifikasi) dikirim oleh View composer di AppServiceProvider --}}
            @php $notif = $notifikasi ?? ['total' => 0, 'items' => []]; @endphp
            <details class="notif-menu">
                <summary aria-label="Notifikasi">
                    {!! $ikon('bell', 22) !!}
                    @if ($notif['total'] > 0)
                        <span class="bell-badge">{{ $notif['total'] > 99 ? '99+' : $notif['total'] }}</span>
                    @endif
                </summary>

                <div class="notif-panel">
                    <div class="notif-head">
                        <span>Notifikasi</span>
                        @if ($notif['total'] > 0)
                            <small>{{ $notif['total'] }} perlu ditindak</small>
                        @endif
                    </div>

                    <div class="notif-list">
                        @forelse ($notif['items'] as $n)
                            <a href="{{ $n['url'] }}" class="notif-item">
                                <span class="notif-ikon {{ $n['jenis'] }}">
                                    {!! $ikon($n['jenis'] === 'pesanan' ? 'cart' : 'box', 16) !!}
                                </span>
                                <span class="notif-isi">
                                    <b>{{ $n['judul'] }}</b>
                                    <span>{{ $n['teks'] }}</span>
                                    @if ($n['waktu'])
                                        <time>{{ $n['waktu'] }}</time>
                                    @endif
                                </span>
                            </a>
                        @empty
                            <div class="notif-kosong">Tidak ada notifikasi baru.</div>
                        @endforelse
                    </div>
                </div>
            </details>

            <details class="admin-menu">
                <summary>
                    <span class="avatar">{!! $ikon('user', 20) !!}</span>
                    <span class="nm">{{ $namaAdmin }}</span>
                    {!! $ikon('chevron', 16) !!}
                </summary>

                {{-- MENU PROFIL DAN LOGOUT --}}
                <div class="dropdown">
                    <a href="{{ route('admin.profil') }}">
                        {!! $ikon('user', 17) !!} Profil Saya
                    </a>

                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Apakah Anda yakin ingin keluar?');">
                            @csrf
                            <button type="submit">
                                {!! $ikon('logout', 17) !!} Logout
                            </button>
                        </form>
                    @endif
                </div>
            </details>
        </div>
    </header>

    <!-- SIDEBAR -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <aside class="sidebar">
        <ul class="sidebar-menu">
            @foreach ($menu as [$label, $ikonMenu, $rute, $pola])
                <li @class(['active' => request()->routeIs($pola)])>
                    <a href="{{ Route::has($rute) ? route($rute) : '#' }}">
                        {!! $ikon($ikonMenu, 20) !!}
                        <span>{{ $label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- Hiasan bunga di dasar sidebar -->
        <svg class="hiasan" viewBox="0 0 240 270" preserveAspectRatio="xMinYMax slice" aria-hidden="true">
            <g fill="none" stroke="#E7A9B9" stroke-width="2.2" stroke-linecap="round" opacity=".85">
                <path d="M30 270C30 220 45 180 78 140"/>
                <path d="M60 270C62 235 80 205 112 185"/>
                <path d="M14 270C10 235 22 205 44 185"/>
            </g>

            <g fill="#E7A9B9" opacity=".85">
                <ellipse cx="46" cy="205" rx="6" ry="16" transform="rotate(-35 46 205)"/>
                <ellipse cx="22" cy="228" rx="5" ry="14" transform="rotate(30 22 228)"/>
                <ellipse cx="70" cy="238" rx="6" ry="15" transform="rotate(-50 70 238)"/>
                <ellipse cx="100" cy="212" rx="5" ry="13" transform="rotate(-60 100 212)"/>
                <ellipse cx="38" cy="168" rx="5" ry="13" transform="rotate(25 38 168)"/>
            </g>

            <use href="#bunga" x="55" y="95" width="62" height="62" style="color:#E7A9B9" opacity=".9"/>
            <use href="#bunga" x="2" y="150" width="42" height="42" style="color:#E7A9B9" opacity=".8"/>
            <use href="#bunga" x="90" y="165" width="38" height="38" style="color:#E7A9B9" opacity=".75"/>
        </svg>
    </aside>

    <!-- AREA ISI -->
    <main class="main-content">
        <div class="area-konten">
            @if (session('success'))
                <div class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>

        <footer class="footer-admin">
            <span>Royal Florist &nbsp;•&nbsp; Admin Panel</span>
            <span class="motto">Bunga untuk setiap cerita ♡</span>
        </footer>
    </main>

    <script>
        // Buka/tutup menu samping di layar kecil
        (function () {
            var body = document.body;
            var tombol = document.getElementById('menuToggle');
            var latar = document.getElementById('sidebarBackdrop');

            function atur(buka) {
                body.classList.toggle('menu-open', buka);
                tombol.setAttribute('aria-expanded', buka ? 'true' : 'false');
            }

            tombol.addEventListener('click', function () {
                atur(!body.classList.contains('menu-open'));
            });

            latar.addEventListener('click', function () {
                atur(false);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') atur(false);
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 900) atur(false);
            });
        })();
    </script>

    <script>
        // Popup lonceng dan menu profil memakai <details>. Secara bawaan <details> tidak menutup sendiri
        // saat klik di luar, jadi ditambahkan di sini.
        (function () {
            var menus = document.querySelectorAll('details.notif-menu, details.admin-menu');

            function tutupSemua(kecuali) {
                menus.forEach(function (m) {
                    if (m !== kecuali) m.open = false;
                });
            }

            // Klik di luar menu manapun -> semua tertutup
            document.addEventListener('click', function (e) {
                menus.forEach(function (m) {
                    if (m.open && !m.contains(e.target)) m.open = false;
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') tutupSemua(null);
            });

            // Membuka satu menu otomatis menutup menu yang lain
            menus.forEach(function (m) {
                m.addEventListener('toggle', function () {
                    if (m.open) tutupSemua(m);
                });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>