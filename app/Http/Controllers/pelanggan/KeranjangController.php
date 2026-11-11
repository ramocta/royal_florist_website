<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    // Estimasi ongkir tetap (sesuai desain). Ubah angkanya di sini kalau tarifnya berubah.
    private const ONGKIR = 20000;

    // Halaman keranjang. Isi keranjang disimpan di browser (localStorage 'rf_cart'),
    // jadi halaman ini hanya menyiapkan tampilannya.
    public function index()
    {
        return view('pelanggan.keranjang', ['ongkir' => self::ONGKIR]);
    }

    // Data produk untuk id yang ada di keranjang, dipanggil lewat fetch() dari halaman keranjang.
    // Harga dan ketersediaan SELALU diambil dari database, bukan dari browser.
    public function data(Request $request)
    {
        $ids = collect((array) $request->query('ids'))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->take(100)
            ->values();

        $produk = Product::with(['category', 'subcategory'])
            ->whereIn('id_product', $ids)
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id_product,
                'nama'       => $p->nama_produk,
                'kategori'   => optional($p->category)->category_name ?? '',
                'sub'        => optional($p->subcategory)->subcategory_name,
                'harga'      => (int) $p->harga,
                'tersedia'   => $p->status === 'Tersedia', // aktif + stok/tersedia
                'pakai_stok' => $p->pakai_stok,
                'stok'       => $p->stok,
                'foto_url'   => $p->foto_url,
            ])
            ->values();

        return response()->json($produk);
    }
}