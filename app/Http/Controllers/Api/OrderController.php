<?php

// Simpan di: app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Detail satu pesanan (dipakai halaman Detail Pesanan).
     * Kolom/tabel yang tidak ada akan dilewati, tidak error.
     */
    public function show($id)
    {
        $order = DB::table('orders')
            ->where('orders.id_order', $id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        $data = (array) $order;

        // Info pelanggan
        $user = DB::table('users')->where('id_user', $order->id_user)->first();
        if ($user) {
            $u = (array) $user;
            $data['nama_pelanggan'] = $u['nama'] ?? null;
            $data['no_hp'] = $u['no_hp'] ?? $u['no_telepon'] ?? $u['telepon'] ?? null;
            if (empty($data['alamat_pengiriman']) && empty($data['alamat'])) {
                $data['alamat'] = $u['alamat'] ?? null;
            }
        }

        // Item produk: coba beberapa nama tabel yang umum
        $data['items'] = $this->ambilItems($id);

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

        $update = ['status_pesanan' => $validated['status_pesanan']];

        // Catatan admin disimpan ke kolom terpisah (kalau ada),
        // supaya tidak menimpa catatan pelanggan.
        if (!empty($validated['catatan']) && Schema::hasColumn('orders', 'catatan_admin')) {
            $update['catatan_admin'] = $validated['catatan'];
        }

        DB::table('orders')->where('id_order', $id)->update($update);

        return response()->json([
            'message' => 'Status pesanan berhasil diubah',
            'data'    => DB::table('orders')->where('id_order', $id)->first(),
        ]);
    }

    /**
     * Ambil item produk dari tabel detail pesanan (nama tabel menyesuaikan).
     */
    private function ambilItems($idOrder): array
    {
        $kandidat = ['detail_orders', 'order_items', 'detail_pesanan', 'order_details'];

        foreach ($kandidat as $tabel) {
            if (!Schema::hasTable($tabel) || !Schema::hasColumn($tabel, 'id_order')) {
                continue;
            }

            $query = DB::table($tabel)->where("$tabel.id_order", $idOrder);

            // Gabungkan nama produk kalau memungkinkan
            if (
                Schema::hasTable('produk')
                && Schema::hasColumn($tabel, 'id_produk')
                && Schema::hasColumn('produk', 'id_produk')
                && Schema::hasColumn('produk', 'nama')
            ) {
                $query->leftJoin('produk', 'produk.id_produk', '=', "$tabel.id_produk")
                      ->select("$tabel.*", 'produk.nama as nama_produk');
            } else {
                $query->select("$tabel.*");
            }

            return $query->get()->map(fn ($r) => (array) $r)->all();
        }

        return [];
    }
}