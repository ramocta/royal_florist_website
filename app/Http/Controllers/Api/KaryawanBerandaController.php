<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Data Beranda mobile karyawan.
 * Rumusnya SAMA dengan Admin\DashboardController supaya angka web & mobile selalu cocok:
 *  - pendapatan = orders.status_pembayaran = 'lunas', dikelompokkan per DATE(dibuat_pada)
 *  - total pesanan = jumlah orders pada DATE(dibuat_pada)
 */
class KaryawanBerandaController extends Controller
{
    public function index(Request $request)
    {
        // Hanya karyawan (dan admin) yang boleh akses
        abort_unless(in_array($request->user()->role, ['karyawan', 'admin']), 403, 'Akses ditolak.');

        // Tanggal grafik (default hari ini), format Y-m-d
        $tanggal = $this->tanggalTerpilih($request->query('tanggal'));
        $hariIni = now()->toDateString();

        // ---- Info hari ini ----
        $totalPesanan = DB::table('orders')->whereDate('dibuat_pada', $hariIni)->count();
        $pendapatan   = (float) DB::table('orders')
            ->where('status_pembayaran', 'lunas')
            ->whereDate('dibuat_pada', $hariIni)
            ->sum('total_harga');

        // ---- Pesanan baru = menunggu dikonfirmasi ----
        $pesananBaru = DB::table('orders')
            ->join('users', 'users.id_user', '=', 'orders.id_user')
            ->where('orders.status_pesanan', 'menunggu_dikonfirmasi')
            ->orderByDesc('orders.id_order')
            ->limit(20)
            ->get(['orders.id_order as id', 'orders.no_invoice as kode', 'users.nama as pelanggan']);

        // ---- Grafik 7 hari (6 hari sebelum $tanggal s/d $tanggal) ----
        $mulai = $tanggal->copy()->subDays(6)->toDateString();

        $perHari = DB::table('orders')
            ->selectRaw('DATE(dibuat_pada) as tgl, SUM(total_harga) as total')
            ->where('status_pembayaran', 'lunas')
            ->whereRaw('DATE(dibuat_pada) BETWEEN ? AND ?', [$mulai, $tanggal->toDateString()])
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        $namaHari = ['Min', 'Sen', 'Sel', 'Rabu', 'Kam', 'Jum', 'Sab']; // Carbon: 0 = Minggu
        $grafik = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $tanggal->copy()->subDays($i);
            $grafik[] = [
                'label'   => $namaHari[$d->dayOfWeek],
                'tanggal' => $d->toDateString(),
                'total'   => (int) ($perHari[$d->toDateString()] ?? 0),
            ];
        }

        return response()->json([
            'data' => [
                'total_pesanan'       => $totalPesanan,
                'pendapatan_hari_ini' => (int) $pendapatan,
                'pesanan_baru'        => $pesananBaru,
                'grafik'              => $grafik,
            ],
        ]);
    }

    private function tanggalTerpilih(mixed $input): Carbon
    {
        if (is_string($input) && $input !== '') {
            try {
                $d = Carbon::createFromFormat('Y-m-d', $input);
                if ($d && $d->format('Y-m-d') === $input) {
                    return $d->startOfDay();
                }
            } catch (\Throwable $e) {
            }
        }
        return now()->startOfDay();
    }
}
