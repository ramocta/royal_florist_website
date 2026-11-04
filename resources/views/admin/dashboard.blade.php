@php
    $ikon = fn (string $n, int $u = 18): string => \App\Support\Ikon::svg($n, $u);

    /* Menu sidebar. Ganti '#' dengan route(...) kalau halamannya sudah ada. */
    $menu = [
        ['href' => route('admin.dashboard'),       'ikon' => 'home',     'label' => 'Dashboard',          'aktif' => true],
        ['href' => route('admin.laporan.index'),   'ikon' => 'laporan',  'label' => 'Laporan Penjualan',  'aktif' => false],
        ['href' => route('admin.pelanggan.index'), 'ikon' => 'user',     'label' => 'Pelanggan',          'aktif' => false],
        ['href' => route('admin.pesanan.index'),   'ikon' => 'cart',     'label' => 'Pesanan',            'aktif' => false],
        ['href' => route('admin.produk.index'),    'ikon' => 'box',      'label' => 'Produk',             'aktif' => false],
        ['href' => route('admin.stok.index'),      'ikon' => 'database', 'label' => 'Stok',               'aktif' => false],
        ['href' => route('admin.karyawan.index'),  'ikon' => 'users',    'label' => 'Manajemen Staff',    'aktif' => false],
        ['href' => '#',                            'ikon' => 'progress', 'label' => 'Manajemen Progress', 'aktif' => false],
        ['href' => '#',                            'ikon' => 'settings', 'label' => 'Pengaturan',         'aktif' => false],
    ];

    $namaAdmin = auth()->user()->nama ?? auth()->user()->name ?? 'Admin';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Royal Florist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
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
            border-radius: 10px; padding: 6px 12px; color: var(--maroon);
        }
        .date-pill input[type=date] { border: none; background: transparent; font-size: 12.5px; color: var(--teks); outline: none; cursor: pointer; }
        .bell { position: relative; color: var(--maroon); display: flex; }
        .bell::after { content: ''; position: absolute; top: -1px; right: 0; width: 7px; height: 7px; background: #e0446a; border-radius: 50%; border: 1.5px solid var(--pink-bg); }

        .admin-menu { position: relative; }
        .admin-menu summary {
            list-style: none; display: flex; align-items: center; gap: 10px; cursor: pointer;
            font-size: 13px; font-weight: 500; color: var(--maroon);
        }
        .admin-menu summary::-webkit-details-marker { display: none; }
        .avatar { width: 38px; height: 38px; background: var(--maroon); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .dropdown {
            position: absolute; right: 0; top: 48px; min-width: 160px; background: #fff; border: 1px solid var(--pink-garis);
            border-radius: 10px; box-shadow: 0 6px 18px rgba(123, 24, 48, .12); padding: 6px;
        }
        .dropdown a, .dropdown button {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; width: 100%;
            color: var(--maroon); text-decoration: none; font-size: 13px; font-weight: 500;
            background: none; border: none; cursor: pointer; font-family: inherit;
        }
        .dropdown a:hover, .dropdown button:hover { background: var(--pink-bg); }

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
        .main-content { margin: 72px 0 0 240px; min-height: calc(100vh - 72px); display: flex; flex-direction: column; min-width: 0; }
        .container { padding: 24px 28px; flex: 1; }
        .welcome-section h1 { font-size: 27px; color: var(--maroon); font-weight: 600; line-height: 1.2; }
        .welcome-section p { font-size: 12.5px; color: #555; margin: 4px 0 26px; }

        /* Kartu statistik */
        .cards-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
        .card {
            position: relative; overflow: hidden; background: var(--pink-kartu); border: 1px solid var(--pink-garis);
            border-radius: 12px; padding: 16px 18px 14px;
        }
        .card-head { display: flex; align-items: center; gap: 12px; }
        .card-icon { width: 40px; height: 40px; background: #A23A55; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .card-title { font-size: 12.5px; font-weight: 600; color: var(--maroon); }
        .card-value { font-size: 30px; font-weight: 600; color: var(--maroon); margin: 14px 0 16px; line-height: 1.1; position: relative; z-index: 1; }
        .card-trend { display: flex; align-items: center; gap: 6px; font-size: 10.5px; color: var(--abu); position: relative; z-index: 1; }
        .card-trend .naik { display: flex; align-items: center; gap: 4px; color: var(--hijau); font-weight: 600; }
        .card-trend .turun { display: flex; align-items: center; gap: 4px; color: var(--merah); font-weight: 600; }
        .card-trend .turun .ikon { transform: rotate(90deg); }
        .watermark { position: absolute; right: -6px; bottom: -10px; width: 100px; height: 100px; color: var(--maroon); opacity: .08; }

        /* Grafik */
        .chart-card { background: #fff; border: 1px solid var(--pink-garis); border-radius: 12px; padding: 20px 22px; }
        .chart-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; gap: 12px; flex-wrap: wrap; }
        .chart-title { display: flex; align-items: center; gap: 12px; color: var(--maroon); }
        .chart-title h3 { font-size: 20px; font-weight: 600; line-height: 1.2; }
        .chart-title p { font-size: 11px; color: var(--abu); margin-top: 1px; }
        .date-chip {
            display: flex; align-items: center; gap: 8px; border: 1px solid var(--pink-garis); border-radius: 9px;
            padding: 8px 14px; font-size: 12px; color: var(--maroon); background: #fff;
        }
        .chart-wrap { position: relative; height: 300px; width: 100%; }

        /* Footer */
        footer {
            display: flex; justify-content: space-between; align-items: center; padding: 14px 28px 20px;
            font-size: 11px; color: var(--abu);
        }
        footer .motto { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 15px; color: #B24A66; }

        @media (max-width: 1100px) { .cards-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 800px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .brand-teks .slogan, .admin-menu summary .nm { display: none; }
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

    <!-- TOP NAVBAR -->
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
            <!-- Form Filter Tanggal -->
            <form method="GET" action="{{ route('admin.dashboard') }}" id="dateFilterForm" class="date-pill">
                {!! $ikon('calendar', 18) !!}
                <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="document.getElementById('dateFilterForm').submit();">
            </form>

            <span class="bell">{!! $ikon('bell', 22) !!}</span>

            <details class="admin-menu">
                <summary>
                    <span class="avatar">{!! $ikon('user', 20) !!}</span>
                    <span class="nm">{{ $namaAdmin }}</span>
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

    <!-- SIDEBAR -->
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

    <!-- MAIN CONTAINER -->
    <main class="main-content">
        <div class="container">
            <div class="welcome-section">
                <h1>Dashboard Admin</h1>
                <p>Selamat datang, {{ $namaAdmin }}! Berikut ringkasan data penjualan Royal Florist.</p>
            </div>

            <!-- CARDS STATISTIK -->
            <section class="cards-grid">
                @foreach ($kartu as $k)
                <div class="card">
                    <svg class="watermark"><use href="#bunga"/></svg>
                    <div class="card-head">
                        <div class="card-icon">{!! $ikon($k['ikon'], 20) !!}</div>
                        <div class="card-title">{{ $k['judul'] }}</div>
                    </div>
                    <div class="card-value">{{ $k['nilai'] }}</div>
                    <div class="card-trend">
                        @if ($k['info']['teks'] !== '')
                            <span class="{{ $k['info']['kelas'] }}">{!! $ikon('trend', 14) !!}{{ $k['info']['teks'] }}</span>
                        @endif
                        <span>{{ $k['info']['ket'] }}</span>
                    </div>
                </div>
                @endforeach
            </section>

            <!-- GRAFIK PENJUALAN -->
            <section class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        {!! $ikon('batang', 30) !!}
                        <div>
                            <h3>Grafik Penjualan</h3>
                            <p>Data penjualan selama 7 hari terakhir</p>
                        </div>
                    </div>
                    <div class="date-chip">
                        {!! $ikon('calendar', 17) !!}
                        {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                        {!! $ikon('chevron', 15) !!}
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="salesChart"></canvas>
                </div>
            </section>
        </div>

        <!-- FOOTER -->
        <footer>
            <span>Royal Florist &nbsp;•&nbsp; Admin Dashboard</span>
            <span class="motto">Bunga untuk setiap cerita ♡</span>
        </footer>
    </main>

    <!-- SCRIPT CHART.JS -->
    <script>
        Chart.register(ChartDataLabels);

        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($labelsGrafik),
                datasets: [{
                    label: 'Pendapatan',
                    data: @json($dataGrafik),
                    backgroundColor: '#A84860',
                    borderRadius: 3,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 25 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#7B1830',
                        font: { weight: '600', size: 10.5 },
                        formatter: function(value) {
                            if (window.innerWidth < 700) {
                                if (value === 0) return '';
                                return value >= 1000000
                                    ? (value / 1000000).toFixed(1).replace('.0', '').replace('.', ',') + ' jt'
                                    : Math.round(value / 1000) + ' rb';
                            }
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { color: '#e5d6da' },
                        ticks: { color: '#555', font: { size: 10.5 } }
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: 2500000,
                        border: { display: false },
                        grid: { color: '#efe4e7' },
                        ticks: {
                            stepSize: 500000,
                            color: '#555',
                            font: { size: 10.5 },
                            callback: function(value) {
                                if (window.innerWidth < 700) {
                                    return value === 0 ? '0' : (value / 1000000).toString().replace('.', ',') + ' jt';
                                }
                                return value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
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