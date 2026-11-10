<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BerandaController extends Controller
{
    /*
     * Teks kartu kategori mengikuti desain Figma. Kuncinya adalah nilai kolom category_name di tabel categories.
     * Kategori yang tidak ada di daftar ini tidak ditampilkan di beranda.
     */
    private const KARTU_KATEGORI = [
        'Buket'       => ['judul' => 'Buket Bunga Segar',  'deskripsi' => 'Mawar, lily, tulip & aneka bunga.',           'tombol' => 'Lihat Katalog Buket'],
        'Papan Bunga' => ['judul' => 'Papan Bunga Ucapan', 'deskripsi' => 'Untuk Happy Wedding, Graduation, & Duka Cita.', 'tombol' => 'Lihat Papan Bunga'],
        'Hantaran'    => ['judul' => 'Seserahan',          'deskripsi' => 'Box estetik & kayu, hiasan bunga.',           'tombol' => 'Lihat Box Seserahan'],
    ];

    public function index()
    {
        return view('pelanggan.beranda', [
            'kategori' => $this->kategori(),
            'terlaris' => $this->terlaris(),
        ]);
    }

    // Kartu kategori. Fotonya diambil dari produk aktif terbaru di kategori itu, jadi tidak perlu diunggah terpisah.
    private function kategori(): array
    {
        $hasil = [];

        foreach (Category::orderBy('id_ctg')->get() as $k) {
            $kartu = self::KARTU_KATEGORI[$k->category_name] ?? null;
            if (! $kartu) {
                continue;
            }

            $contoh = Product::where('id_ctg', $k->id_ctg)
                ->where('is_active', true)
                ->whereNotNull('foto_produk')
                ->orderByDesc('id_product')
                ->first();

            $hasil[] = $kartu + ['gambar' => $contoh?->foto_url];
        }

        return $hasil;
    }

    /*
     * Produk terlaris = paling banyak terjual dalam 7 hari terakhir. Pesanan yang dibatalkan tidak dihitung.
     * Kalau yang terjual kurang dari 4 (misalnya toko baru buka), sisanya diisi produk aktif terbaru
     * supaya bagian ini tidak pernah kosong.
     */
    private function terlaris(): array
    {
        $idTerjual = DB::table('order_items')
            ->join('orders', 'orders.id_order', '=', 'order_items.id_order')
            ->where('orders.dibuat_pada', '>=', now()->subDays(7))
            ->where('orders.status_pesanan', '!=', 'dibatalkan')
            ->selectRaw('order_items.id_product, SUM(order_items.jumlah) AS terjual')
            ->groupBy('order_items.id_product')
            ->orderByDesc('terjual')
            ->limit(12)             // lebih dari 4, karena sebagian bisa tersaring (nonaktif / habis)
            ->pluck('id_product')
            ->all();

        $produk = Product::with('subcategory')->whereIn('id_product', $idTerjual)->get()->keyBy('id_product');

        $daftar = collect($idTerjual)
            ->map(fn ($id) => $produk[$id] ?? null)
            ->filter(fn ($p) => $p && $p->status === 'Tersedia')
            ->take(4);

        if ($daftar->count() < 4) {
            $tambahan = Product::with('subcategory')
                ->where('is_active', true)
                ->whereNotIn('id_product', $daftar->pluck('id_product')->all())
                ->orderByDesc('id_product')
                ->get()
                ->filter(fn ($p) => $p->status === 'Tersedia')
                ->take(4 - $daftar->count());

            $daftar = $daftar->concat($tambahan);
        }

        return $daftar->map(fn ($p) => [
            'id'          => $p->id_product,
            'nama'        => $p->nama_produk,
            // Baris kecil di bawah nama: potongan deskripsi, atau nama jenis produk kalau deskripsi kosong
            'spesifikasi' => Str::limit(strip_tags((string) $p->deskripsi), 38) ?: ($p->subcategory->subcategory_name ?? ''),
            'harga'       => (int) $p->harga,
            'gambar'      => $p->foto_url,
            'maks'        => $p->pakai_stok ? $p->stok : null,   // null = tidak dibatasi stok
        ])->values()->all();
    }
}
