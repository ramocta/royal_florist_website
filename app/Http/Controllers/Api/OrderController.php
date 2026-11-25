<?php

// Simpan di: app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
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
            ]);
        });

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui.',
            'data' => DB::table('orders')
                ->where('id_order', $id)
                ->first(),
        ]);
    }
}