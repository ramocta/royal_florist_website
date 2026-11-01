<?php
require_once __DIR__ . '/../../../public/koneksi.php';

// ============================================================
// 1. PROTEKSI LOGIN (sama seperti dashboard.php)
// ============================================================
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    header('Location: /login.php');
    exit;
}

// ============================================================
// 2. FUNGSI BANTU
// ============================================================
function h($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function rupiah($n): string { return 'Rp ' . number_format((float) $n, 0, ',', '.'); }

function tglPendek(string $tgl): string {
    $b = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($tgl);
    return $t ? date('d', $t) . ' ' . $b[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
}
function tglPanjang(string $tgl): string {
    $b = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = strtotime($tgl);
    return $t ? date('j', $t) . ' ' . $b[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
}
function rentangTanggal(string $a, string $b): string {
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $ta = strtotime($a); $tb = strtotime($b);
    if (date('Y-m', $ta) === date('Y-m', $tb)) {
        return date('j', $ta) . ' - ' . date('j', $tb) . ' ' . $bulan[(int) date('n', $tb)] . ' ' . date('Y', $tb);
    }
    return tglPanjang($a) . ' - ' . tglPanjang($b);
}

/* Ambil nilai pertama yang ada dari beberapa kemungkinan nama kolom */
function ambil(array $row, array $kunci, $default = '') {
    foreach ($kunci as $k) {
        if (isset($row[$k]) && $row[$k] !== '') return $row[$k];
    }
    return $default;
}

/* Samakan berbagai penulisan status di database ke 4 status pada desain */
function normalStatus(string $s): string {
    $s = strtolower(trim(str_replace(['_', '-'], ' ', $s)));
    if (in_array($s, ['selesai', 'completed', 'done'], true))                                return 'Selesai';
    if (in_array($s, ['batal', 'dibatalkan', 'cancel', 'cancelled', 'canceled'], true))      return 'Batal';
    if (in_array($s, ['siap kirim', 'siap dikirim', 'siap diambil', 'siap', 'ready'], true)) return 'Siap Kirim';
    return 'Diproses';
}

$kelasStatus = [
    'Diproses'   => 'st-proses',
    'Siap Kirim' => 'st-siap',
    'Selesai'    => 'st-selesai',
    'Batal'      => 'st-batal',
];

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

// ============================================================
// 3. AMBIL DATA PESANAN
//    - Jika tabel `pesanan` ada isinya  -> pakai data asli database
//    - Jika kosong / error              -> pakai data contoh (sesuai desain)
// ============================================================
$dariDb = [];
try {
    $dariDb = $pdo->query("SELECT * FROM pesanan ORDER BY 1 DESC LIMIT 1000")->fetchAll();
} catch (PDOException $ex) {
    $dariDb = [];
}

$modeDemo = empty($dariDb);
$semua    = [];

if ($modeDemo) {
    $semua = [
        ['id' => '#ORD-8821', 'pelanggan' => 'Siti Rahmawati', 'produk' => 'Romantic Rose Bouquet', 'tgl' => '2025-09-22', 'total' => 350000,  'status' => 'Diproses'],
        ['id' => '#ORD-8820', 'pelanggan' => 'Budi Santoso',   'produk' => 'Elegant Lily Stand',     'tgl' => '2025-09-22', 'total' => 750000,  'status' => 'Siap Kirim'],
        ['id' => '#ORD-8819', 'pelanggan' => 'Amanda Putri',   'produk' => 'Sunshine Bloom Box',     'tgl' => '2025-09-21', 'total' => 450000,  'status' => 'Selesai'],
        ['id' => '#ORD-8818', 'pelanggan' => 'Dewi Lestari',   'produk' => 'White Grace Board',      'tgl' => '2025-09-21', 'total' => 1200000, 'status' => 'Selesai'],
        ['id' => '#ORD-8817', 'pelanggan' => 'Rian Hidayat',   'produk' => "Pastel Baby's Breath",   'tgl' => '2025-09-20', 'total' => 280000,  'status' => 'Batal'],
    ];
} else {
    foreach ($dariDb as $r) {
        $idRaw = ambil($r, ['kode_pesanan', 'no_pesanan', 'no_invoice', 'id_pesanan', 'id']);
        $semua[] = [
            'id'        => is_numeric($idRaw) ? '#ORD-' . $idRaw : (string) $idRaw,
            'pelanggan' => (string) ambil($r, ['nama_pelanggan', 'nama_pemesan', 'pelanggan', 'nama'], '-'),
            'produk'    => (string) ambil($r, ['nama_produk', 'produk', 'item_produk'], '-'),
            'tgl'       => (string) ambil($r, ['tanggal_pengiriman', 'tgl_pengiriman', 'jadwal_pengiriman', 'tanggal', 'created_at'], ''),
            'total'     => (float)  ambil($r, ['total_harga', 'total'], 0),
            'status'    => normalStatus((string) ambil($r, ['status_pesanan', 'status'], 'diproses')),
        ];
    }
}

// ============================================================
// 4. FILTER (tanggal, status, pencarian)
// ============================================================
// Rentang tanggal default: demo = 16-22 Sep 2025 (sesuai desain), data asli = minggu ini
$defDari   = $modeDemo ? '2025-09-16' : date('Y-m-d', strtotime('monday this week'));
$defSampai = $modeDemo ? '2025-09-22' : date('Y-m-d', strtotime('sunday this week'));

$dari   = $_GET['dari']   ?? $defDari;
$sampai = $_GET['sampai'] ?? $defSampai;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))   $dari   = $defDari;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) $sampai = $defSampai;
if ($dari > $sampai) { [$dari, $sampai] = [$sampai, $dari]; }

