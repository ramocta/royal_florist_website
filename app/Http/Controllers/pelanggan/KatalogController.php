<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    private const CATEGORY_NAME = 'category_name';
    private const PER_PAGE = 9;

    public function index()
    {
        $categories = Category::with('subcategories')->orderBy('id_ctg')->get()->map(fn ($c) => [
            'id'        => $c->getKey(),
            'nama'      => $c->{self::CATEGORY_NAME},
            'sub_label' => $c->{self::CATEGORY_NAME} === 'Papan Bunga' ? 'Ukuran' : 'Jenis',
            'subs'      => $c->subcategories->map(fn ($s) => ['id' => $s->id_subctg, 'nama' => $s->subcategory_name])->values(),
        ]);

        return view('pelanggan.katalog', compact('categories'));
    }

    public function data(Request $request)
    {
        $query = Product::with(['category', 'subcategory'])->where('is_active', true);

        if ($s = trim((string) $request->input('q', ''))) {
            $query->where('nama_produk', 'like', "%{$s}%");
        }
        if ($request->filled('category')) {
            $query->where('id_ctg', $request->input('category'));
        }
        if ($request->filled('sub')) {
            $query->where('id_subctg', $request->input('sub'));
        }

        $query->orderByDesc('tersedia');

        match ($request->input('sort')) {
            'termurah' => $query->orderBy('harga'),
            'termahal' => $query->orderByDesc('harga'),
            'nama'     => $query->orderBy('nama_produk'),
            default    => $query->orderByDesc('id_product'),
        };

        $page = $query->paginate(self::PER_PAGE);

        return response()->json([
            'items' => $page->getCollection()->map(fn ($p) => [
                'id'         => $p->id_product,
                'nama'       => $p->nama_produk,
                'deskripsi'  => $p->deskripsi,
                'kategori'   => optional($p->category)->{self::CATEGORY_NAME} ?? '-',
                'sub'        => optional($p->subcategory)->subcategory_name,
                'harga'      => (float) $p->harga,
                'tersedia'   => $p->status === 'Tersedia',
                'pakai_stok' => $p->pakai_stok,
                'stok'       => $p->stok,
                'foto_url'   => $p->foto_url,
            ])->values(),
            'pagination' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'total'        => $page->total(),
                'from'         => $page->firstItem() ?? 0,
                'to'           => $page->lastItem() ?? 0,
            ],
        ]);
    }
}
