<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;

/**
 * Mengumpulkan notifikasi untuk lonceng di header admin.
 *
 * Notifikasi tidak disimpan di tabel sendiri: dihitung langsung dari data yang sudah ada.
 * Jadi notifikasi otomatis hilang begitu masalahnya selesai (pesanan sudah dikonfirmasi,
 * stok sudah ditambah), dan tidak ada status "sudah dibaca" yang harus diurus.
 */
class NotifikasiAdminService
{
    // Sama dengan halaman Stok: stok 1-10 dianggap menipis
    private const BATAS_MENIPIS = 10;

    // Jumlah maksimal baris per jenis yang ditampilkan di popup (angka pada badge tetap menghitung semuanya)
    private const MAKS_PER_JENIS = 5;

    /**
     * @return array{total:int, items:array<int, array{jenis:string, judul:string, teks:string, waktu:?string, url:string}>}
     */
    public function ambil(): array
    {
        [$jumlahPesanan, $itemPesanan] = $this->pesananMenungguKonfirmasi();
        [$jumlahStok, $itemStok]       = $this->stokBermasalah();

        return [
            'total' => $jumlahPesanan + $jumlahStok,
            // Pesanan ditaruh di atas karena pelanggan sedang menunggu
            'items' => array_merge($itemPesanan, $itemStok),
        ];
    }

    // Pesanan yang sudah dibayar / diajukan pelanggan dan menunggu kasir
    private function pesananMenungguKonfirmasi(): array
    {
        $query = Order::where('status_pesanan', 'menunggu_dikonfirmasi');

        $jumlah = (clone $query)->count();

        $items = $query->with('user:id_user,nama')
            ->latest('dibuat_pada')
            ->limit(self::MAKS_PER_JENIS)
            ->get()
            ->map(fn (Order $o) => [
                'jenis' => 'pesanan',
                'judul' => 'Pesanan menunggu konfirmasi',
                // Pesanan yang dibuat admin di toko tidak punya id_user, jadi nama bisa kosong
                'teks'  => $o->no_invoice . ' • ' . ($o->user->nama ?? 'Pelanggan toko')
                           . ' • Rp ' . number_format($o->total_harga, 0, ',', '.'),
                // locale('id') dipaksa karena APP_LOCALE masih 'en'
                'waktu' => ($o->dibayar_pada ?? $o->dibuat_pada)?->locale('id')->diffForHumans(),
                'url'   => route('admin.pesanan.index', ['status' => 'menunggu_dikonfirmasi']),
            ])
            ->all();

        return [$jumlah, $items];
    }

    // Produk yang dihitung per stok (Buket Fresh Flower) dan stoknya habis atau menipis
    private function stokBermasalah(): array
    {
        $query = Product::where('is_active', true)
            // Produk non-stok memakai flag "tersedia", stok 0 di situ bukan masalah, jadi disaring keluar
            ->whereHas('subcategory', fn ($q) => $q->where('pakai_stok', true))
            ->where('stok', '<=', self::BATAS_MENIPIS);

        $jumlah = (clone $query)->count();

        $items = $query->orderBy('stok')   // yang paling sedikit tampil duluan
            ->limit(self::MAKS_PER_JENIS)
            ->get()
            ->map(function (Product $p) {
                $habis = $p->stok <= 0;

                return [
                    'jenis' => $habis ? 'habis' : 'menipis',
                    'judul' => $habis ? 'Stok habis' : 'Stok menipis',
                    'teks'  => $habis ? $p->nama_produk : "{$p->nama_produk} • sisa {$p->stok}",
                    'waktu' => null,   // stok tidak punya "waktu kejadian"
                    'url'   => route('admin.stok.index', ['status' => $habis ? 'kritis' : 'menipis']),
                ];
            })
            ->all();

        return [$jumlah, $items];
    }
}
