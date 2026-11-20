<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tanggal dari filter (default hari ini)
        $selectedDate = $this->tanggalTerpilih($request->query('tanggal'));
        $tanggal      = Carbon::parse($selectedDate);

        // 2. Data grafik 7 hari terakhir (6 hari lalu sampai tanggal terpilih)
        $mulai = $tanggal->copy()->subDays(6)->toDateString();

        $perHari = DB::table('orders')
            ->selectRaw('DATE(dibuat_pada) as tgl, SUM(total_harga) as total')
            ->where('status_pembayaran', 'lunas')
            ->whereRaw('DATE(dibuat_pada) BETWEEN ? AND ?', [$mulai, $selectedDate])
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        $labelsGrafik = [];
        $dataGrafik   = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = $tanggal->copy()->subDays($i);
            // Dua baris label: tanggal di atas, nama hari di bawah
            $labelsGrafik[] = [$d->format('d/m'), $d->locale('id')->translatedFormat('l')];
            $dataGrafik[]   = (float) ($perHari[$d->toDateString()] ?? 0);
        }

        $pendapatanToday = end($dataGrafik);

        // 3. Angka kartu statistik
        $totalPesanan = DB::table('orders')->whereDate('dibuat_pada', $selectedDate)->count();
        $totalUser    = DB::table('users')->count();
        $totalProduk  = DB::table('products')->where('is_active', 1)->count();

        // Pembanding: hari yang sama pada minggu sebelumnya
        $tglLalu        = $tanggal->copy()->subDays(7)->toDateString();
        $pesananLalu    = DB::table('orders')->whereDate('dibuat_pada', $tglLalu)->count();
        $pendapatanLalu = (float) DB::table('orders')
            ->where('status_pembayaran', 'lunas')
            ->whereDate('dibuat_pada', $tglLalu)
            ->sum('total_harga');

        $kartu = [
            [
                'judul' => 'Total Pesanan',
                'ikon'  => 'cart',
                'nilai' => number_format($totalPesanan, 0, ',', '.'),
                'info'  => $this->infoPersen($totalPesanan, $pesananLalu),
            ],
            [
                'judul' => 'Total User',
                'ikon'  => 'user',
                'nilai' => number_format($totalUser, 0, ',', '.'),
                'info'  => ['teks' => '', 'ket' => 'akun terdaftar', 'kelas' => 'netral'],
            ],
            [
                'judul' => 'Total Produk',
                'ikon'  => 'box',
                'nilai' => number_format($totalProduk, 0, ',', '.'),
                'info'  => ['teks' => '', 'ket' => 'produk aktif', 'kelas' => 'netral'],
            ],
            [
                'judul' => 'Pendapatan Hari Ini',
                'ikon'  => 'wallet',
                'nilai' => 'Rp ' . number_format($pendapatanToday, 0, ',', '.'),
                'info'  => $this->infoPersen($pendapatanToday, $pendapatanLalu),
            ],
        ];

        return view('admin.dashboard', compact('selectedDate', 'labelsGrafik', 'dataGrafik', 'kartu'));
    }

    /** Validasi input tanggal (format Y-m-d), kalau tidak valid pakai hari ini. */
    private function tanggalTerpilih(mixed $input): string
    {
        if (is_string($input) && $input !== '') {
            try {
                $d = Carbon::createFromFormat('Y-m-d', $input);
                if ($d && $d->format('Y-m-d') === $input) {
                    return $input;
                }
            } catch (\Throwable $e) {
                // abaikan, pakai default
            }
        }

        return now()->toDateString();
    }

    /** Persentase perubahan untuk kartu. */
    private function infoPersen($sekarang, $sebelumnya): array
    {
        if ($sebelumnya <= 0) {
            return ['teks' => '', 'ket' => 'Belum ada pembanding', 'kelas' => 'netral'];
        }

        $p = (int) round((($sekarang - $sebelumnya) / $sebelumnya) * 100);

        return [
            'teks'  => ($p >= 0 ? '+' : '') . $p . '%',
            'ket'   => 'dari minggu sebelumnya',
            'kelas' => $p >= 0 ? 'naik' : 'turun',
        ];
    }
}