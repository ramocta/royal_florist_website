<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class StockManagementService
{
    public function getSummaryCards(): array
    {
        $total_stok = Product::where('is_active', true)->count();

        $stok_aman = Product::where('is_active', true)->where('stok', '>', 10)->count();

        $stok_menipis = Product::where('is_active', true)->whereBetween('stok', [1, 10])->count();

        $stok_kritis = Product::where('is_active', true)->where(function ($q) {
            $q->where('stok', '<=', 0)->orWhere('tersedia', false);
        })->count();

        return [
            'total_stok' => ['nilai' => $total_stok, 'trend' => '+0% dari minggu sebelumnya', 'kelas' => 'naik'],
            'stok_aman' => ['nilai' => $stok_aman, 'trend' => '+0% dari minggu sebelumnya', 'kelas' => 'naik'],
            'stok_menipis' => ['nilai' => $stok_menipis, 'trend' => '+0% dari minggu sebelumnya', 'kelas' => 'turun'],
            'stok_kritis' => ['nilai' => $stok_kritis, 'trend' => '+0% dari minggu sebelumnya', 'kelas' => 'turun'],
        ];
    }

    public function getCategoryBreakdown(): array
    {
        return Category::withSum(['products as total_stok' => function ($query) {
            $query->where('is_active', true);
        }], 'stok')
            ->get()
            ->map(function ($category) {
                return [
                    'nama'  => $category->category_name,
                    'total' => $category->total_stok ?? 0,
                ];
            })->all();
    }

    public function getTodayMutations(): array
    {
        return [
            'masuk'       => 0,
            'keluar'      => 0,
            'penyesuaian' => 0,
            'top_keluar'  => null,
        ];
    }

    public function getFilteredProducts($request)
    {
        $query = Product::with(['category', 'subcategory']);

        if ($request->filled('q')) {
            $keyword = ltrim($request->q, '#PRD-');
            $query->where(function ($q) use ($keyword, $request) {
                $q->where('nama_produk', 'like', "%{$request->q}%")
                    ->orWhere('id_product', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('id_ctg', $request->kategori);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'aman'    => $query->where('stok', '>', 10),
                'menipis' => $query->whereBetween('stok', [1, 10]),
                'kritis'  => $query->where(function ($q) {
                    $q->where('stok', '<=', 0)->orWhere('tersedia', false);
                }),
                default   => null,
            };
        }

        return $query->orderBy('id_product', 'desc')->paginate(10)->withQueryString();
    }

    public function updateStock(Product $product, int $stokBaru): void
    {
        $product->update([
            'stok'     => $stokBaru,
            'tersedia' => $stokBaru > 0, // status otomatis tersedia jika stok > 0
        ]);
    }
}
