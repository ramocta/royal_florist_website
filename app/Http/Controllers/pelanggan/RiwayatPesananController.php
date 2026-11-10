<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RiwayatPesananController extends Controller
{
    private const KELOMPOK = [
        'diproses'     => ['menunggu_pembayaran', 'menunggu_dikonfirmasi', 'dikonfirmasi', 'diproses'],
        'siap_diambil' => ['siap_diambil'],
        'dikirim'      => ['dalam_pengiriman'],
        'selesai'      => ['selesai'],
    ];

    public function index(Request $request)
    {
        $filter = $request->query('status', 'semua');
        $orders = $request->user()->orders();

        $jumlah = ['semua' => (clone $orders)->count()];
        foreach (self::KELOMPOK as $kunci => $status) {
            $jumlah[$kunci] = (clone $orders)->whereIn('status_pesanan', $status)->count();
        }

        $daftar = $orders
            ->with(['items.product', 'statusHistory'])
            ->when(isset(self::KELOMPOK[$filter]), fn ($q) => $q->whereIn('status_pesanan', self::KELOMPOK[$filter]))
            ->latest('dibuat_pada')
            ->paginate(10)
            ->withQueryString();

        return view('pelanggan.riwayat-pesanan', compact('daftar', 'jumlah', 'filter'));
    }
}
