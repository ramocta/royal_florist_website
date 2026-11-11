<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use App\Models\AlamatPengiriman;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        $pelanggan = auth()->user();

        // Mengambil semua alamat milik pelanggan yang sedang login.
        // Alamat utama ditampilkan paling atas.
        $daftarAlamat = AlamatPengiriman::where(
            'id_user',
            $pelanggan->id_user
        )
            ->orderByDesc('is_utama')
            ->orderByDesc('created_at')
            ->get();

        return view('pelanggan.checkout', compact(
            'pelanggan',
            'daftarAlamat'
        ));
    }

    public function store(Request $request)
    {
        // Proses penyimpanan pesanan akan kita buat
        // setelah struktur tabel database diperiksa.
    }
}