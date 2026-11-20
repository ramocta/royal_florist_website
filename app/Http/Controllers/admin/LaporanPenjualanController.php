<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanController extends Controller
{
    /** Jumlah baris detail per halaman di layar. */
    private const PER_HALAMAN = 10;

    public function index(Request $request)
    {
        $data = $this->dataLaporan($request);

        // Dipakai tombol "Export PDF": kirim semua data periode ini (bukan hanya satu halaman)
        // sebagai JSON. File PDF-nya dibuat di browser, jadi tidak perlu paket Composer.
        if ($request->query('format') === 'json') {
            return response()->json([
                'startDate'       => $data['startDate'],
                'endDate'         => $data['endDate'],
                'search'          => $data['search'],
                'namaAdmin'       => auth()->user()->nama ?? auth()->user()->name ?? 'Admin',
                'kartu'           => $data['kartu'],
                'rincianHarian'   => $data['rincianHarian'],
                'totalPendapatan' => $data['totalPendapatan'],
                'kategori'        => collect($data['kategoriLabels'])
                    ->map(fn ($nama, $i) => ['nama' => $nama, 'jumlah' => $data['kategoriData'][$i]])
                    ->values(),
                'detail'          => $data['queryDetail']->get(),
            ]);
        }

        $listDetail = $data['queryDetail']
            ->paginate(self::PER_HALAMAN)
            ->withQueryString(); // filter tanggal & pencarian ikut terbawa ke halaman lain

        unset($data['queryDetail']);

        return view('admin.laporan-penjualan', $data + ['listDetail' => $listDetail]);
    }

    /* ------------------------------------------------------------------ */
    /* Perhitungan data laporan                                           */
    /* ------------------------------------------------------------------ */

    private function dataLaporan(Request $request): array
    {
        /* ------------------------- Filter tanggal ------------------------- */

        $endDate   = $this->parseTanggal($request->query('end_date')) ?? now()->toDateString();
        $startDate = $this->parseTanggal($request->query('start_date'))
            ?? Carbon::parse($endDate)->subDays(6)->toDateString();

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $jumlahHari = (int) Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $jumlahHari = min($jumlahHari, 31);

        $end       = Carbon::parse($endDate);
        $start     = $end->copy()->subDays($jumlahHari - 1);
        $startDate = $start->toDateString();

        $prevEnd   = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($jumlahHari - 1);

        $search = trim((string) $request->query('search', ''));

        /* ---------------------------- Query dasar -------------------------- */

        // Pesanan lunas dalam rentang tanggal
        $pesananLunas = fn (string $dari, string $sampai) => DB::table('orders')
            ->where('status_pembayaran', 'lunas')
            ->whereRaw('DATE(dibuat_pada) BETWEEN ? AND ?', [$dari, $sampai]);

        // Item terjual (join produk + kategori) dalam rentang tanggal
        $itemTerjual = fn (string $dari, string $sampai) => DB::table('order_items as oi')
            ->join('orders as o', 'o.id_order', '=', 'oi.id_order')
            ->join('products as p', 'p.id_product', '=', 'oi.id_product')
            ->join('categories as c', 'c.id_ctg', '=', 'p.id_ctg')
            ->where('o.status_pembayaran', 'lunas')
            ->whereRaw('DATE(o.dibuat_pada) BETWEEN ? AND ?', [$dari, $sampai]);

        /* ---------------------------- Data grafik -------------------------- */

        $perHari = $pesananLunas($startDate, $endDate)
            ->selectRaw('DATE(dibuat_pada) as tgl, SUM(total_harga) as total')
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        $namaHari      = ['Ming', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $labelsGrafik  = [];
        $dataGrafik    = [];
        $rincianHarian = [];

        for ($i = $jumlahHari - 1; $i >= 0; $i--) {
            $d     = $end->copy()->subDays($i);
            $total = (float) ($perHari[$d->toDateString()] ?? 0);

            $labelsGrafik[]  = [$namaHari[$d->dayOfWeek], $d->format('d/m')];
            $dataGrafik[]    = $total;
            $rincianHarian[] = ['tanggal' => $d->format('d/m/Y'), 'hari' => $namaHari[$d->dayOfWeek], 'total' => $total];
        }

        /* ------------------------- Kartu ringkasan ------------------------ */

        $totalPendapatan = array_sum($dataGrafik);
        $totalTransaksi  = $pesananLunas($startDate, $endDate)->count();
        $pelangganBaru   = DB::table('users')->where('role', 'pelanggan')->count();

        $totalProdukTerjual = (int) DB::table('order_items as oi')
            ->join('orders as o', 'o.id_order', '=', 'oi.id_order')
            ->where('o.status_pembayaran', 'lunas')
            ->whereRaw('DATE(o.dibuat_pada) BETWEEN ? AND ?', [$startDate, $endDate])
            ->sum('oi.jumlah');

        $pendapatanPrev = (float) $pesananLunas($prevStart->toDateString(), $prevEnd->toDateString())->sum('total_harga');
        $transaksiPrev  = $pesananLunas($prevStart->toDateString(), $prevEnd->toDateString())->count();

        $kartu = [
            ['judul' => 'Total Penjualan',      'ikon' => 'cart',   'nilai' => 'Rp ' . number_format($totalPendapatan, 0, ',', '.'), 'tren' => $this->perubahan($totalPendapatan, $pendapatanPrev)],
            ['judul' => 'Total Transaksi',      'ikon' => 'wallet', 'nilai' => number_format($totalTransaksi, 0, ',', '.'),          'tren' => $this->perubahan($totalTransaksi, $transaksiPrev)],
            ['judul' => 'Pelanggan Baru',       'ikon' => 'user',   'nilai' => number_format($pelangganBaru, 0, ',', '.'),           'tren' => null],
            ['judul' => 'Total Produk Terjual', 'ikon' => 'box',    'nilai' => number_format($totalProdukTerjual, 0, ',', '.'),      'tren' => null],
        ];

        /* ------------------------ Kategori terlaris ----------------------- */

        $kategori = $itemTerjual($startDate, $endDate)
            ->selectRaw('c.category_name as nama, SUM(oi.jumlah) as total')
            ->groupBy('c.id_ctg', 'c.category_name')
            ->orderByDesc('total')
            ->get();

        $kategoriLabels = $kategori->pluck('nama')->all();
        $kategoriData   = $kategori->map(fn ($r) => (int) $r->total)->all();
        $totalKategori  = array_sum($kategoriData);

        /* ---------------------------- Tabel detail ------------------------- */

        // Belum dieksekusi: tampilan layar memakai paginate(), export PDF memakai get().
        $queryDetail = $itemTerjual($startDate, $endDate)
            ->when($search !== '', fn ($q) => $q->where('p.nama_produk', 'like', "%{$search}%"))
            ->selectRaw('o.dibuat_pada as tanggal, p.nama_produk, c.category_name as kategori, oi.jumlah, oi.harga_satuan, (oi.jumlah * oi.harga_satuan) as total')
            ->orderByDesc('o.dibuat_pada')
            ->orderByDesc('oi.id_order')
            ->orderBy('p.nama_produk');

        return compact(
            'startDate', 'endDate', 'search',
            'labelsGrafik', 'dataGrafik', 'rincianHarian', 'kartu',
            'kategoriLabels', 'kategoriData', 'totalKategori',
            'totalTransaksi', 'totalPendapatan', 'queryDetail'
        );
    }

    /** Kembalikan tanggal (Y-m-d) kalau valid, kalau tidak null. */
    private function parseTanggal(mixed $nilai): ?string
    {
        if (! is_string($nilai) || $nilai === '') {
            return null;
        }

        try {
            $d = Carbon::createFromFormat('Y-m-d', $nilai);

            return ($d && $d->format('Y-m-d') === $nilai) ? $nilai : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Persentase perubahan dibanding periode sebelumnya (null kalau tidak ada pembanding). */
    private function perubahan($sekarang, $sebelumnya): ?int
    {
        if ($sebelumnya > 0) {
            return (int) round((($sekarang - $sebelumnya) / $sebelumnya) * 100);
        }

        return null;
    }
}