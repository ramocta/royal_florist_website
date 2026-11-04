@php
    $ikon = fn (string $n, int $u = 18): string => \App\Support\Ikon::svg($n, $u);

    /* Menu sidebar. Ganti '#' dengan route(...) kalau halamannya sudah ada. */
    $menu = [
        ['href' => route('admin.dashboard'),       'ikon' => 'home',     'label' => 'Dashboard',          'aktif' => false],
        ['href' => route('admin.laporan.index'),   'ikon' => 'laporan',  'label' => 'Laporan Penjualan',  'aktif' => true],
        ['href' => route('admin.pelanggan.index'), 'ikon' => 'user',     'label' => 'Pelanggan',          'aktif' => false],
        ['href' => route('admin.pesanan.index'),   'ikon' => 'cart',     'label' => 'Pesanan',            'aktif' => false],
        ['href' => route('admin.produk.index'),    'ikon' => 'box',      'label' => 'Produk',             'aktif' => false],
        ['href' => '#',                            'ikon' => 'database', 'label' => 'Stok',               'aktif' => false],
        ['href' => route('admin.karyawan.index'),  'ikon' => 'users',    'label' => 'Manajemen Staff',    'aktif' => false],
        ['href' => '#',                            'ikon' => 'progress', 'label' => 'Manajemen Progress', 'aktif' => false],
        ['href' => '#',                            'ikon' => 'settings', 'label' => 'Pengaturan',         'aktif' => false],
    ];

    $warnaKategori = ['#7B1830', '#A23A55', '#BF607A', '#D68DA1', '#E8B3C1', '#F5D9E0'];
    $namaAdmin = auth()->user()->nama ?? auth()->user()->name ?? 'Admin';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Royal Florist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --maroon: #7B1830;
            --maroon-gelap: #6A1228;
            --pink-bg: #FBE9ED;
            --pink-kartu: #FAE6EB;
            --pink-garis: #F3CCD6;
            --pink-tabel: #FBE3E9;
            --teks: #2B2B2B;
            --abu: #6F6F6F;
            --hijau: #2E8B57;
            --merah: #C0392B;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #fff; color: var(--teks); min-height: 100vh; }
        .ikon { flex-shrink: 0; display: block; }

        /* ---------- Header atas ---------- */
        .topbar {
            position: fixed; top: 0; left: 0; right: 0; height: 72px; z-index: 20;
            background: var(--pink-bg); display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px 0 24px; border-bottom: 1px solid var(--pink-garis);
        }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--maroon); }
        .brand svg.logo { width: 44px; height: 44px; }
        .brand-teks .nama { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 500; line-height: 1; letter-spacing: .3px; }
        .brand-teks .slogan { font-size: 8.5px; color: #5d3a45; margin-top: 3px; letter-spacing: .2px; }
        .top-kanan { display: flex; align-items: center; gap: 18px; }

        .date-pill {
            display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid var(--pink-garis);
            border-radius: 10px; padding: 6px 10px; color: var(--maroon);
        }
        .date-pill input[type=date] {
            border: none; background: transparent; font-size: 12.5px; color: var(--teks); width: 118px; outline: none;
        }
        .date-pill .sd { font-size: 11px; color: var(--abu); }
        .date-pill button {
            border: none; background: var(--maroon); color: #fff; font-size: 11px; font-weight: 600;
            padding: 5px 10px; border-radius: 7px; cursor: pointer;
        }
        .date-pill button:hover { background: var(--maroon-gelap); }
        .bell { position: relative; color: var(--maroon); display: flex; }
        .bell::after { content: ''; position: absolute; top: -1px; right: 0; width: 7px; height: 7px; background: #e0446a; border-radius: 50%; border: 1.5px solid var(--pink-bg); }
        .admin { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 500; color: var(--maroon); }
        .avatar { width: 38px; height: 38px; background: var(--maroon); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

        /* Dropdown admin + logout */
        .admin-menu { position: relative; }
        .admin-menu summary { list-style: none; cursor: pointer; }
        .admin-menu summary::-webkit-details-marker { display: none; }
        .admin-menu .dropdown {
            position: absolute; right: 0; top: 50px; min-width: 170px; background: #fff;
            border: 1px solid var(--pink-garis); border-radius: 12px; overflow: hidden;
            box-shadow: 0 8px 24px rgba(123, 24, 48, .15); z-index: 30;
        }
        .admin-menu .dropdown button {
            display: flex; align-items: center; gap: 10px; padding: 12px 16px; width: 100%;
            color: var(--maroon); font-size: 13px; font-weight: 600; background: none; border: none; cursor: pointer;
        }
        .admin-menu .dropdown button:hover { background: var(--pink-tabel); }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed; top: 72px; bottom: 0; left: 0; width: 240px; background: var(--maroon);
            color: #fff; display: flex; flex-direction: column; overflow: hidden; z-index: 10;
        }
        .sidebar-menu { list-style: none; padding: 18px 0 0; position: relative; z-index: 2; }
        .sidebar-menu li { margin: 2px 12px; }
        .sidebar-menu li a {
            display: flex; align-items: center; gap: 14px; padding: 11px 16px; border-radius: 12px;
            color: rgba(255, 255, 255, .78); text-decoration: none; font-size: 13px; transition: .2s;
        }
        .sidebar-menu li a:hover { background: rgba(255, 255, 255, .1); color: #fff; }
        .sidebar-menu li.active a { background: #FCEEF1; color: var(--maroon); font-weight: 600; box-shadow: 0 2px 6px rgba(0, 0, 0, .12); }
        .hiasan { position: absolute; left: 0; bottom: 0; width: 100%; height: 270px; z-index: 1; pointer-events: none; }

        /* ---------- Konten ---------- */
        .main-content { margin: 72px 0 0 240px; padding: 22px 26px 30px; min-width: 0; }
        .page-title-area { display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; flex-wrap: wrap; margin-bottom: 16px; }
        .page-title-area h1 { font-size: 26px; color: var(--maroon); font-weight: 600; line-height: 1.2; }
        .page-title-area p { font-size: 12px; color: var(--abu); margin-top: 2px; }
        .btn-group { display: flex; gap: 10px; }
        .btn-action {
            display: inline-flex; align-items: center; gap: 8px; background: var(--pink-tabel); color: var(--maroon);
            border: 1px solid var(--pink-garis); padding: 9px 18px; border-radius: 9px; font-size: 12px; font-weight: 500; cursor: pointer;
        }
        .btn-action:hover { background: #f6d3dc; }
        a.btn-action { text-decoration: none; }

        /* Kartu ringkasan */
        .cards-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 16px; }
        .card-stat {
            position: relative; overflow: hidden; background: var(--pink-kartu); border: 1px solid var(--pink-garis);
            border-radius: 12px; padding: 14px 16px 12px;
        }
        .card-head { display: flex; align-items: center; gap: 10px; }
        .card-icon { width: 36px; height: 36px; background: var(--maroon); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .card-head .title { font-size: 12.5px; font-weight: 600; color: var(--maroon); }
        .card-stat .value { font-size: 28px; font-weight: 600; color: var(--maroon); margin: 8px 0 4px; line-height: 1.1; position: relative; z-index: 1; }
        .tren { display: flex; align-items: center; gap: 5px; font-size: 10.5px; position: relative; z-index: 1; }
        .tren span { color: var(--abu); }
        .tren.naik { color: var(--hijau); }
        .tren.turun { color: var(--merah); }
        .tren.turun .ikon { transform: rotate(90deg); }
        .watermark { position: absolute; right: -6px; bottom: -10px; width: 90px; height: 90px; color: var(--maroon); opacity: .08; }

        /* Grafik */
        .charts-row { display: grid; grid-template-columns: 1.75fr 1fr; gap: 16px; margin-bottom: 16px; }
        .chart-box { background: #fff; border: 1px solid var(--pink-garis); border-radius: 12px; padding: 16px 18px; }
        .chart-title { display: flex; align-items: center; gap: 8px; color: var(--maroon); font-weight: 600; font-size: 17px; }
        .chart-sub { font-size: 11px; color: var(--abu); margin: 1px 0 8px 30px; }
        .chart-wrap { height: 230px; position: relative; }

        .donut-area { display: flex; align-items: center; gap: 14px; margin-top: 12px; }
        .donut-area .donut { position: relative; width: 190px; height: 190px; flex-shrink: 0; }
        .legend { list-style: none; flex: 1; min-width: 0; }
        .legend li { display: flex; align-items: center; gap: 8px; font-size: 11.5px; padding: 6px 0; color: var(--teks); }
        .legend .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .legend .nm { flex: 1; }
        .legend .jml { color: var(--abu); font-size: 11px; white-space: nowrap; }

        /* Tabel */
        .table-box { background: #fff; border: 1px solid var(--pink-garis); border-radius: 12px; padding: 16px 18px; overflow-x: auto; }
        .table-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
        .search-box {
            display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; border-radius: 9px;
            padding: 7px 12px; color: #555; min-width: 220px;
        }
        .search-box input { border: none; outline: none; font-size: 12px; width: 100%; background: transparent; }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--pink-tabel); color: var(--maroon); font-size: 11.5px; font-weight: 600; padding: 10px 12px; text-align: left; }
        th:first-child { border-radius: 8px 0 0 8px; }
        th:last-child { border-radius: 0 8px 8px 0; }
        td { font-size: 11.5px; padding: 10px 12px; border-bottom: 1px solid #eee; color: #444; }
        .kosong { text-align: center; color: #999; padding: 25px; }
        .table-foot { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 11px; color: var(--abu); flex-wrap: wrap; gap: 10px; }
        .paging { display: flex; gap: 6px; }
        .paging span { min-width: 26px; height: 26px; padding: 0 6px; border: 1px solid #e3e3e3; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11.5px; color: #555; }
        .paging { flex-wrap: wrap; }
        .paging a { min-width: 26px; height: 26px; padding: 0 6px; border: 1px solid #e3e3e3; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11.5px; color: #555; text-decoration: none; }
        .paging a:hover { background: var(--pink-tabel); border-color: var(--pink-garis); color: var(--maroon); }
        .paging span.aktif { background: var(--maroon); border-color: var(--maroon); color: #fff; }
        .paging span.nonaktif { opacity: .4; }
        .paging span.titik { border: none; }

        @media (max-width: 1100px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 800px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .date-pill input[type=date] { width: 100px; }
            .brand-teks .slogan, .admin span { display: none; }
        }

        @media print {
            .sidebar, .btn-group, .topbar .top-kanan, .search-box, .paging { display: none !important; }
            .topbar { position: static; border: none; }
            .main-content { margin: 0; padding: 10px 0; }
        }

        /* ---------- Responsif (tablet & HP) ---------- */
        .menu-toggle {
            display: none; width: 40px; height: 40px; align-items: center; justify-content: center; flex-shrink: 0;
            background: #fff; border: 1px solid var(--pink-garis); border-radius: 10px; color: var(--maroon); cursor: pointer;
        }
        .sidebar-backdrop { display: none; }

        @media screen and (max-width: 900px) {
            body.menu-open { overflow: hidden; }

            /* Header: baris 1 = menu, logo, lonceng, admin. Baris 2 = filter tanggal */
            .topbar { position: sticky; height: auto; min-height: 60px; padding: 8px 14px; flex-wrap: wrap; gap: 8px 10px; }
            .top-kanan { display: contents; }
            .menu-toggle { display: flex; order: 0; }
            .brand { order: 1; margin-right: auto; }
            .bell { order: 2; }
            .admin-menu { order: 3; }
            .date-pill { order: 4; flex: 1 1 100%; flex-wrap: wrap; justify-content: center; }
            .date-pill input[type=date] { flex: 1 1 110px; min-width: 0; width: auto; font-size: 14px; }
            .date-pill button { flex: 1 1 100%; padding: 8px; }
            .brand-teks .slogan { display: none; }
            .brand-teks .nama { font-size: 24px; }
            .brand svg.logo { width: 34px; height: 34px; }

            /* Sidebar jadi menu geser (drawer) */
            .sidebar { display: flex !important; top: 0; width: 260px; max-width: 84vw; padding-top: 8px; z-index: 50; transform: translateX(-105%); transition: transform .25s ease; }
            body.menu-open .sidebar { transform: none; box-shadow: 6px 0 24px rgba(0, 0, 0, .25); }
            .sidebar-backdrop { display: block; position: fixed; inset: 0; z-index: 45; background: rgba(43, 12, 20, .45); opacity: 0; pointer-events: none; transition: opacity .25s; }
            body.menu-open .sidebar-backdrop { opacity: 1; pointer-events: auto; }

            /* Konten */
            .main-content { margin: 0 !important; min-height: 0; padding: 16px 14px 24px; }
            .container { padding: 0; }
            .welcome-section h1, .page-title-area h1 { font-size: 22px; }
            .welcome-section p { margin-bottom: 18px; }
            .cards-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .card-value, .card-stat .value { font-size: 24px; }
            .chart-card, .chart-box, .table-box { padding: 14px; }
            .chart-title h3 { font-size: 17px; }
            .chart-wrap { height: 260px; }
            .btn-group { width: 100%; }
            .btn-action { flex: 1; justify-content: center; }
            .search-box { min-width: 0; width: 100%; }
            table { min-width: 640px; }
            .table-foot { justify-content: center; }
            footer { flex-direction: column; gap: 4px; text-align: center; padding: 12px 14px 20px; }
        }

        @media screen and (max-width: 600px) {
            .admin-menu summary span:not(.avatar) { display: none; }
        }

        @media screen and (max-width: 520px) {
            .cards-grid { grid-template-columns: 1fr; }
            .donut-area { flex-direction: column; align-items: stretch; }
            .donut-area .donut { margin: 0 auto; }
            .chart-sub { margin-left: 0; }
        }
    </style>
</head>
<body>

    <!-- Aset gambar bunga (dipakai ulang) -->
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

    <header class="topbar">
        <button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka menu" aria-expanded="false">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
        </button>
        <a href="{{ route('admin.dashboard') }}" class="brand">
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
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="date-pill" title="Pilih periode laporan">
                {!! $ikon('calendar', 18) !!}
                <input type="date" name="start_date" value="{{ $startDate }}" aria-label="Dari tanggal">
                <span class="sd">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate }}" aria-label="Sampai tanggal">
                @if ($search !== '')
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif
                <button type="submit">Terapkan</button>
            </form>
            <span class="bell">{!! $ikon('bell', 22) !!}</span>

            <!-- Menu admin + Logout -->
            <details class="admin-menu">
                <summary class="admin">
                    <div class="avatar">{!! $ikon('user', 20) !!}</div>
                    <span>{{ $namaAdmin }}</span>
                    {!! $ikon('chevron', 16) !!}
                </summary>
                <div class="dropdown">
                    <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Apakah Anda yakin ingin keluar?');">
                        @csrf
                        <button type="submit">{!! $ikon('logout', 17) !!} Logout</button>
                    </form>
                </div>
            </details>
        </div>
    </header>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <aside class="sidebar">
        <ul class="sidebar-menu">
            @foreach ($menu as $item)
                <li @class(['active' => $item['aktif'] ?? false])>
                    <a href="{{ $item['href'] }}">{!! $ikon($item['ikon'], 20) !!}<span>{{ $item['label'] }}</span></a>
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

    <main class="main-content">
        <div class="page-title-area">
            <div>
                <h1>Laporan Penjualan</h1>
                <p>Berikut adalah ringkasan dan detail penjualan Royal Florist periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}.</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.laporan.pdf', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search])) }}" class="btn-action" title="Unduh laporan lengkap periode ini sebagai file PDF">{!! $ikon('download', 17) !!} Export PDF</a>
                <button type="button" onclick="window.print()" class="btn-action">{!! $ikon('printer', 17) !!} Cetak</button>
            </div>
        </div>

        <section class="cards-grid">
            @foreach ($kartu as $k)
            <div class="card-stat">
                <svg class="watermark"><use href="#bunga"/></svg>
                <div class="card-head">
                    <div class="card-icon">{!! $ikon($k['ikon'], 19) !!}</div>
                    <div class="title">{{ $k['judul'] }}</div>
                </div>
                <div class="value">{{ $k['nilai'] }}</div>
                @if ($k['tren'] === null)
                    <div class="tren netral"><span>Belum ada pembanding</span></div>
                @else
                    <div class="tren {{ $k['tren'] >= 0 ? 'naik' : 'turun' }}">
                        {!! $ikon('trend', 14) !!}<b>{{ $k['tren'] >= 0 ? '+' : '' }}{{ $k['tren'] }}%</b><span>dari periode sebelumnya</span>
                    </div>
                @endif
            </div>
            @endforeach
        </section>

        <section class="charts-row">
            <div class="chart-box">
                <div class="chart-title">{!! $ikon('aktivitas', 22) !!} Grafik Penjualan</div>
                <div class="chart-sub">Total penjualan per hari</div>
                <div class="chart-wrap"><canvas id="lineChartPenjualan"></canvas></div>
            </div>
            <div class="chart-box">
                <div class="chart-title">{!! $ikon('jam', 22) !!} Kategori Produk Terlaris</div>
                <div class="donut-area">
                    <div class="donut"><canvas id="doughnutChartKategori"></canvas></div>
                    <ul class="legend">
                        @foreach ($kategoriLabels as $i => $nama)
                            @php
                                $jml = $kategoriData[$i];
                                $persen = $totalKategori > 0 ? round($jml / $totalKategori * 100) : 0;
                            @endphp
                            <li>
                                <span class="dot" style="background: {{ $warnaKategori[$i % count($warnaKategori)] }}"></span>
                                <span class="nm">{{ $nama }}</span>
                                <span class="jml">{{ $jml }} ({{ $persen }}%)</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <section class="table-box">
            <div class="table-head">
                <div class="chart-title">{!! $ikon('tas', 22) !!} Detail Penjualan</div>
                <form method="GET" action="{{ route('admin.laporan.index') }}" class="search-box">
                    {!! $ikon('cari', 16) !!}
                    <input type="text" name="search" placeholder="Cari produk..." value="{{ $search }}" aria-label="Cari produk">
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Jumlah Terjual</th>
                        <th>Harga Satuan</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($listDetail as $row)
                        <tr>
                            <td>{{ $listDetail->firstItem() + $loop->index }}</td>
                            <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $row->nama_produk }}</td>
                            <td>{{ $row->kategori }}</td>
                            <td>{{ (int) $row->jumlah }}</td>
                            <td>Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format((float) $row->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="kosong">Data penjualan tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="table-foot">
                <div>
                    @if ($listDetail->total() > 0)
                        Menampilkan {{ $listDetail->firstItem() }}&ndash;{{ $listDetail->lastItem() }} dari {{ $listDetail->total() }} data
                    @else
                        Tidak ada data
                    @endif
                </div>
                @include('admin.partials.pagination', ['paginator' => $listDetail])
            </div>
        </section>
    </main>

    <script>
        const rupiah = v => 'Rp ' + Number(v).toLocaleString('id-ID');
        const dataPenjualan = @json($dataGrafik);

        // Angka di atas titik grafik (hanya kalau titiknya tidak terlalu banyak)
        const labelTitik = {
            id: 'labelTitik',
            afterDatasetsDraw(chart) {
                if (dataPenjualan.length > 10 || window.innerWidth < 700) return;
                const { ctx } = chart;
                ctx.save();
                ctx.font = '600 10px Inter, sans-serif';
                ctx.fillStyle = '#7B1830';
                ctx.textAlign = 'center';
                chart.getDatasetMeta(0).data.forEach((titik, i) => {
                    ctx.fillText(rupiah(dataPenjualan[i]), titik.x, titik.y - 10);
                });
                ctx.restore();
            }
        };

        new Chart(document.getElementById('lineChartPenjualan').getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($labelsGrafik),
                datasets: [{
                    label: 'Penjualan',
                    data: dataPenjualan,
                    borderColor: '#7B1830',
                    borderWidth: 1.8,
                    backgroundColor: 'rgba(245, 205, 216, 0.55)',
                    fill: true,
                    tension: 0,
                    pointRadius: 3.5,
                    pointBackgroundColor: '#7B1830',
                    pointBorderColor: '#7B1830'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 22, right: 28 } },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => rupiah(c.parsed.y) } }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: '#666', font: { size: 10 } } },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: '#f0e3e7' },
                        ticks: { color: '#666', font: { size: 10 }, maxTicksLimit: 5, callback: v => window.innerWidth < 700 ? (v >= 1000000 ? (v / 1000000).toString().replace('.', ',') + ' jt' : (v / 1000) + ' rb') : rupiah(v) }
                    }
                }
            },
            plugins: [labelTitik]
        });

        // Teks di tengah donat
        const teksTengah = {
            id: 'teksTengah',
            afterDraw(chart) {
                const { ctx, chartArea: a } = chart;
                const x = (a.left + a.right) / 2, y = (a.top + a.bottom) / 2;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.fillStyle = '#7B1830';
                ctx.font = '600 11px Inter, sans-serif';
                ctx.fillText('Total Terjual', x, y - 14);
                ctx.font = '700 24px Inter, sans-serif';
                ctx.fillText(@json((string) $totalKategori), x, y + 10);
                ctx.font = '400 11px Inter, sans-serif';
                ctx.fillText('produk', x, y + 26);
                ctx.restore();
            }
        };

        new Chart(document.getElementById('doughnutChartKategori').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json($kategoriLabels),
                datasets: [{
                    data: @json($kategoriData),
                    backgroundColor: @json($warnaKategori),
                    borderColor: '#fff',
                    borderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: { legend: { display: false } }
            },
            plugins: [teksTengah]
        });
    </script>
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
            tombol.addEventListener('click', function () { atur(!body.classList.contains('menu-open')); });
            latar.addEventListener('click', function () { atur(false); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') atur(false); });
            window.addEventListener('resize', function () { if (window.innerWidth > 900) atur(false); });
        })();
    </script>
</body>
</html>