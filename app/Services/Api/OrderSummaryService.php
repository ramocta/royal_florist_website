<?php

namespace App\Services\Api;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderSummaryService
{
    private const TIMEZONE = 'Asia/Jakarta';

    /**
     * Ringkasan mingguan (Senin–Minggu): 4 kartu statistik + perbandingan dengan minggu sebelumnya.
     * $tanggal = tanggal mana pun di dalam minggu yang diinginkan (default: hari ini).
     */
    public function getWeeklySummary(?string $tanggal = null): array
    {
        $date = $tanggal ? Carbon::parse($tanggal, self::TIMEZONE) : Carbon::now(self::TIMEZONE);

        $start = $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end   = $date->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $prevStart = $start->copy()->subWeek();
        $prevEnd   = $end->copy()->subWeek();

        $current  = $this->countByStatus($start, $end);
        $previous = $this->countByStatus($prevStart, $prevEnd);

        $thisWeekStart = Carbon::now(self::TIMEZONE)->startOfWeek(Carbon::MONDAY)->startOfDay();

        return [
            'periode' => [
                'tanggal_mulai'   => $start->toDateString(),
                'tanggal_selesai' => $end->toDateString(),
                'label'           => $start->copy()->locale('id')->translatedFormat('d M')
                                     . ' – ' . $end->copy()->locale('id')->translatedFormat('d M Y'), // 28 Sep – 04 Okt 2026
                'is_minggu_ini'   => $start->equalTo($thisWeekStart),
            ],
            'cards' => [
                $this->buildCard('pesanan_masuk', 'Pesanan Masuk', $current['total'], $previous['total']),
                $this->buildCard('dalam_proses', 'Dalam Proses', $current['diproses'], $previous['diproses']),
                $this->buildCard('selesai', 'Selesai', $current['selesai'], $previous['selesai']),
                $this->buildCard('dibatalkan', 'Dibatalkan', $current['dibatalkan'], $previous['dibatalkan']),
            ],
        ];
    }

    /**
     * Pesanan terbaru untuk section "Pesanan Terbaru".
     */
    public function getRecentOrders(int $limit = 5): Collection
    {
        return Order::with(['pelanggan', 'user'])
            ->latest('dibuat_pada')
            ->limit($limit)
            ->get();
    }

    /**
     * Daftar pesanan (tombol "Lihat Semua") dengan pencarian & filter status.
     */
    public function getOrderList(?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return Order::with(['pelanggan', 'user'])
            ->when($search, function ($query) use ($search) {
                // Dikelompokkan agar OR tidak merusak filter status di bawahnya
                $query->where(function ($q) use ($search) {
                    $q->where('no_invoice', 'like', "%{$search}%")
                        ->orWhereHas('pelanggan', fn ($p) => $p->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when($status && $status !== 'semua', fn ($query) => $query->where('status_pesanan', $status))
            ->latest('dibuat_pada')
            ->paginate($perPage);
    }

    /**
     * Hitung pesanan per status dalam satu rentang waktu (1 query).
     */
    private function countByStatus(Carbon $start, Carbon $end): array
    {
        $rows = Order::query()
            ->whereBetween('dibuat_pada', [$start, $end])
            ->selectRaw('status_pesanan, COUNT(*) as total')
            ->groupBy('status_pesanan')
            ->pluck('total', 'status_pesanan');

        return [
            'total'      => (int) $rows->sum(),
            'diproses'   => (int) ($rows['diproses'] ?? 0),
            'selesai'    => (int) ($rows['selesai'] ?? 0),
            'dibatalkan' => (int) ($rows['dibatalkan'] ?? 0),
        ];
    }

    private function buildCard(string $key, string $label, int $current, int $previous): array
    {
        if ($previous > 0) {
            $percent = (int) round((($current - $previous) / $previous) * 100);
        } else {
            $percent = $current > 0 ? 100 : 0;
        }

        $trend = $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'flat');
        $sign  = $percent > 0 ? '+' : '';

        return [
            'key'    => $key,
            'label'  => $label,
            'jumlah' => $current,
            'perubahan' => [
                'persen' => $percent,
                'trend'  => $trend, // up | down | flat (untuk warna hijau/merah/abu di UI)
                'label'  => "{$sign}{$percent}% dari minggu lalu",
            ],
        ];
    }
}