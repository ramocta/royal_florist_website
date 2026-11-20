<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use App\Models\AlamatPengiriman;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    // Ongkir tetap. Samakan dengan KeranjangController::ONGKIR dan konstanta COURIER di checkout.blade.php.
    private const ONGKIR = 20000;

    private const MAKS_JENIS_PRODUK = 50;   // batas jumlah jenis produk dalam satu pesanan
    private const MAKS_JUMLAH       = 99;   // batas jumlah per produk

    // Metode bayar yang boleh dipilih pelanggan kalau pesanan DIKIRIM. Ambil di toko selalu bayar di kasir.
    private const METODE_ONLINE = ['QRIS', 'transfer_bank'];

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

        // Diisi kalau pelanggan menekan "Beli Sekarang" di halaman detail produk (null = checkout dari keranjang)
        $beliLangsung = session('beli_langsung');

        // Transfer Bank baru ditawarkan kalau admin sudah mengisi rekening toko
        $transferTersedia = Setting::rekeningBank() !== null;

        return view('pelanggan.checkout', compact(
            'pelanggan',
            'daftarAlamat',
            'beliLangsung',
            'transferTersedia'
        ));
    }

    /*
     * Tombol "Lanjut Bayar": membuat pesanan, lalu
     *   - dikirim (QRIS / transfer bank) -> halaman pembayaran untuk bayar + unggah bukti
     *   - bayar di toko (ambil)          -> riwayat pesanan
     *
     * Harga, ongkir, dan total SELALU dihitung ulang di sini dari database.
     * Dari browser hanya dipakai id produk, jumlahnya, jenis pengambilan, dan pilihan metode bayar
     * (pilihan metode bayar tetap divalidasi: harus salah satu dari METODE_ONLINE, dan hanya berlaku untuk pesanan dikirim).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'jenis_pengambilan' => ['required', Rule::in(['dikirim', 'ambil_di_toko'])],
            // Wajib dipilih kalau pesanan dikirim. Kalau ambil di toko, nilainya diabaikan (selalu bayar di kasir).
            'metode_pembayaran' => ['nullable', 'required_if:jenis_pengambilan,dikirim', Rule::in([...self::METODE_ONLINE, 'bayar_di_toko'])],
            'sumber'            => ['nullable', Rule::in(['keranjang', 'langsung'])],
            'id_alamat'         => ['nullable', 'integer'],
        ], [
            'metode_pembayaran.required_if' => 'Silakan pilih metode pembayaran terlebih dahulu.',
            'metode_pembayaran.in'          => 'Metode pembayaran tidak valid.',
        ]);

        $dikirim = $data['jenis_pengambilan'] === 'dikirim';
        $sumber  = $data['sumber'] ?? 'keranjang';

        $items = $this->ambilItem($request, $sumber);
        if (! $items) {
            return back()->withInput()
                ->withErrors(['keranjang' => 'Keranjang masih kosong. Silakan pilih produk terlebih dahulu.']);
        }

        // Alamat harus milik pelanggan ini (id_alamat dari browser tidak langsung dipercaya)
        $alamat = null;
        if ($dikirim) {
            $alamat = AlamatPengiriman::where('id_user', $user->id_user)
                ->where('id_alamat', $data['id_alamat'] ?? 0)
                ->first();

            if (! $alamat) {
                return back()->withInput()
                    ->withErrors(['id_alamat' => 'Silakan pilih alamat pengiriman yang valid.']);
            }
        }

        // Ambil di toko: selalu bayar di kasir. Dikirim: pakai pilihan pelanggan (QRIS / transfer bank), dicek ulang di sini.
        $metode = $dikirim ? $data['metode_pembayaran'] : 'bayar_di_toko';

        if ($dikirim && ! in_array($metode, self::METODE_ONLINE, true)) {
            return back()->withInput()
                ->withErrors(['metode_pembayaran' => 'Pesanan yang dikirim harus dibayar lewat QRIS atau transfer bank.']);
        }

        // Jangan buat pesanan transfer kalau rekening toko belum diisi: pelanggan tidak akan tahu harus transfer ke mana
        if ($metode === 'transfer_bank' && Setting::rekeningBank() === null) {
            return back()->withInput()
                ->withErrors(['metode_pembayaran' => 'Transfer bank belum tersedia saat ini. Silakan pilih QRIS.']);
        }

        try {
            $order = DB::transaction(function () use ($user, $items, $dikirim, $metode, $alamat, $sumber) {
                // Kunci baris produk selama transaksi, supaya dua pembeli tidak bisa merebut stok yang sama
                $produk = Product::whereIn('id_product', array_keys($items))->lockForUpdate()->get()->keyBy('id_product');

                $subtotal = 0;
                $baris    = [];

                foreach ($items as $idProduk => $jumlah) {
                    $p = $produk[$idProduk] ?? null;

                    if (! $p || $p->status !== 'Tersedia') {
                        throw new \DomainException('Ada produk di pesanan yang sudah tidak tersedia. Silakan periksa keranjang Anda.');
                    }
                    if ($p->pakai_stok && $jumlah > $p->stok) {
                        throw new \DomainException("Stok {$p->nama_produk} tinggal {$p->stok}. Kurangi jumlah pesanan.");
                    }

                    $harga     = (float) $p->harga;
                    $subtotal += $harga * $jumlah;
                    $baris[]   = ['produk' => $p, 'jumlah' => $jumlah, 'harga' => $harga];
                }

                $order = new Order();
                // forceFill: semua nilainya dibuat server sendiri, tidak ada yang berasal dari input pengguna
                $order->forceFill([
                    'no_invoice'        => $this->buatNoInvoice(),
                    'id_user'           => $user->id_user,
                    'metode_pembayaran' => $metode,
                    'jenis_pengambilan' => $dikirim ? 'dikirim' : 'ambil_di_toko',
                    'alamat_tujuan'     => $alamat ? $this->teksAlamat($alamat) : null,
                    'status_pembayaran' => 'belum_dibayar',
                    // QRIS: menunggu pembayaran + bukti. Bayar di toko: langsung menunggu dikonfirmasi kasir.
                    'status_pesanan'    => $dikirim ? 'menunggu_pembayaran' : 'menunggu_dikonfirmasi',
                    'total_harga'       => $subtotal + ($dikirim ? self::ONGKIR : 0),
                ])->save();

                foreach ($baris as $b) {
                    OrderItem::create([
                        'id_order'     => $order->id_order,
                        'id_product'   => $b['produk']->id_product,
                        'jumlah'       => $b['jumlah'],
                        'harga_satuan' => $b['harga'],   // harga saat dibeli, supaya tidak berubah kalau harga produk diubah nanti
                    ]);

                    // Stok langsung dikurangi (ditahan untuk pesanan ini). Kalau pesanan dibatalkan, stok perlu dikembalikan.
                    if ($b['produk']->pakai_stok) {
                        Product::where('id_product', $b['produk']->id_product)->decrement('stok', $b['jumlah']);
                    }
                }

                $labelMetode = ['QRIS' => 'QRIS', 'transfer_bank' => 'transfer bank', 'bayar_di_toko' => 'bayar di toko'][$metode];
                $catatan = 'Pesanan dibuat oleh pelanggan (' . ($dikirim ? 'dikirim kurir' : 'ambil di toko') . ", bayar: {$labelMetode}).";
                $wrapping = session('beli_langsung.wrapping');
                if ($sumber === 'langsung' && $wrapping) {
                    $catatan .= " Warna wrapping: {$wrapping}.";   // belum ada kolom khusus wrapping di order_items
                }

                OrderStatusHistory::create([
                    'id_order'       => $order->id_order,
                    'status_pesanan' => $order->status_pesanan,
                    'catatan'        => $catatan,
                    'diubah_oleh'    => $user->id_user,
                ]);

                return $order;
            });
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['keranjang' => $e->getMessage()]);
        }

        if ($sumber === 'langsung') {
            session()->forget('beli_langsung');
        }

        if ($dikirim) {
            return redirect()->route('pelanggan.pembayaran.show', $order);
        }

        return redirect()->route('pelanggan.riwayat')
            ->with('success', "Pesanan {$order->no_invoice} berhasil dibuat. Silakan bayar langsung di kasir saat mengambil pesanan.");
    }

    // Daftar produk dan jumlahnya: dari session (Beli Sekarang) atau dari keranjang yang dikirim browser
    private function ambilItem(Request $request, string $sumber): array
    {
        if ($sumber === 'langsung') {
            $s = session('beli_langsung');

            return $s ? [(int) $s['id_product'] => (int) $s['jumlah']] : [];
        }

        $mentah = json_decode((string) $request->input('cart_items'), true);
        if (! is_array($mentah)) {
            return [];
        }

        $items = [];
        foreach ($mentah as $baris) {
            $id     = (int) ($baris['id_product'] ?? 0);
            $jumlah = (int) ($baris['jumlah'] ?? 0);

            if ($id < 1 || $jumlah < 1) {
                continue;
            }

            // Produk yang sama muncul dua kali digabung, dan jumlahnya dibatasi
            $items[$id] = min(self::MAKS_JUMLAH, ($items[$id] ?? 0) + $jumlah);
        }

        return array_slice($items, 0, self::MAKS_JENIS_PRODUK, true);
    }

    // Format: RF-20261112-0001 (16 karakter, kolom no_invoice muat 20). Urutan naik per hari.
    private function buatNoInvoice(): string
    {
        $awalan   = 'RF-' . now()->format('Ymd') . '-';
        $terakhir = Order::where('no_invoice', 'like', $awalan . '%')->lockForUpdate()->max('no_invoice');
        $urut     = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $awalan . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    // Disalin sebagai teks ke pesanan, supaya alamat di pesanan tidak berubah kalau alamat tersimpan diubah atau dihapus
    private function teksAlamat(AlamatPengiriman $a): string
    {
        $teks = "{$a->nama_penerima} ({$a->nomor_hp}) - {$a->alamat_lengkap}, {$a->kota}";

        return Str::limit($teks, 255, '');   // kolom alamat_tujuan maksimal 255 karakter
    }
}