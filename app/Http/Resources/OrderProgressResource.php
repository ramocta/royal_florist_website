<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $progressMap = [
            'pending'  => ['step' => 0, 'percentage' => 0],
            'diproses' => ['step' => 1, 'percentage' => 25],
            'dikirim'  => ['step' => 3, 'percentage' => 75],
            'selesai'  => ['step' => 4, 'percentage' => 100],
            'batal'    => ['step' => 0, 'percentage' => 0],
        ];

        $currentProgress = $progressMap[strtolower($this->status_pesanan)] ?? ['step' => 0, 'percentage' => 0];

        return [
            'id_order'           => $this->id_order,
            'no_invoice'         => $this->no_invoice,
            'nama_pelanggan'     => $this->user?->name ?? 'Guest',
            'tanggal_pesan'      => $this->dibuat_pada ? $this->dibuat_pada->format('d M Y H:i') : null,
            'total_harga'        => (float) $this->total_harga,
            'status_pesanan'     => $this->status_pesanan,
            'progress'           => [
                'step_label' => $currentProgress['step'] . '/4',
                'percentage' => $currentProgress['percentage'],
            ],
        ];
    }
}
