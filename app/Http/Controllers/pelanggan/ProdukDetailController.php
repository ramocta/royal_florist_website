<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProdukDetailController extends Controller
{
    // Jumlah maksimal sekali beli untuk produk yang tidak memakai stok
    private const JUMLAH_MAKS = 99;

    // Pilihan warna pembungkus (wrapping) sesuai desain Figma. Dipakai halaman detail dan validasi di bawah.
    public const WRAPPING = ['Merah Marun', 'Pink Soft', 'Cream'];

    // Halaman detail produk
    public function show(Product $produk)
    {
        // Produk yang dinonaktifkan admin tidak boleh dibuka walau URL-nya diketik manual
        abort_unless($produk->is_active, 404);

        $produk->load(['category', 'subcategory']);

        return view('pelanggan.produk-detail', [
            'produk'     => $produk,
            'bestseller' => $this->bestseller($produk->id_product),
            'maks'       => $produk->pakai_stok ? max(0, (int) $produk->stok) : self::JUMLAH_MAKS,
        ]);
    }

    /*
     * "Beli Sekarang": beli satu produk langsung ke checkout tanpa lewat keranjang.
     * Yang disimpan di session hanya id, jumlah, dan pilihan wrapping. Harga TIDAK disimpan di sini,
     * jadi checkout harus mengambil harga dari database (harga dari browser tidak boleh dipercaya).
     * Halaman checkout membacanya lewat: session('beli_langsung')
     */
    public function beliLangsung(Request $request, Product $produk)
    {
        abort_unless($produk->is_active, 404);

        $data = $request->validate([
            'jumlah'   => ['required', 'integer', 'min:1', 'max:' . self::JUMLAH_MAKS],
            'wrapping' => ['nullable', Rule::in(self::WRAPPING)],
        ], [
            'jumlah.*'    => 'Jumlah pesanan tidak valid.',
            'wrapping.in' => 'Pilihan warna wrapping tidak valid.',
        ]);

        // Validasi ulang di server walau tombolnya sudah dibatasi di browser
        if ($produk->status !== 'Tersedia') {
            return back()->with('error', 'Produk ini sedang tidak tersedia.');
        }

        if ($produk->pakai_stok && $data['jumlah'] > $produk->stok) {
            return back()->with('error', "Stok tersisa {$produk->stok}. Kurangi jumlah pesanan.");
        }

        session(['beli_langsung' => [
            'id_product' => $produk->id_product,
            'jumlah'     => (int) $data['jumlah'],
            'wrapping'   => $data['wrapping'] ?? null,
        ]]);

        return redirect()->route('checkout');
    }

    // Lencana BESTSELLER: produk termasuk 3 terlaris dalam 30 hari terakhir (pesanan batal tidak dihitung)
    private function bestseller(int $idProduk): bool
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id_order', '=', 'order_items.id_order')
            ->where('orders.dibuat_pada', '>=', now()->subDays(30))
            ->where('orders.status_pesanan', '!=', 'dibatalkan')
            ->groupBy('order_items.id_product')
            ->orderByRaw('SUM(order_items.jumlah) DESC')
            ->limit(3)
            ->pluck('order_items.id_product')
            ->contains($idProduk);
    }
}
