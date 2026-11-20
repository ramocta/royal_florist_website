<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecentOrderResource;
use App\Services\Api\OrderSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderSummaryController extends Controller
{
    public function __construct(
        private readonly OrderSummaryService $summaryService,
    ) {}

    /**
     * GET /api/ringkasan
     * Seluruh data halaman Ringkasan dalam satu request.
     * Query opsional: ?tanggal=2026-10-04 (tanggal mana pun dalam minggu yang diinginkan) &limit=5
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'limit'   => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'nama' => $user->nama,
                    'role' => $user->role ?? 'Staff Florist',
                ],
                'ringkasan_minggu_ini' => $this->summaryService->getWeeklySummary($validated['tanggal'] ?? null),
                'pesanan_terbaru'    => RecentOrderResource::collection(
                    $this->summaryService->getRecentOrders($validated['limit'] ?? 5)
                ),
            ],
        ]);
    }

    /**
     * GET /api/ringkasan/pesanan
     * Untuk tombol "Lihat Semua".
     * Query opsional: ?search=&status=&per_page=10
     */
    public function pesanan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search'   => ['nullable', 'string', 'max:100'],
            'status'   => ['nullable', 'in:semua,dikonfirmasi,diproses,dikirim,selesai,dibatalkan'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $orders = $this->summaryService->getOrderList(
            $validated['search'] ?? null,
            $validated['status'] ?? null,
            $validated['per_page'] ?? 10,
        );

        return RecentOrderResource::collection($orders)
            ->additional(['success' => true])
            ->response();
    }
}