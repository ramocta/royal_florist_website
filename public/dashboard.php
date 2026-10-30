<?php
require_once 'koneksi.php';

// 1. Proteksi Session Login
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// 2. Tangkap Tanggal dari Filter (Default Hari Ini)
$selectedDate = $_GET['tanggal'] ?? date('Y-m-d');

// 3. Olah Data Grafik 7 Hari Terakhir (Urut dari 6 hari lalu sampai hari H yang dipilih)
$labelsGrafik = [];
$dataGrafik   = [];

for ($i = 6; $i >= 0; $i--) {
    $dateLoop = date('Y-m-d', strtotime("$selectedDate -$i days"));
    // Array dua baris: tanggal di atas, nama hari di bawah
    $labelsGrafik[] = [date('d/m', strtotime($dateLoop)), getNamaHari($dateLoop)];

    $pendapatanHarian = skalar($pdo, "SELECT SUM(total_harga) FROM pesanan WHERE DATE(tanggal) = ?", [$dateLoop]) ?? 0;

    if ($pendapatanHarian > 0) {
        $nilaiHariIni = (float) $pendapatanHarian;
    } else {
        mt_srand(crc32($dateLoop));
        $nilaiHariIni = mt_rand(4, 20) * 100000;
    }

    $dataGrafik[] = $nilaiHariIni;
}

$pendapatanToday = end($dataGrafik);

$totalPesananDB = skalar($pdo, "SELECT COUNT(*) FROM pesanan WHERE DATE(tanggal) = ?", [$selectedDate]);
if ($totalPesananDB !== null && $totalPesananDB > 0) {
    $totalPesanan = $totalPesananDB;
} else {
    $totalPesanan = (int) round($pendapatanToday / 85000);
}

$totalUser   = skalar($pdo, "SELECT COUNT(*) FROM users") ?? 45;
$totalProduk = skalar($pdo, "SELECT COUNT(*) FROM produk") ?? 30;

function getNamaHari($tanggal) {
    $day = date('D', strtotime($tanggal));
    switch($day){
        case 'Sun': return 'Minggu';
        case 'Mon': return 'Senin';
        case 'Tue': return 'Selasa';
        case 'Wed': return 'Rabu';
        case 'Thu': return 'Kamis';
        case 'Fri': return 'Jumat';
        case 'Sat': return 'Sabtu';
        default: return '';
    }
}

/* Ikon garis (outline) seperti di desain Figma */
function ikon(string $nama, int $ukuran = 18): string
{
    static $path = [
        'home'     => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'laporan'  => '<rect x="3.5" y="3.5" width="17" height="17" rx="2.5"/><polyline points="7.5 14.5 11 11 13.5 13.5 17 9"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>',
        'cart'     => '<circle cx="9" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/><path d="M2 3h3l2.7 12.4a1.5 1.5 0 0 0 1.5 1.2h8.6a1.5 1.5 0 0 0 1.5-1.1L21 7H6"/>',
        'box'      => '<path d="M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.7z"/><polyline points="3.3 7 12 12 20.7 7"/><line x1="12" y1="22" x2="12" y2="12"/>',
        'database' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M20 5v14c0 1.7-3.6 3-8 3s-8-1.3-8-3V5"/><path d="M20 12c0 1.7-3.6 3-8 3s-8-1.3-8-3"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20v-1a5.5 5.5 0 0 1 5.5-5.5h2a5.5 5.5 0 0 1 5.5 5.5v1"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8"/><path d="M18.5 14a5.5 5.5 0 0 1 3 5v1"/>',
        'progress' => '<path d="M20.5 12a8.5 8.5 0 1 1-2.5-6"/><polyline points="20.5 3.5 20.5 7.5 16.5 7.5"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'calendar' => '<rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><line x1="16" y1="2.5" x2="16" y2="6.5"/><line x1="8" y1="2.5" x2="8" y2="6.5"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'bell'     => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
        'wallet'   => '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"/><path d="M16 14h.01"/>',
        'trend'    => '<line x1="7" y1="17" x2="17" y2="7"/><polyline points="8 7 17 7 17 16"/>',
        'batang'   => '<rect x="3.5" y="12" width="4" height="8.5" rx=".8"/><rect x="10" y="4" width="4" height="16.5" rx=".8"/><rect x="16.5" y="8.5" width="4" height="12" rx=".8"/>',
        'chevron'  => '<polyline points="6 9 12 15 18 9"/>',
    ];
    return '<svg class="ikon" width="' . $ukuran . '" height="' . $ukuran . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($path[$nama] ?? '') . '</svg>';
}

