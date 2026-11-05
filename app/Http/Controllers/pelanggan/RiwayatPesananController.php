<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RiwayatPesananController extends Controller
{
    private const KELOMPOK = [
        'diproses' => ['menunggu_pembayaran', 'menunggu_dikonfirmasi', 'diproses', 'siap_diambil'],
        'selesai'  => ['selesai'],
    ];

    public function index(Request $request)
    {
        $filter = $request->query('status', 'semua');
        $orders = $request->user()->orders();

        $jumlah = [
            'semua'    => (clone $orders)->count(),
            'diproses' => (clone $orders)->whereIn('status_pesanan', self::KELOMPOK['diproses'])->count(),
            'selesai'  => (clone $orders)->whereIn('status_pesanan', self::KELOMPOK['selesai'])->count(),
        ];

        $daftar = $orders
            ->with('items.product')
            ->when(isset(self::KELOMPOK[$filter]), fn ($q) => $q->whereIn('status_pesanan', self::KELOMPOK[$filter]))
            ->latest('dibuat_pada')
            ->paginate(10)
            ->withQueryString();

        return view('pelanggan.riwayat-pesanan', compact('daftar', 'jumlah', 'filter'));
    }
}
