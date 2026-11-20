<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderProgressService
{
    public function getProgressSummary(): array
    {
        $total    = Order::count();
        $diproses = Order::where('status_pesanan', 'diproses')->count();
        $dikirim  = Order::where('status_pesanan', 'dikirim')->count();
        $selesai  = Order::where('status_pesanan', 'selesai')->count();

        // Hitung persentase untuk progress bar & ringkasan status
        $percDiproses = $total > 0 ? round(($diproses / $total) * 100) : 0;
        $percDikirim  = $total > 0 ? round(($dikirim / $total) * 100) : 0;
        $percSelesai  = $total > 0 ? round(($selesai / $total) * 100) : 0;

        return [
            'cards' => [
                'total_pesanan'    => $total,
                'pesanan_diproses' => $diproses,
                'pesanan_dikirim'  => $dikirim,
                'pesanan_selesai'  => $selesai,
            ],
            'charts' => [
                'diproses' => ['count' => $diproses, 'percentage' => $percDiproses],
                'dikirim'  => ['count' => $dikirim, 'percentage' => $percDikirim],
                'selesai'  => ['count' => $selesai, 'percentage' => $percSelesai],
            ]
        ];
    }

    /**
     * Mengambil daftar pesanan dengan filter pencarian dan status
     */
    public function getOrderList(?string $search = null, ?string $status = null, int $perPage = 10)
    {
        return Order::with('user')
            ->when($search, function ($query) use ($search) {
                $query->where('no_invoice', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($status && $status !== 'semua', function ($query) use ($status) {
                $query->where('status_pesanan', $status);
            })
            ->latest('dibuat_pada')
            ->paginate($perPage);
    }

    /**
     * Update status pesanan & catat ke statusHistory jika ada
     */
    public function updateStatus(int $idOrder, array $data): Order
    {
        return DB::transaction(function () use ($idOrder, $data) {
            $order = Order::findOrFail($idOrder);

            $order->update([
                'status_pesanan' => $data['status_pesanan']
            ]);

            // Jika relasi statusHistory ada, simpan jejak perubahannya
            if (method_exists($order, 'statusHistory')) {
                $order->statusHistory()->create([
                    'status'  => $data['status_pesanan'],
                    'catatan' => $data['catatan'] ?? 'Pembaruan status dari dashboard manajemen progress',
                ]);
            }

            return $order;
        });
    }
}
