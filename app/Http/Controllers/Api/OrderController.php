<?php

// Simpan di: app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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
}