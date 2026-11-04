<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StockManagementService;
use App\Models\Product;
use App\Models\Category;

class StockController extends Controller
{
    protected StockManagementService $stockManagementService;

    public function __construct(StockManagementService $stockManagementService)
    {
        $this->stockManagementService = $stockManagementService;
    }

    public function index(Request $request)
    {
        $summaryCards = $this->stockManagementService->getSummaryCards();
        $stokKategori = $this->stockManagementService->getCategoryBreakdown();
        $mutasiHariIni = $this->stockManagementService->getTodayMutations();
        $productsList = $this->stockManagementService->getFilteredProducts($request);
        $kategoriList = Category::select('id_ctg', 'category_name')->get();

        return view(
            'admin.stock.stock-management',
            compact(

                'summaryCards',
                'stokKategori',
                'mutasiHariIni',
                'productsList',
                'kategoriList',
            )
        );
    }

    public function adjust(Request $request, $id)
    {
        $request->validate([
            'stok_baru' => 'required|integer|min:0',
        ], [
            'stok_baru.required' => 'Stok baru harus diisi.',
            'stok_baru.integer' => 'Stok baru harus berupa angka.',
            'stok_baru.min' => 'Stok baru tidak boleh kurang dari 0.',
        ]);

        $product = Product::findOrFail($id);

        $this->stockManagementService->updateStock($product, $request->stok_baru);

        return redirect()->back()->with('success', 'Stok ' . $product->nama_produk . ' berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
    }
}
