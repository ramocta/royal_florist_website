<?php

// Simpan di: app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Daftar pesanan (dipakai halaman Status Order).
     */
    public function index()
    {
        $orders = DB::table('orders')
            ->join('users', 'users.id_user', '=', 'orders.id_user')
            ->select(
                'orders.id_order',
                'orders.no_invoice',
                'users.nama as nama_pelanggan',
                'orders.total_harga',
                'orders.status_pesanan',
                'orders.dibuat_pada'
            )
            ->orderByDesc('orders.dibuat_pada')
            ->get();

        return response()->json(['data' => $orders]);
    }

<<<<<<< HEAD
    // Update konfirmasi pesanan mobile
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status_pesanan' => [
                'required',
                Rule::in([
                    'menunggu_pembayaran',
                    'menunggu_dikonfirmasi',
                    'dikonfirmasi',
                    'diproses',
                    'dalam_pengiriman',
                    'siap_diambil',
                    'selesai',
                    'dibatalkan',
                ]),
            ],
            'nama_kurir' => 'nullable|string|max:100',
            'no_hp_kurir' => 'nullable|string|max:20',
            'estimasi_tiba' => 'nullable|date',
            'catatan_toko' => 'nullable|string',
        ]);

        $order = DB::table('orders')
            ->where('id_order', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        DB::transaction(function () use ($id, $validated) {
            $dataUpdate = [
                'status_pesanan' => $validated['status_pesanan'],
            ];

            foreach ([
                'nama_kurir',
                'no_hp_kurir',
                'estimasi_tiba',
                'catatan_toko',
            ] as $field) {
                if (array_key_exists($field, $validated)) {
                    $dataUpdate[$field] = $validated[$field];
                }
            }

            DB::table('orders')
                ->where('id_order', $id)
                ->update($dataUpdate);

            DB::table('order_status_history')->insert([
                'id_order' => $id,
                'status_pesanan' => $validated['status_pesanan'],
                'catatan' => $validated['catatan_toko'] ?? null,
                'diubah_oleh' => null,
=======
    /**
     * Detail satu pesanan (halaman Detail Pesanan dan Struk).
     * GET /api/orders/{id}
     */
    public function show($id)
    {
        $order = DB::table('orders')->where('id_order', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        $data = (array) $order;

        $user = DB::table('users')->where('id_user', $order->id_user)->first();

        // Alamat pengiriman milik pelanggan (yang utama dulu)
        $alamat = DB::table('alamat_pengiriman')
            ->where('id_user', $order->id_user)
            ->orderByDesc('is_utama')
            ->orderByDesc('id_alamat')
            ->first();

        // Nama pelanggan
        $data['nama_pelanggan'] = $user->nama ?? ($alamat->nama_penerima ?? null);

        // Telepon: users.nmr_telepon, cadangan alamat_pengiriman.nomor_hp
        $data['no_hp'] = !empty($user->nmr_telepon)
            ? $user->nmr_telepon
            : ($alamat->nomor_hp ?? null);

        // Alamat: alamat_tujuan pesanan, atau "Ambil di toko"
        if (!empty($order->alamat_tujuan)) {
            $data['alamat'] = $order->alamat_tujuan;
        } elseif ($order->jenis_pengambilan === 'ambil_di_toko') {
            $data['alamat'] = 'Ambil di toko';
        } else {
            $data['alamat'] = $user->alamat ?? null;
        }

        // Catatan pelanggan (diambil dari catatan alamat pengiriman)
        $data['catatan_pelanggan'] = $alamat->catatan ?? null;

        // Item produk
        $data['items'] = DB::table('order_items as oi')
            ->leftJoin('products as p', 'p.id_product', '=', 'oi.id_product')
            ->where('oi.id_order', $id)
            ->select(
                'oi.id_order_item',
                'oi.id_product',
                'p.nama_produk',
                'oi.jumlah',
                'oi.harga_satuan'
            )
            ->get();

        return response()->json(['data' => $data]);
    }

    /**
     * Ubah status pesanan.
     * PATCH /api/orders/{id}/status
     * body: status_pesanan, catatan (opsional)
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status_pesanan' => 'required|in:dikonfirmasi,diproses,siap_diambil,selesai,dibatalkan',
            'catatan'        => 'nullable|string|max:1000',
        ]);

        $order = DB::table('orders')->where('id_order', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        $catatan = $validated['catatan'] ?? null;

        // Hanya isi diubah_oleh kalau id pengguna benar-benar ada di tabel users
        $idPengguna = optional($request->user())->getKey();
        if ($idPengguna && !DB::table('users')->where('id_user', $idPengguna)->exists()) {
            $idPengguna = null;
        }

        DB::transaction(function () use ($id, $validated, $catatan, $idPengguna) {
            $update = ['status_pesanan' => $validated['status_pesanan']];

            // Catatan admin disimpan di kolom catatan_toko
            if (!empty($catatan)) {
                $update['catatan_toko'] = $catatan;
            }

            DB::table('orders')->where('id_order', $id)->update($update);

            // Catat riwayat perubahan status
            DB::table('order_status_history')->insert([
                'id_order'       => $id,
                'status_pesanan' => $validated['status_pesanan'],
                'catatan'        => $catatan,
                'diubah_oleh'    => $idPengguna,
                'diubah_pada'    => now(),
>>>>>>> cc14e30d8ef55f126c4d11ecf994c20d4ce168ff
            ]);
        });

        return response()->json([
<<<<<<< HEAD
            'message' => 'Status pesanan berhasil diperbarui.',
            'data' => DB::table('orders')
                ->where('id_order', $id)
                ->first(),
=======
            'message' => 'Status pesanan berhasil diubah',
            'data'    => DB::table('orders')->where('id_order', $id)->first(),
>>>>>>> cc14e30d8ef55f126c4d11ecf994c20d4ce168ff
        ]);
    }
}