$statusPilih = $_GET['status'] ?? '';
$daftarStatus = ['Diproses', 'Siap Kirim', 'Selesai', 'Batal'];
if (!in_array($statusPilih, $daftarStatus, true)) $statusPilih = '';

$cari = trim($_GET['q'] ?? '');

$hasil = array_values(array_filter($semua, function ($p) use ($dari, $sampai, $statusPilih, $cari) {
    $tgl = $p['tgl'] ? date('Y-m-d', strtotime($p['tgl'])) : '';
    if ($tgl && ($tgl < $dari || $tgl > $sampai)) return false;
    if ($statusPilih !== '' && $p['status'] !== $statusPilih) return false;
    if ($cari !== '') {
        $gabung = strtolower($p['id'] . ' ' . $p['pelanggan'] . ' ' . $p['produk']);
        if (strpos($gabung, strtolower($cari)) === false) return false;
    }
    return true;
}));

// ============================================================
// 5. EXPORT CSV (tombol "Export Data")
// ============================================================
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="daftar-pesanan-' . date('Ymd') . '.csv"');
    echo "\xEF\xBB\xBF"; // BOM agar Excel membaca UTF-8
    $out = fopen('php://output', 'w');
    fputcsv($out, ['No', 'ID Pesanan', 'Pelanggan', 'Item Produk', 'Tgl Pengiriman', 'Total', 'Status']);
    foreach ($hasil as $i => $p) {
        fputcsv($out, [$i + 1, $p['id'], $p['pelanggan'], $p['produk'], tglPendek($p['tgl']), (int) $p['total'], $p['status']]);
    }
    fclose($out);
    exit;
}

// ============================================================
// 6. KARTU STATISTIK
// ============================================================
if ($modeDemo) {
    $statTotal = 128; $statProses = 12; $statSiap = 5; $statSelesaiHariIni = 18;
} else {
    $bulanIni = date('Y-m');
    $hariIni  = date('Y-m-d');
    $statTotal = $statProses = $statSiap = $statSelesaiHariIni = 0;
    foreach ($semua as $p) {
        $t = $p['tgl'] ? date('Y-m-d', strtotime($p['tgl'])) : '';
        if (substr($t, 0, 7) === $bulanIni) $statTotal++;
        if ($p['status'] === 'Diproses')   $statProses++;
        if ($p['status'] === 'Siap Kirim') $statSiap++;
        if ($p['status'] === 'Selesai' && $t === $hariIni) $statSelesaiHariIni++;
    }
}

// ============================================================
// 7. PAGINASI (5 baris per halaman)
// ============================================================
$perHalaman  = 5;
$totalHasil  = count($hasil);
$totalHal    = max(1, (int) ceil($totalHasil / $perHalaman));
$halaman     = max(1, min($totalHal, (int) ($_GET['hal'] ?? 1)));
$tampil      = array_slice($hasil, ($halaman - 1) * $perHalaman, $perHalaman);
$labelTotal  = $modeDemo ? max($statTotal, $totalHasil) : $totalHasil;