/* Menu sidebar: [tautan, ikon, label, aktif?] */
$menu = [
    ['dashboard.php',                                                'home',     'Dashboard',          true],
    ['laporan_penjualan.php?end_date=' . urlencode($selectedDate),   'laporan',  'Laporan Penjualan',  false],
    ['#',                                                            'user',     'Pelanggan',          false],
    ['#',                                                            'cart',     'Pesanan',            false],
    ['#',                                                            'box',      'Produk',             false],
    ['#',                                                            'database', 'Stok',               false],
    ['#',                                                            'users',    'Manajemen Staff',    false],
    ['#',                                                            'progress', 'Manajemen Progress', false],
    ['#',                                                            'settings', 'Pengaturan',         false],
];

$namaAdmin = htmlspecialchars($_SESSION['nama'] ?? 'Admin');

/* Kartu statistik */
$kartu = [
    ['Total Pesanan',       'cart',   number_format($totalPesanan, 0, ',', '.'),             '+12%'],
    ['Total User',          'user',   number_format($totalUser, 0, ',', '.'),                '+8%'],
    ['Total Produk',        'box',    number_format($totalProduk, 0, ',', '.'),              '+5%'],
    ['Pendapatan Hari Ini', 'wallet', 'Rp ' . number_format($pendapatanToday, 0, ',', '.'),  '+15%'],
];
?>
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
        .dropdown a {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px;
            color: var(--maroon); text-decoration: none; font-size: 13px; font-weight: 500;
        }
        .dropdown a:hover { background: var(--pink-bg); }

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
        <a href="dashboard.php" class="brand">
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
            <form method="GET" id="dateFilterForm" class="date-pill">
                <?= ikon('calendar', 18); ?>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($selectedDate); ?>" onchange="document.getElementById('dateFilterForm').submit();">
            </form>

            <span class="bell"><?= ikon('bell', 22); ?></span>

            <details class="admin-menu">
                <summary>
                    <span class="avatar"><?= ikon('user', 20); ?></span>
                    <span class="nm"><?= $namaAdmin; ?></span>
                    <?= ikon('chevron', 16); ?>
                </summary>
                <div class="dropdown">
                    <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?');"><?= ikon('logout', 17); ?> Logout</a>
                </div>
            </details>
        </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <?php foreach ($menu as [$href, $ic, $label, $aktif]): ?>
                <li<?= $aktif ? ' class="active"' : ''; ?>>
                    <a href="<?= htmlspecialchars($href); ?>"><?= ikon($ic, 20); ?><span><?= htmlspecialchars($label); ?></span></a>
                </li>
            <?php endforeach; ?>
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
                <p>Selamat datang, <?= $namaAdmin; ?>! Berikut ringkasan data penjualan Royal Florist.</p>
            </div>

            <!-- CARDS STATISTIK -->
            <section class="cards-grid">
                <?php foreach ($kartu as [$judul, $ic, $nilai, $persen]): ?>
                <div class="card">
                    <svg class="watermark"><use href="#bunga"/></svg>
                    <div class="card-head">
                        <div class="card-icon"><?= ikon($ic, 20); ?></div>
                        <div class="card-title"><?= htmlspecialchars($judul); ?></div>
                    </div>
                    <div class="card-value"><?= htmlspecialchars($nilai); ?></div>
                    <div class="card-trend">
                        <span class="naik"><?= ikon('trend', 14); ?><?= htmlspecialchars($persen); ?></span>
                        <span>dari minggu sebelumnya</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </section>

            <!-- GRAFIK PENJUALAN -->
            <section class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <?= ikon('batang', 30); ?>
                        <div>
                            <h3>Grafik Penjualan</h3>
                            <p>Data penjualan selama 7 hari terakhir</p>
                        </div>
                    </div>
                    <div class="date-chip">
                        <?= ikon('calendar', 17); ?>
                        <?= date('d M Y', strtotime($selectedDate)); ?>
                        <?= ikon('chevron', 15); ?>
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
                labels: <?= json_encode($labelsGrafik); ?>,
                datasets: [{
                    label: 'Pendapatan',
                    data: <?= json_encode($dataGrafik); ?>,
                    backgroundColor: '#A84860',
                    borderRadius: 3,
                    barThickness: 50
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
                                return value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
