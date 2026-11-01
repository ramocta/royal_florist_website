<?php

// Namespace disamakan dengan KaryawanController (folder "admin" huruf kecil)
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProdukController extends Controller
{
    private const CATEGORY_NAME = 'category_name';   // kolom nama di tabel categories
    private const PER_PAGE = 5;
    private const KATEGORI_BUKET = 'Buket';
    private const KATEGORI_PAPAN = 'Papan Bunga';

    // ---------- halaman ----------

    public function index()
    {
        return view('admin.produk-index', ['categories' => $this->categories()]);
    }

    public function create()
    {
        return view('admin.produk-create', ['categories' => $this->categories()]);
    }

    public function edit(Product $produk)
    {
        return view('admin.produk-edit', ['produk' => $produk, 'categories' => $this->categories()]);
    }

    // ---------- simpan / ubah / hapus ----------

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('foto_produk')) {
            $data['foto_produk'] = $request->file('foto_produk')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $produk)
    {
        $data = $this->validated($request);

        if ($request->hasFile('foto_produk')) {
            if ($produk->foto_produk) {
                Storage::disk('public')->delete($produk->foto_produk);
            }
            $data['foto_produk'] = $request->file('foto_produk')->store('products', 'public');
        }

        $produk->update($data);

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $produk)
    {
        try {
            $produk->delete();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Produk sudah dipakai di pesanan dan tidak bisa dihapus. Nonaktifkan saja produknya.',
            ], 409);
        }

        if ($produk->foto_produk) {
            Storage::disk('public')->delete($produk->foto_produk);
        }

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }

    // ---------- data realtime (JSON) ----------

    public function data(Request $request)
    {
        $query = Product::with(['category', 'subcategory']);

        if ($s = trim((string) $request->input('q', ''))) {
            if (preg_match('/^#?(?:prd-?)?(\d+)$/i', $s, $m)) {
                $query->where('id_product', (int) $m[1]);
            } else {
                $query->where('nama_produk', 'like', "%{$s}%");
            }
        }

        if ($request->filled('category')) {
            $query->where('id_ctg', $request->input('category'));
        }

        if ($request->filled('sub')) {
            $query->where('id_subctg', $request->input('sub'));
        }

        $page = $query->orderByDesc('id_product')->paginate(self::PER_PAGE);

        return response()->json([
            'items' => $page->getCollection()->map(fn ($p) => [
                'id'       => $p->id_product,
                'sku'      => $p->sku,
                'nama'     => $p->nama_produk,
                'kategori' => optional($p->category)->{self::CATEGORY_NAME} ?? '-',
                'sub'      => optional($p->subcategory)->subcategory_name,
                'harga'    => (float) $p->harga,
                'stok'     => $p->stok,
                'pakai_stok' => $p->pakai_stok,
                'status'   => $p->status,
                'foto_url' => $p->foto_url,
            ])->values(),
            'pagination' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'from'         => $page->firstItem() ?? 0,
                'to'           => $page->lastItem() ?? 0,
                'total'        => $page->total(),
            ],
            'stats' => [
                'total'    => Product::count(),
                'buket'    => $this->countByCategory(self::KATEGORI_BUKET),
                'papan'    => $this->countByCategory(self::KATEGORI_PAPAN),
                'nonaktif' => Product::where(fn ($q) => $q->where('is_active', false)->orWhere('tersedia', false))->count(),
            ],
        ]);
    }

    public function export()
    {
        $rows = Product::with('category')->orderBy('id_product')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['SKU', 'Nama Produk', 'Kategori', 'Harga', 'Stok', 'Status']);
            foreach ($rows as $p) {
                fputcsv($out, [$p->sku, $p->nama_produk, optional($p->category)->{self::CATEGORY_NAME}, $p->harga, $p->stok, $p->status]);
            }
            fclose($out);
        }, 'produk-royal-florist-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---------- helper ----------

    private function categories()
    {
        return Category::with('subcategories')->orderBy('id_ctg')->get()->map(fn ($c) => [
            'id'        => $c->getKey(),
            'nama'      => $c->{self::CATEGORY_NAME},
            'sub_label' => $c->{self::CATEGORY_NAME} === self::KATEGORI_PAPAN ? 'Ukuran' : 'Jenis',
            'subs'      => $c->subcategories->map(fn ($s) => ['id' => $s->id_subctg, 'nama' => $s->subcategory_name, 'pakai_stok' => $s->pakai_stok])->values(),
        ]);
    }

    private function validated(Request $request): array
    {
        $cat = new Category;

        $data = $request->validate([
            'nama_produk' => ['nullable', 'string', 'max:100'],
            'deskripsi'   => ['nullable', 'string'],
            'harga'       => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'id_ctg'      => ['required', Rule::exists($cat->getTable(), $cat->getKeyName())],
            'id_subctg'   => ['nullable', 'integer'],
            'stok'        => ['nullable', 'integer', 'min:0'],
            'tersedia'    => ['nullable', 'boolean'],
            'foto_produk' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'harga.required'       => 'Harga wajib diisi.',
            'harga.numeric'        => 'Harga harus berupa angka.',
            'stok.integer'         => 'Stok harus berupa bilangan bulat.',
            'id_ctg.required'      => 'Pilih kategori.',
            'foto_produk.image'    => 'File harus berupa gambar.',
            'foto_produk.max'      => 'Ukuran foto maksimal 2 MB.',
        ]);

        // Subkategori (jenis / ukuran) wajib dipilih bila kategorinya punya pilihan
        $subs = Subcategory::where('id_ctg', $data['id_ctg'])->get();
        $sub  = null;

        if ($subs->isNotEmpty()) {
            $sub = $subs->firstWhere('id_subctg', (int) ($data['id_subctg'] ?? 0));
            if (! $sub) {
                throw ValidationException::withMessages(['id_subctg' => 'Pilih jenis atau ukuran produk.']);
            }
        } else {
            $data['id_subctg'] = null;
        }

        // Nama produk: diisi bebas hanya untuk Buket Fresh Flower (mis. Aster, Mawar).
        // Jenis lain: nama otomatis mengikuti jenis yang dipilih.
        if (! $sub || $sub->pakai_stok) {
            if (blank($data['nama_produk'] ?? null)) {
                throw ValidationException::withMessages(['nama_produk' => 'Nama produk wajib diisi.']);
            }
        } else {
            $data['nama_produk'] = $this->namaOtomatis(Category::find($data['id_ctg']), $sub);
        }

        if ($sub && $sub->pakai_stok) {
            // Buket Fresh Flower: dihitung per stok (tangkai)
            if (! isset($data['stok'])) {
                throw ValidationException::withMessages(['stok' => 'Stok wajib diisi.']);
            }
            $data['tersedia'] = $data['stok'] > 0;
        } else {
            // Selain itu: tidak ada stok, hanya tersedia / tidak
            $data['stok']     = 0;
            $data['tersedia'] = $request->boolean('tersedia');
        }

        unset($data['foto_produk']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** Buket Satin -> "Buket Satin", Box Akrilik -> "Hantaran Box Akrilik", Besar -> "Papan Bunga Besar" */
    private function namaOtomatis(Category $kategori, Subcategory $sub): string
    {
        $k = $kategori->{self::CATEGORY_NAME};
        $j = $sub->subcategory_name;

        return str_starts_with(mb_strtolower($j), mb_strtolower($k)) ? $j : "{$k} {$j}";
    }

    private function countByCategory(string $nama): int
    {
        return Product::whereHas('category', fn ($q) => $q->where(self::CATEGORY_NAME, $nama))->count();
    }
}
