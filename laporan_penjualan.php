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

/* --------------------------- Data grafik -------------------------- */

$labelsGrafik = [];
$dataGrafik   = [];

for ($i = $jumlahHari - 1; $i >= 0; $i--) {
    $dateLoop = date('Y-m-d', strtotime("$endDate -$i days"));
    $labelsGrafik[] = [date('d/m', strtotime($dateLoop)), getNamaHariSingkat($dateLoop)];

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

$kategoriTerlaris = [
    'labels' => ['Buket Bunga', 'Mawar Tangkai', 'Standing Flower', 'Hand Bouquet', 'Bunga Box', 'Lainnya'],
    'data'   => [22, 15, 10, 8, 5, 2],
];

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

// Data contoh hanya kalau tidak sedang mencari dan mode demo aktif
if (empty($listDetail) && $querySearch === '' && $modeDemo) {
    $listDetail = [
        ['tanggal' => '2025-09-22', 'nama_produk' => 'Mawar Tangkai Merah', 'kategori' => 'Mawar Tangkai',   'jumlah' => 8, 'harga_satuan' => 25000,  'total' => 200000],
        ['tanggal' => '2025-09-21', 'nama_produk' => 'Buket Fresh Flower',  'kategori' => 'Buket Bunga',     'jumlah' => 5, 'harga_satuan' => 150000, 'total' => 750000],
        ['tanggal' => '2025-09-20', 'nama_produk' => 'Standing Flower',     'kategori' => 'Standing Flower', 'jumlah' => 4, 'harga_satuan' => 120000, 'total' => 480000],
        ['tanggal' => '2025-09-19', 'nama_produk' => 'Hand Bouquet',        'kategori' => 'Hand Bouquet',    'jumlah' => 7, 'harga_satuan' => 85000,  'total' => 595000],
        ['tanggal' => '2025-09-18', 'nama_produk' => 'Bunga Box',           'kategori' => 'Bunga Box',       'jumlah' => 3, 'harga_satuan' => 100000, 'total' => 300000],
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Royal Florist</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f3f0f2; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #802B41; color: white; display: flex; flex-direction: column; position: fixed; height: 100%; z-index: 10; }
        .sidebar-brand { padding: 25px 20px; font-size: 18px; font-weight: 700; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .sidebar-menu { list-style: none; padding: 10px 0; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 8px; padding: 12px 25px; color: rgba(255, 255, 255, 0.7); text-decoration: none; font-size: 13.5px; transition: 0.3s; }
        .sidebar-menu li.active a, .sidebar-menu li a:hover { background-color: #ffffff; color: #802B41; font-weight: 600; border-radius: 20px 0 0 20px; margin-left: 15px; }
        .main-content { margin-left: 250px; flex: 1; padding: 20px 30px; min-width: 0; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: white; padding: 15px 25px; border-radius: 12px; }
        .brand-header h2 { color: #802B41; font-size: 20px; font-weight: 700; }
        .brand-header p { font-size: 11px; color: #777; }
        .top-right { display: flex; align-items: center; gap: 15px; }
        .user-info { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; }
        .avatar { width: 32px; height: 32px; background: #802B41; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .page-title-area { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 15px; flex-wrap: wrap; }
        .page-title-area h1 { font-size: 22px; color: #802B41; font-weight: 700; }
        .page-title-area p { font-size: 12px; color: #777; }
        .btn-group { display: flex; gap: 10px; }
        .btn-action { background: #fce4ec; color: #802B41; border: 1px solid #f8bbd0; padding: 7px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .btn-action:hover { background: #f8bbd0; }
        .filter-bar { background: white; padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
        .filter-bar label { display: block; font-size: 11px; color: #888; margin-bottom: 3px; }
        .filter-bar input { padding: 7px 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 12px; }
        .cards-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .card-stat { background: white; padding: 18px; border-radius: 12px; display: flex; gap: 12px; align-items: center; }
        .card-icon { width: 42px; height: 42px; background: #fdf2f4; color: #802B41; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .card-info .title { font-size: 11px; color: #888; }
        .card-info .value { font-size: 18px; font-weight: 700; color: #222; }
        .charts-row { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 20px; margin-bottom: 20px; }
        .chart-box { background: white; padding: 20px; border-radius: 12px; }
        .chart-box-header { color: #802B41; font-weight: 700; font-size: 15px; margin-bottom: 10px; }
        .table-box { background: white; padding: 20px; border-radius: 12px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #fdf2f4; color: #802B41; font-size: 11px; padding: 10px; text-align: left; }
        td { font-size: 12px; padding: 12px 10px; border-bottom: 1px solid #eee; }
        .kosong { text-align: center; color: #999; padding: 25px; }

        @media (max-width: 900px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-row { grid-template-columns: 1fr; }
        }

        @media print {
            .sidebar, .btn-group, .filter-bar { display: none !important; }
            .main-content { margin-left: 0; padding: 0; }
            body { background: white; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <a href="dashboard.php" class="sidebar-brand">🌷 Royal Florist</a>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 <span>Dashboard</span></a></li>
            <li class="active"><a href="laporan_penjualan.php">📄 <span>Laporan Penjualan</span></a></li>
            <li><a href="#">👥 <span>Pelanggan</span></a></li>
            <li><a href="#">🛒 <span>Pesanan</span></a></li>
            <li><a href="#">📦 <span>Produk</span></a></li>
            <li><a href="#">🗄️ <span>Stok</span></a></li>
            <li><a href="#">👨‍💼 <span>Manajemen Staff</span></a></li>
            <li><a href="#">🔄 <span>Manajemen Progress</span></a></li>
            <li><a href="#">⚙️ <span>Pengaturan</span></a></li>
            <li><a href="logout.php">🚪 <span>Keluar</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <div class="brand-header">
                <h2>Royal Florist</h2>
                <p>Fresh Flower • Special Moment</p>
            </div>
            <div class="top-right">
                <div class="user-info">
                    <div class="avatar"><?= e(strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1))); ?></div>
                    <span><?= e($_SESSION['nama'] ?? 'Admin'); ?></span>
                </div>
            </div>
        </div>

        <div class="page-title-area">
            <div>
                <h1>Laporan Penjualan</h1>
                <p>Ringkasan dan detail penjualan Royal Florist periode <?= e(date('d/m/Y', strtotime($startDate))); ?> s/d <?= e(date('d/m/Y', strtotime($endDate))); ?>.</p>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn-action">📥 Export PDF</button>
                <button onclick="window.print()" class="btn-action">🖨️ Cetak</button>
            </div>
        </div>

        <form method="get" class="filter-bar">
            <div>
                <label for="start_date">Dari tanggal</label>
                <input type="date" id="start_date" name="start_date" value="<?= e($startDate); ?>">
            </div>
            <div>
                <label for="end_date">Sampai tanggal</label>
                <input type="date" id="end_date" name="end_date" value="<?= e($endDate); ?>">
            </div>
            <div>
                <label for="search">Cari produk</label>
                <input type="text" id="search" name="search" placeholder="Nama produk..." value="<?= e($querySearch); ?>">
            </div>
            <button type="submit" class="btn-action">🔍 Terapkan</button>
            <a href="laporan_penjualan.php" class="btn-action" style="text-decoration:none;">Reset</a>
        </form>

        <div class="cards-grid">
            <div class="card-stat">
                <div class="card-icon">🛒</div>
                <div class="card-info">
                    <div class="title">Total Penjualan</div>
                    <div class="value">Rp <?= number_format($totalPendapatan, 0, ',', '.'); ?></div>
                </div>
            </div>
            <div class="card-stat">
                <div class="card-icon">💼</div>
                <div class="card-info">
                    <div class="title">Total Transaksi</div>
                    <div class="value"><?= $totalTransaksi; ?></div>
                </div>
            </div>
            <div class="card-stat">
                <div class="card-icon">👤</div>
                <div class="card-info">
                    <div class="title">Pelanggan Baru</div>
                    <div class="value"><?= $pelangganBaru; ?></div>
                </div>
            </div>
            <div class="card-stat">
                <div class="card-icon">📦</div>
                <div class="card-info">
                    <div class="title">Total Produk Terjual</div>
                    <div class="value"><?= $totalProdukTerjual; ?></div>
                </div>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-box">
                <div class="chart-box-header">📈 Grafik Penjualan</div>
                <div style="height: 220px;"><canvas id="lineChartPenjualan"></canvas></div>
            </div>
            <div class="chart-box">
                <div class="chart-box-header">🕒 Kategori Produk Terlaris</div>
                <div style="height: 220px;"><canvas id="doughnutChartKategori"></canvas></div>
            </div>
        </div>

        <div class="table-box">
            <div class="chart-box-header">🛍️ Detail Penjualan</div>
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
                            <td><b><?= e($row['nama_produk']); ?></b></td>
                            <td><?= e($row['kategori']); ?></td>
                            <td><?= (int) $row['jumlah']; ?></td>
                            <td>Rp <?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                            <td><b>Rp <?= number_format($row['total'], 0, ',', '.'); ?></b></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('lineChartPenjualan').getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labelsGrafik); ?>,
                datasets: [{
                    label: 'Penjualan',
                    data: <?= json_encode($dataGrafik); ?>,
                    borderColor: '#802B41',
                    backgroundColor: 'rgba(128, 43, 65, 0.08)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(1) + ' jt' }
                    }
                }
            }
        });

        new Chart(document.getElementById('doughnutChartKategori').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($kategoriTerlaris['labels']); ?>,
                datasets: [{
                    data: <?= json_encode($kategoriTerlaris['data']); ?>,
                    backgroundColor: ['#802B41', '#a84d65', '#c87088', '#e295aa', '#f2bac9', '#f8dbe2']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }
            }
        });
    </script>
</body>
</html>
