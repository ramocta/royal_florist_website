<?php
require_once 'koneksi.php';

// Mulai sesi hanya kalau koneksi.php belum memulainya
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

/* ------------------------------------------------------------------
   MODE DEMO
   true  = kalau tabel masih kosong, tampilkan data contoh (untuk presentasi)
   false = tampilkan data asli apa adanya (nol kalau memang belum ada)
------------------------------------------------------------------- */
$modeDemo = true;

/* ------------------------- Fungsi bantu --------------------------- */

// Jaga-jaga kalau skalar() ternyata hanya ada di dashboard.php
if (!function_exists('skalar')) {
    function skalar(PDO $pdo, string $sql, array $params = [])
    {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $v = $stmt->fetchColumn();
            return $v === false ? null : $v;
        } catch (PDOException $e) {
            return null;
        }
    }
}

function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function tanggalValid($d): bool
{
    $x = DateTime::createFromFormat('Y-m-d', (string) $d);
    return $x && $x->format('Y-m-d') === $d;
}

function getNamaHariSingkat($tanggal): string
{
    $days = ['Sun' => 'Ming', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
    return $days[date('D', strtotime($tanggal))] ?? '';
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
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'printer'  => '<polyline points="6 9 6 3 18 3 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7"/>',
        'calendar' => '<rect x="3" y="4.5" width="18" height="16.5" rx="2.5"/><line x1="16" y1="2.5" x2="16" y2="6.5"/><line x1="8" y1="2.5" x2="8" y2="6.5"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'bell'     => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
        'wallet'   => '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"/><path d="M16 14h.01"/>',
        'trend'    => '<line x1="7" y1="17" x2="17" y2="7"/><polyline points="8 7 17 7 17 16"/>',
        'aktivitas'=> '<polyline points="3 17 9 11 13 15 21 6"/><polyline points="15 6 21 6 21 12"/>',
        'jam'      => '<circle cx="12" cy="12" r="9.5"/><polyline points="12 6.5 12 12 15.5 14"/>',
        'tas'      => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'cari'     => '<circle cx="11" cy="11" r="7.5"/><line x1="21" y1="21" x2="16.7" y2="16.7"/>',
        'chevron'  => '<polyline points="6 9 12 15 18 9"/>',
    ];
    return '<svg class="ikon" width="' . $ukuran . '" height="' . $ukuran . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($path[$nama] ?? '') . '</svg>';
}

/* Persentase perubahan dibanding periode sebelumnya (null kalau tidak bisa dihitung) */
function perubahan($sekarang, $sebelumnya, $cadanganDemo = null)
{
    if ($sebelumnya > 0) {
        return (int) round((($sekarang - $sebelumnya) / $sebelumnya) * 100);
    }
    return $cadanganDemo;
}

/* ------------------------- Filter tanggal ------------------------- */

$endDate = $_GET['end_date'] ?? date('Y-m-d');
if (!tanggalValid($endDate)) {
    $endDate = date('Y-m-d');
}

$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-6 days', strtotime($endDate)));
if (!tanggalValid($startDate)) {
    $startDate = date('Y-m-d', strtotime('-6 days', strtotime($endDate)));
}

if ($startDate > $endDate) {
    [$startDate, $endDate] = [$endDate, $startDate];
}