function urlHalaman(int $h): string {
    $q = $_GET; unset($q['export']); $q['hal'] = $h;
    return '/admin/pesanan?' . http_build_query($q);
}

// ============================================================
// 8. MENU SIDEBAR & DATA ADMIN
// ============================================================
$menu = [
    ['/dashboard.php',         'home',     'Dashboard',          false],
    ['/laporan_penjualan.php', 'laporan',  'Laporan Penjualan',  false],
    ['#',                      'user',     'Pelanggan',          false],
    ['/admin/pesanan',         'cart',     'Pesanan',            true],
    ['#',                      'box',      'Produk',             false],
    ['#',                      'database', 'Stok',               false],
    ['#',                      'users',    'Manajemen Staff',    false],
    ['#',                      'progress', 'Manajemen Progress', false],
    ['#',                      'settings', 'Pengaturan',         false],
];
$namaAdmin = h($_SESSION['nama'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Royal Florist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #7B1830;
            --maroon-gelap: #5A1226;
            --pink-bg: #FBE9ED;
            --pink-halaman: #F8EEF3;
            --pink-garis: #F3CCD6;
            --pink-thead: #FBE9ED;
            --teks: #2B2B2B;
            --abu: #6F6F6F;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--pink-halaman); color: var(--teks); min-height: 100vh; }
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

        .date-menu { position: relative; }
        .date-menu summary {
            list-style: none; display: flex; align-items: center; gap: 8px; cursor: pointer; background: #fff;
            border: 1px solid var(--pink-garis); border-radius: 10px; padding: 8px 12px; color: var(--maroon);
            font-size: 12.5px; font-weight: 500;
        }
        .date-menu summary::-webkit-details-marker { display: none; }
        .date-form {
            position: absolute; right: 0; top: 48px; width: 250px; background: #fff; border: 1px solid var(--pink-garis);
            border-radius: 10px; box-shadow: 0 6px 18px rgba(123, 24, 48, .12); padding: 14px; z-index: 30;
        }
        .date-form label { display: block; font-size: 11px; color: var(--abu); margin: 0 0 4px; }
        .date-form input[type=date] {
            width: 100%; padding: 7px 9px; border: 1px solid var(--pink-garis); border-radius: 8px;
            font-size: 12.5px; color: var(--teks); margin-bottom: 10px;
        }
        .date-form button {
            width: 100%; padding: 8px; border: none; border-radius: 8px; background: var(--maroon-gelap);
            color: #fff; font-size: 12.5px; font-weight: 600; cursor: pointer;
        }

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
        .main-content { margin: 72px 0 0 240px; min-height: calc(100vh - 72px); min-width: 0; }
        .container { padding: 22px 28px 32px; }

        .page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        .page-head h1 { font-size: 26px; color: var(--maroon-gelap); font-weight: 700; line-height: 1.2; }
        .page-head p { font-size: 12.5px; color: #666; margin-top: 4px; }
        .head-aksi { display: flex; gap: 12px; }
        .btn {
            display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px;
            font-size: 12.5px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid var(--pink-garis);
            background: #fff; color: var(--maroon-gelap); font-family: inherit;
        }
        .btn-primary { background: var(--maroon-gelap); color: #fff; border-color: var(--maroon-gelap); }

        /* Kartu statistik */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 22px; }
        .stat { background: #fff; border-radius: 12px; padding: 16px 20px; }
        .stat .judul { font-size: 12px; font-weight: 700; color: var(--maroon-gelap); }
        .stat .angka { font-size: 28px; font-weight: 700; color: #1d1d1d; margin: 4px 0 6px; line-height: 1.15; }
        .stat .ket { font-size: 10.5px; color: var(--abu); }
        .stat .ket.biru   { color: #1A5FB4; font-weight: 600; }
        .stat .ket.kuning { color: #B7860B; font-weight: 600; }
        .stat .ket.hijau  { color: #2E7D32; font-weight: 600; }

        /* Panel tabel */
        .panel { background: #fff; border-radius: 14px; padding: 22px 28px 26px; min-height: 574px; display: flex; flex-direction: column; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 14px; flex-wrap: wrap; }
        .search {
            display: flex; align-items: center; gap: 8px; width: 337px; max-width: 100%; border: 1px solid var(--pink-garis);
            border-radius: 10px; padding: 0 14px; height: 42px; background: #fff;
        }
        .search input { border: none; outline: none; flex: 1; font-size: 12.5px; color: var(--teks); background: transparent; }
        .search input::placeholder { color: #777; }
        .filter-status {
            height: 42px; border: 1px solid var(--pink-garis); border-radius: 10px; padding: 0 14px; background: #fff;
            font-size: 12.5px; color: #333; cursor: pointer; min-width: 164px; outline: none;
        }

        .tabel-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 860px; }
        thead th {
            background: var(--pink-thead); color: var(--maroon-gelap); font-size: 12px; font-weight: 700;
            text-align: left; padding: 14px 10px; height: 40px;
        }
        thead th:first-child { border-radius: 8px 0 0 8px; padding-left: 18px; }
        thead th:last-child  { border-radius: 0 8px 8px 0; }
        tbody td { padding: 14px 10px; font-size: 12px; border-bottom: 1px solid #EFDDE3; height: 47px; vertical-align: middle; }
        tbody td:first-child { padding-left: 18px; }
        td.nama  { font-weight: 700; color: #1d1d1d; }
        td.produk { color: #666; }
        td.total { font-weight: 700; color: #1d1d1d; }
        td.kosong { text-align: center; color: var(--abu); padding: 60px 10px; border-bottom: none; }

        .badge { display: inline-block; padding: 6px 17px; border-radius: 999px; font-size: 10.5px; font-weight: 700; }
        .st-proses  { background: #DCEBFB; color: #1A5FB4; }
        .st-siap    { background: #FDF3CC; color: #B7860B; }
        .st-selesai { background: #DDF0E0; color: #2E7D32; }
        .st-batal   { background: #FBDADF; color: #C62839; }

        .aksi { font-weight: 700; color: #1d1d1d; white-space: nowrap; }
        .aksi button { border: none; background: none; font: inherit; font-weight: 700; color: inherit; cursor: pointer; padding: 0; }
        .aksi button:hover { color: var(--maroon); text-decoration: underline; }
        .aksi .sp { margin: 0 4px; }

        .panel-foot { margin-top: auto; padding-top: 20px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .panel-foot .info { font-size: 12px; color: var(--abu); }
        .paging { display: flex; gap: 10px; }
        .paging a, .paging span {
            min-width: 32px; height: 30px; padding: 0 8px; display: flex; align-items: center; justify-content: center;
            border: 1px solid #ddd; border-radius: 6px; background: #fff; color: #555; font-size: 12px; font-weight: 600; text-decoration: none;
        }
        .paging .aktif { background: var(--maroon-gelap); border-color: var(--maroon-gelap); color: #fff; }
        .paging .mati  { opacity: .45; pointer-events: none; }

        /* Modal detail */
        .modal-bg { position: fixed; inset: 0; background: rgba(40, 10, 20, .45); display: none; align-items: center; justify-content: center; z-index: 50; padding: 16px; }
        .modal-bg.buka { display: flex; }
        .modal { background: #fff; border-radius: 14px; width: 420px; max-width: 100%; padding: 24px; box-shadow: 0 14px 40px rgba(0, 0, 0, .25); }
        .modal h3 { color: var(--maroon-gelap); font-size: 18px; margin-bottom: 14px; }
        .modal dl { display: grid; grid-template-columns: 130px 1fr; gap: 10px 8px; font-size: 13px; }
        .modal dt { color: var(--abu); }
        .modal dd { font-weight: 600; color: #1d1d1d; }
        .modal .tutup-baris { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }

        @media (max-width: 1100px) { .stats { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 800px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .brand-teks .slogan, .admin-menu summary .nm { display: none; }
            .stats { grid-template-columns: 1fr; }
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
        <a href="/dashboard.php" class="brand">
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
            <!-- Filter rentang tanggal -->
            <details class="date-menu">
                <summary>
                    <?= ikon('calendar', 20); ?>
                    <span><?= h(rentangTanggal($dari, $sampai)); ?></span>
                    <?= ikon('chevron', 16); ?>
                </summary>
                <form method="GET" class="date-form">
                    <input type="hidden" name="q" value="<?= h($cari); ?>">
                    <input type="hidden" name="status" value="<?= h($statusPilih); ?>">
                    <label for="dari">Dari tanggal</label>
                    <input type="date" id="dari" name="dari" value="<?= h($dari); ?>">
                    <label for="sampai">Sampai tanggal</label>
                    <input type="date" id="sampai" name="sampai" value="<?= h($sampai); ?>">
                    <button type="submit">Terapkan</button>
                </form>
            </details>

            <span class="bell"><?= ikon('bell', 22); ?></span>

            <details class="admin-menu">
                <summary>
                    <span class="avatar"><?= ikon('user', 20); ?></span>
                    <span class="nm"><?= $namaAdmin; ?></span>
                    <?= ikon('chevron', 16); ?>
                </summary>
                <div class="dropdown">
                    <a href="/logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?');"><?= ikon('logout', 17); ?> Logout</a>
                </div>
            </details>
        </div>
    </header>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <?php foreach ($menu as [$href, $ic, $label, $aktif]): ?>
                <li<?= $aktif ? ' class="active"' : ''; ?>>
                    <a href="<?= h($href); ?>"><?= ikon($ic, 20); ?><span><?= h($label); ?></span></a>
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

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="container">

            <div class="page-head">
                <div>
                    <h1>Daftar Pesanan</h1>
                    <p>Pantau pesanan masuk, status pembuatan buket, jadwal pengiriman, dan pembayaran.</p>
                </div>
                <div class="head-aksi">
                    <?php $qExport = $_GET; $qExport['export'] = 1; unset($qExport['hal']); ?>
                    <a class="btn" href="/admin/pesanan?<?= h(http_build_query($qExport)); ?>">Export Data</a>
                    <a class="btn btn-primary" href="#" onclick="alert('Halaman Pesanan Baru belum dibuat.'); return false;">+ Pesanan Baru</a>
                </div>
            </div>

            <!-- KARTU STATISTIK -->
            <section class="stats">
                <div class="stat">
                    <div class="judul">Total Pesanan</div>
                    <div class="angka"><?= number_format($statTotal, 0, ',', '.'); ?></div>
                    <div class="ket">Pesanan bulan ini</div>
                </div>
                <div class="stat">
                    <div class="judul">Perlu Diproses</div>
                    <div class="angka"><?= number_format($statProses, 0, ',', '.'); ?></div>
                    <div class="ket biru">Sedang dirangkai florist</div>
                </div>
                <div class="stat">
                    <div class="judul">Siap Dikirim</div>
                    <div class="angka"><?= number_format($statSiap, 0, ',', '.'); ?></div>
                    <div class="ket kuning">Menunggu kurir</div>
                </div>
                <div class="stat">
                    <div class="judul">Selesai Hari Ini</div>
                    <div class="angka"><?= number_format($statSelesaiHariIni, 0, ',', '.'); ?></div>
                    <div class="ket hijau">Berhasil terkirim</div>
                </div>
            </section>

            <!-- TABEL PESANAN -->
            <section class="panel">
                <form method="GET" class="toolbar" id="formFilter">
                    <input type="hidden" name="dari" value="<?= h($dari); ?>">
                    <input type="hidden" name="sampai" value="<?= h($sampai); ?>">
                    <label class="search">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#777" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/></svg>
                        <input type="text" name="q" value="<?= h($cari); ?>" placeholder="Cari No. ID / nama pemesan...">
                    </label>
                    <select name="status" class="filter-status" onchange="document.getElementById('formFilter').submit();">
                        <option value="">Semua Status</option>
                        <?php foreach ($daftarStatus as $st): ?>
                            <option value="<?= h($st); ?>"<?= $statusPilih === $st ? ' selected' : ''; ?>><?= h($st); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <div class="tabel-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:50px">No</th>
                                <th>ID Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Item Produk</th>
                                <th>Tgl Pengiriman</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($tampil)): ?>
                            <tr><td class="kosong" colspan="8">Tidak ada pesanan yang cocok dengan filter.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tampil as $i => $p): ?>
                            <?php
                                $no = ($halaman - 1) * $perHalaman + $i + 1;
                                $dataJson = json_encode([
                                    'id'        => $p['id'],
                                    'pelanggan' => $p['pelanggan'],
                                    'produk'    => $p['produk'],
                                    'tgl'       => tglPanjang($p['tgl']),
                                    'total'     => rupiah($p['total']),
                                    'status'    => $p['status'],
                                ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                            ?>
                            <tr>
                                <td><?= $no; ?></td>
                                <td><?= h($p['id']); ?></td>
                                <td class="nama"><?= h($p['pelanggan']); ?></td>
                                <td class="produk"><?= h($p['produk']); ?></td>
                                <td><?= h(tglPendek($p['tgl'])); ?></td>
                                <td class="total"><?= h(rupiah($p['total'])); ?></td>
                                <td><span class="badge <?= h($kelasStatus[$p['status']] ?? 'st-proses'); ?>"><?= h($p['status']); ?></span></td>
                                <td class="aksi">
                                    <button type="button" data-pesanan="<?= h($dataJson); ?>" onclick="bukaDetail(this)">Detail</button><span class="sp">|</span><button type="button" data-pesanan="<?= h($dataJson); ?>" onclick="cetakPesanan(this)">Cetak</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="panel-foot">
                    <div class="info">Menampilkan <?= count($tampil); ?> dari <?= number_format($labelTotal, 0, ',', '.'); ?> pesanan</div>
                    <div class="paging">
                        <a href="<?= h(urlHalaman(max(1, $halaman - 1))); ?>" class="<?= $halaman <= 1 ? 'mati' : ''; ?>">&lsaquo;</a>
                        <?php
                            $awal  = max(1, $halaman - 2);
                            $akhir = min($totalHal, $halaman + 2);
                            for ($h = $awal; $h <= $akhir; $h++):
                        ?>
                            <a href="<?= h(urlHalaman($h)); ?>" class="<?= $h === $halaman ? 'aktif' : ''; ?>"><?= $h; ?></a>
                        <?php endfor; ?>
                        <a href="<?= h(urlHalaman(min($totalHal, $halaman + 1))); ?>" class="<?= $halaman >= $totalHal ? 'mati' : ''; ?>">&rsaquo;</a>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- MODAL DETAIL PESANAN -->
    <div class="modal-bg" id="modalDetail" onclick="if(event.target===this) tutupDetail();">
        <div class="modal">
            <h3>Detail Pesanan</h3>
            <dl id="isiDetail"></dl>
            <div class="tutup-baris">
                <button type="button" class="btn" onclick="tutupDetail()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function esc(s) {
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }

        function bukaDetail(btn) {
            const d = JSON.parse(btn.dataset.pesanan);
            document.getElementById('isiDetail').innerHTML =
                '<dt>ID Pesanan</dt><dd>' + esc(d.id) + '</dd>' +
                '<dt>Pelanggan</dt><dd>' + esc(d.pelanggan) + '</dd>' +
                '<dt>Item Produk</dt><dd>' + esc(d.produk) + '</dd>' +
                '<dt>Tgl Pengiriman</dt><dd>' + esc(d.tgl) + '</dd>' +
                '<dt>Total</dt><dd>' + esc(d.total) + '</dd>' +
                '<dt>Status</dt><dd>' + esc(d.status) + '</dd>';
            document.getElementById('modalDetail').classList.add('buka');
        }
        function tutupDetail() {
            document.getElementById('modalDetail').classList.remove('buka');
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupDetail(); });

        function cetakPesanan(btn) {
            const d = JSON.parse(btn.dataset.pesanan);
            const w = window.open('', '_blank', 'width=520,height=640');
            if (!w) { alert('Pop-up diblokir browser. Izinkan pop-up untuk mencetak.'); return; }
            w.document.write(
                '<html><head><title>Cetak ' + esc(d.id) + '</title>' +
                '<style>body{font-family:Arial,sans-serif;padding:28px;color:#222}h2{color:#7B1830;margin:0}' +
                'p.s{margin:2px 0 18px;font-size:12px;color:#777}table{width:100%;border-collapse:collapse;font-size:14px}' +
                'td{padding:8px 0;border-bottom:1px solid #eee}td:first-child{color:#777;width:40%}</style></head><body>' +
                '<h2>Royal Florist</h2><p class="s">Fresh Flower &bull; Special Moment</p>' +
                '<table>' +
                '<tr><td>ID Pesanan</td><td><b>' + esc(d.id) + '</b></td></tr>' +
                '<tr><td>Pelanggan</td><td>' + esc(d.pelanggan) + '</td></tr>' +
                '<tr><td>Item Produk</td><td>' + esc(d.produk) + '</td></tr>' +
                '<tr><td>Tgl Pengiriman</td><td>' + esc(d.tgl) + '</td></tr>' +
                '<tr><td>Status</td><td>' + esc(d.status) + '</td></tr>' +
                '<tr><td>Total</td><td><b>' + esc(d.total) + '</b></td></tr>' +
                '</table></body></html>'
            );
            w.document.close();
            w.focus();
            w.print();
        }
    </script>
</body>
</html>