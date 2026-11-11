<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderProgressService;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderProgressResource;
use Exception;

class OrderProgressController extends Controller
{
    public function __construct(
        protected OrderProgressService $progressService
    ) {}

    public function index(Request $request)
    {
        $summary = $this->progressService->getProgressSummary();
        $orders  = $this->progressService->getOrderList(
            $request->query('search'),
            $request->query('status'),
            10
        );

        return view('admin.progresmonitoring.progres-monitoring', compact('summary', 'orders'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id)
    {
        try {
            $this->progressService->updateStatus($id, $request->validated());

            return redirect()
                ->back()
                ->with('success', 'Status pesanan berhasil diperbarui!');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