// Maksimal 31 hari supaya grafik tetap terbaca
$jumlahHari = (int) ((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
$jumlahHari = min($jumlahHari, 31);
$startDate  = date('Y-m-d', strtotime("$endDate -" . ($jumlahHari - 1) . " days"));

// Periode pembanding (periode sebelumnya dengan panjang hari yang sama)
$prevEnd   = date('Y-m-d', strtotime("$startDate -1 day"));
$prevStart = date('Y-m-d', strtotime("$prevEnd -" . ($jumlahHari - 1) . " days"));

/* --------------------------- Data grafik -------------------------- */

$labelsGrafik = [];
$dataGrafik   = [];

for ($i = $jumlahHari - 1; $i >= 0; $i--) {
    $dateLoop = date('Y-m-d', strtotime("$endDate -$i days"));
    // Label dua baris seperti desain: nama hari, lalu tanggal
    $labelsGrafik[] = [getNamaHariSingkat($dateLoop), date('d/m', strtotime($dateLoop))];

    $pendapatanHarian = (float) (skalar($pdo, "SELECT SUM(total_harga) FROM pesanan WHERE DATE(tanggal) = ?", [$dateLoop]) ?? 0);

    if ($pendapatanHarian <= 0 && $modeDemo) {
        mt_srand(crc32($dateLoop));
        $pendapatanHarian = mt_rand(5, 18) * 100000;
    }
    $dataGrafik[] = $pendapatanHarian;
}

/* ------------------------- Kartu ringkasan ------------------------ */

$totalPendapatan = array_sum($dataGrafik);

$totalTransaksi = (int) (skalar($pdo, "SELECT COUNT(*) FROM pesanan WHERE DATE(tanggal) BETWEEN ? AND ?", [$startDate, $endDate]) ?: ($modeDemo ? 28 : 0));
$pelangganBaru  = (int) (skalar($pdo, "SELECT COUNT(*) FROM users WHERE DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate]) ?: ($modeDemo ? 12 : 0));
$totalProdukTerjual = (int) (skalar($pdo, "SELECT SUM(jumlah) FROM detail_pesanan") ?: ($modeDemo ? 62 : 0));

// Perubahan dibanding periode sebelumnya
$pendapatanPrev = (float) (skalar($pdo, "SELECT SUM(total_harga) FROM pesanan WHERE DATE(tanggal) BETWEEN ? AND ?", [$prevStart, $prevEnd]) ?? 0);
$transaksiPrev  = (int) (skalar($pdo, "SELECT COUNT(*) FROM pesanan WHERE DATE(tanggal) BETWEEN ? AND ?", [$prevStart, $prevEnd]) ?? 0);
$pelangganPrev  = (int) (skalar($pdo, "SELECT COUNT(*) FROM users WHERE DATE(created_at) BETWEEN ? AND ?", [$prevStart, $prevEnd]) ?? 0);

$tren = [
    'pendapatan' => perubahan($totalPendapatan, $pendapatanPrev, $modeDemo ? 15 : null),
    'transaksi'  => perubahan($totalTransaksi, $transaksiPrev, $modeDemo ? 12 : null),
    'pelanggan'  => perubahan($pelangganBaru, $pelangganPrev, $modeDemo ? 33 : null),
    'produk'     => $modeDemo ? 18 : null, // belum ada pembanding per periode untuk detail_pesanan
];

$kategoriTerlaris = [
    'labels' => ['Bucket', 'Bunga', 'Hantaran Box Akrilik', 'Hantaran Box Fiber', 'Papan Bunga', 'Lainnya'],
    'data'   => [22, 15, 10, 8, 5, 2],
];
$warnaKategori  = ['#7B1830', '#A23A55', '#BF607A', '#D68DA1', '#E8B3C1', '#F5D9E0'];
$totalKategori  = array_sum($kategoriTerlaris['data']);

/* ------------------------- Tabel detail --------------------------- */

$querySearch = trim($_GET['search'] ?? '');
$listDetail  = [];

try {
    if ($querySearch !== '') {
        $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE nama_produk LIKE ? ORDER BY tanggal DESC LIMIT 5");
        $stmt->execute(["%$querySearch%"]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pesanan ORDER BY tanggal DESC LIMIT 5");
        $stmt->execute();
    }
    $listDetail = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $listDetail = [];
}

// Samakan nama kolom supaya tidak muncul "Undefined index"
$listDetail = array_map(function ($row) {
    $jumlah = (int) ($row['jumlah'] ?? 1);
    $harga  = (float) ($row['harga_satuan'] ?? 0);
    return [
        'tanggal'      => $row['tanggal'] ?? '',
        'nama_produk'  => $row['nama_produk'] ?? '-',
        'kategori'     => $row['kategori'] ?? '-',
        'jumlah'       => $jumlah,
        'harga_satuan' => $harga,
        'total'        => (float) ($row['total'] ?? $row['total_harga'] ?? ($jumlah * $harga)),
    ];
}, $listDetail);

// Data contoh dipakai kalau database kosong dan mode demo aktif (tetap bisa dicari)
if (empty($listDetail) && $modeDemo) {
    $listDetail = [
        ['tanggal' => '2025-09-22', 'nama_produk' => 'Bunga Aster',                'kategori' => 'Bunga',                'jumlah' => 8, 'harga_satuan' => 25000,  'total' => 200000],
        ['tanggal' => '2025-09-21', 'nama_produk' => 'Bucket Fresh Flower',        'kategori' => 'Bucket',               'jumlah' => 5, 'harga_satuan' => 150000, 'total' => 750000],
        ['tanggal' => '2025-09-20', 'nama_produk' => 'Box Akrilik [30 x 40 cm]',   'kategori' => 'Hantaran Box Akrilik', 'jumlah' => 4, 'harga_satuan' => 120000, 'total' => 480000],
        ['tanggal' => '2025-09-19', 'nama_produk' => 'Bucket Snack',               'kategori' => 'Bucket',               'jumlah' => 7, 'harga_satuan' => 85000,  'total' => 595000],
        ['tanggal' => '2025-09-18', 'nama_produk' => 'Papan Bunga Kecil',          'kategori' => 'Papan Bunga',          'jumlah' => 3, 'harga_satuan' => 100000, 'total' => 300000],
    ];

    // Saring data contoh sesuai kata pencarian (nama produk atau kategori)
    if ($querySearch !== '') {
        $listDetail = array_values(array_filter($listDetail, function ($r) use ($querySearch) {
            return stripos($r['nama_produk'], $querySearch) !== false
                || stripos($r['kategori'], $querySearch) !== false;
        }));
    }
}

/* ------------------------- Menu sidebar --------------------------- */

$menu = [
    ['dashboard.php',         'home',     'Dashboard',          false],
    ['laporan_penjualan.php', 'laporan',  'Laporan Penjualan',  true],
    ['#',                     'user',     'Pelanggan',          false],
    ['#',                     'cart',     'Pesanan',            false],
    ['#',                     'box',      'Produk',             false],
    ['#',                     'database', 'Stok',               false],
    ['#',                     'users',    'Manajemen Staff',    false],
    ['#',                     'progress', 'Manajemen Progress', false],
    ['#',                     'settings', 'Pengaturan',         false],
    ['logout.php',            'logout',   'Keluar',             false],
];

/* Kartu tren: tampilkan panah + persen */
function badgeTren($nilai): string
{
    if ($nilai === null) {
        return '<div class="tren netral"><span>Belum ada pembanding</span></div>';
    }
    $kelas = $nilai >= 0 ? 'naik' : 'turun';
    $tanda = $nilai >= 0 ? '+' : '';
    return '<div class="tren ' . $kelas . '">' . ikon('trend', 14) . '<b>' . $tanda . $nilai . '%</b><span>dari periode sebelumnya</span></div>';
}
?>
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
        td b { font-weight: 600; }
        .kosong { text-align: center; color: #999; padding: 25px; }
        .table-foot { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 11px; color: var(--abu); flex-wrap: wrap; gap: 10px; }
        .paging { display: flex; gap: 6px; }
        .paging span { min-width: 26px; height: 26px; padding: 0 6px; border: 1px solid #e3e3e3; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11.5px; color: #555; }
        .paging span.aktif { background: var(--maroon); border-color: var(--maroon); color: #fff; }

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
        <a href="dashboard.php" class="brand">
            <svg class="logo" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M24 8c-5 0-9 3.5-9 8 0 3.5 2.3 6 5 7.2M24 8c5 0 9 3.5 9 8 0 3.5-2.3 6-5 7.2"/>
                <path d="M24 8c-2.2 2.5-2.2 6 0 9 2.2-3 2.2-6.500 0-9z"/>
                <path d="M20 23.200c1.200.5 2.500.8 4 .8s2.800-.3 4-.8"/>
                <path d="M24 24v18"/>
                <path d="M24 34c-4.500-.5-7.500-3-8.500-6.500 4 0 7.500 2 8.500 6.500z"/>
                <path d="M24 38c4-.5 7-2.800 8-6 -3.800 0-7 1.800-8 6z"/>
            </svg>
            <div class="brand-teks">
                <div class="nama">Royal Florist</div>
                <div class="slogan">Fresh Flower • Special Moment</div>
            </div>
        </a>

        <div class="top-kanan">
            <form method="get" class="date-pill" title="Pilih periode laporan">
                <?= ikon('calendar', 18); ?>
                <input type="date" name="start_date" value="<?= e($startDate); ?>" aria-label="Dari tanggal">
                <span class="sd">s/d</span>
                <input type="date" name="end_date" value="<?= e($endDate); ?>" aria-label="Sampai tanggal">
                <?php if ($querySearch !== ''): ?>
                    <input type="hidden" name="search" value="<?= e($querySearch); ?>">
                <?php endif; ?>
                <button type="submit">Terapkan</button>
            </form>
            <span class="bell"><?= ikon('bell', 22); ?></span>
            <div class="admin">
                <div class="avatar"><?= ikon('user', 20); ?></div>
                <span><?= e($_SESSION['nama'] ?? 'Admin'); ?></span>
                <?= ikon('chevron', 16); ?>
            </div>
        </div>
    </header>

    <aside class="sidebar">
        <ul class="sidebar-menu">
            <?php foreach ($menu as [$href, $ic, $label, $aktif]): ?>
                <li<?= $aktif ? ' class="active"' : ''; ?>>
                    <a href="<?= e($href); ?>"><?= ikon($ic, 20); ?><span><?= e($label); ?></span></a>
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

    <main class="main-content">
        <div class="page-title-area">
            <div>
                <h1>Laporan Penjualan</h1>
                <p>Berikut adalah ringkasan dan detail penjualan Royal Florist periode <?= e(date('d/m/Y', strtotime($startDate))); ?> s/d <?= e(date('d/m/Y', strtotime($endDate))); ?>.</p>
            </div>
            <div class="btn-group">
                <button type="button" onclick="window.print()" class="btn-action"><?= ikon('download', 17); ?> Export PDF</button>
                <button type="button" onclick="window.print()" class="btn-action"><?= ikon('printer', 17); ?> Cetak</button>
            </div>
        </div>

        <section class="cards-grid">
            <div class="card-stat">
                <svg class="watermark"><use href="#bunga"/></svg>
                <div class="card-head">
                    <div class="card-icon"><?= ikon('cart', 19); ?></div>
                    <div class="title">Total Penjualan</div>
                </div>
                <div class="value">Rp <?= number_format($totalPendapatan, 0, ',', '.'); ?></div>
                <?= badgeTren($tren['pendapatan']); ?>
            </div>
            <div class="card-stat">
                <svg class="watermark"><use href="#bunga"/></svg>
                <div class="card-head">
                    <div class="card-icon"><?= ikon('wallet', 19); ?></div>
                    <div class="title">Total Transaksi</div>
                </div>
                <div class="value"><?= $totalTransaksi; ?></div>
                <?= badgeTren($tren['transaksi']); ?>
            </div>
            <div class="card-stat">
                <svg class="watermark"><use href="#bunga"/></svg>
                <div class="card-head">
                    <div class="card-icon"><?= ikon('user', 19); ?></div>
                    <div class="title">Pelanggan Baru</div>
                </div>
                <div class="value"><?= $pelangganBaru; ?></div>
                <?= badgeTren($tren['pelanggan']); ?>
            </div>
            <div class="card-stat">
                <svg class="watermark"><use href="#bunga"/></svg>
                <div class="card-head">
                    <div class="card-icon"><?= ikon('box', 19); ?></div>
                    <div class="title">Total Produk Terjual</div>
                </div>
                <div class="value"><?= $totalProdukTerjual; ?></div>
                <?= badgeTren($tren['produk']); ?>
            </div>
        </section>

        <section class="charts-row">
            <div class="chart-box">
                <div class="chart-title"><?= ikon('aktivitas', 22); ?> Grafik Penjualan</div>
                <div class="chart-sub">Total penjualan per hari</div>
                <div class="chart-wrap"><canvas id="lineChartPenjualan"></canvas></div>
            </div>
            <div class="chart-box">
                <div class="chart-title"><?= ikon('jam', 22); ?> Kategori Produk Terlaris</div>
                <div class="donut-area">
                    <div class="donut"><canvas id="doughnutChartKategori"></canvas></div>
                    <ul class="legend">
                        <?php foreach ($kategoriTerlaris['labels'] as $i => $nama): ?>
                            <?php $jml = $kategoriTerlaris['data'][$i]; $persen = $totalKategori > 0 ? round($jml / $totalKategori * 100) : 0; ?>
                            <li>
                                <span class="dot" style="background:<?= e($warnaKategori[$i]); ?>"></span>
                                <span class="nm"><?= e($nama); ?></span>
                                <span class="jml"><?= (int) $jml; ?> (<?= (int) $persen; ?>%)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>

        <section class="table-box">
            <div class="table-head">
                <div class="chart-title"><?= ikon('tas', 22); ?> Detail Penjualan</div>
                <form method="get" class="search-box">
                    <?= ikon('cari', 16); ?>
                    <input type="text" name="search" placeholder="Cari produk..." value="<?= e($querySearch); ?>" aria-label="Cari produk">
                    <input type="hidden" name="start_date" value="<?= e($startDate); ?>">
                    <input type="hidden" name="end_date" value="<?= e($endDate); ?>">
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
                    <?php if (empty($listDetail)): ?>
                        <tr><td colspan="7" class="kosong">Data penjualan tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($listDetail as $row): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= $row['tanggal'] ? e(date('d/m/Y', strtotime($row['tanggal']))) : '-'; ?></td>
                            <td><?= e($row['nama_produk']); ?></td>
                            <td><?= e($row['kategori']); ?></td>
                            <td><?= (int) $row['jumlah']; ?></td>
                            <td>Rp <?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($row['total'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="table-foot">
                <div>Menampilkan <?= count($listDetail); ?> dari <?= $querySearch === '' ? $totalTransaksi : count($listDetail); ?> data</div>
                <div class="paging">
                    <span>&lsaquo;</span><span class="aktif">1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>&rsaquo;</span>
                </div>
            </div>
        </section>
    </main>

    <script>
        const rupiah = v => 'Rp ' + Number(v).toLocaleString('id-ID');
        const dataPenjualan = <?= json_encode($dataGrafik); ?>;

        // Angka di atas titik grafik (hanya kalau titiknya tidak terlalu banyak)
        const labelTitik = {
            id: 'labelTitik',
            afterDatasetsDraw(chart) {
                if (dataPenjualan.length > 10) return;
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
                labels: <?= json_encode($labelsGrafik); ?>,
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
                        ticks: { color: '#666', font: { size: 10 }, maxTicksLimit: 5, callback: v => rupiah(v) }
                    }
                }
            }
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
                ctx.fillText('<?= (int) $totalKategori; ?>', x, y + 10);
                ctx.font = '400 11px Inter, sans-serif';
                ctx.fillText('produk', x, y + 26);
                ctx.restore();
            }
        };

        new Chart(document.getElementById('doughnutChartKategori').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($kategoriTerlaris['labels']); ?>,
                datasets: [{
                    data: <?= json_encode($kategoriTerlaris['data']); ?>,
                    backgroundColor: <?= json_encode($warnaKategori); ?>,
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
</body>
</html>
