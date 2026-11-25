<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentOrderResource extends JsonResource
{
    private const STATUS_LABEL = [
        'dikonfirmasi' => 'Dikonfirmasi',
        'diproses'     => 'Diproses',
        'dikirim'      => 'Dikirim',
        'selesai'      => 'Selesai',
        'dibatalkan'   => 'Dibatalkan',
    ];

    public function toArray(Request $request): array
    {
        $status = $this->status_pesanan;

        return [
            'id_order'    => $this->id_order,
            'no_invoice'  => $this->no_invoice,                   // INV-20261004-001
            'pelanggan'   => $this->nama_pelanggan,                // Siti Maisyaroh (accessor di model Order)
            'jam'         => $this->dibuat_pada?->timezone('Asia/Jakarta')->format('H:i'), // 14:32
            'status'      => $status,                             // dipakai UI untuk warna badge
            'status_label'=> self::STATUS_LABEL[$status] ?? ucfirst((string) $status),
        ];
    }
}