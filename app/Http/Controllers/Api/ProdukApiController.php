<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\StockManagementService;
use App\Support\FotoProduk;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProdukApiController extends Controller
{
    public function __construct(private StockManagementService $layananStok)
    {
    }

    public function index(): JsonResponse
    {
        $items = Product::with(['category', 'subcategory'])
            ->orderByDesc('id_product')
            ->get()
            ->map(fn (Product $p) => $this->format($p))
            ->values();

        return response()->json(['items' => $items, 'kategori' => $this->kategori()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('foto_produk')) {
            $data['foto_produk'] = FotoProduk::simpan($request->file('foto_produk'));
        }

        $produk = Product::create($data);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'item'    => $this->format($produk->load(['category', 'subcategory'])),
        ], 201);
    }

    public function update(Request $request, Product $produk): JsonResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('foto_produk')) {
            FotoProduk::hapus($produk->foto_produk);
            $data['foto_produk'] = FotoProduk::simpan($request->file('foto_produk'));
        }

        $produk->update($data);

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'item'    => $this->format($produk->fresh(['category', 'subcategory'])),
        ]);
    }

    public function stok(Request $request, Product $produk): JsonResponse
    {
        abort_unless($produk->pakai_stok, 422, 'Produk ini memakai keterangan tersedia / tidak, bukan stok.');

        $data = $request->validate(['stok' => ['required', 'integer', 'min:0']], [
            'stok.required' => 'Stok wajib diisi.',
            'stok.integer'  => 'Stok harus berupa bilangan bulat.',
            'stok.min'      => 'Stok tidak boleh kurang dari 0.',
        ]);

        $this->layananStok->updateStock($produk, (int) $data['stok']);
        $produk->refresh()->update(['tersedia' => $data['stok'] > 0]);

        return response()->json([
            'message' => 'Stok berhasil diperbarui.',
            'item'    => $this->format($produk->fresh(['category', 'subcategory'])),
        ]);
    }

    public function ketersediaan(Request $request, Product $produk): JsonResponse
    {
        abort_if($produk->pakai_stok, 422, 'Produk ini memakai stok, bukan keterangan tersedia / tidak.');

        $request->validate(['tersedia' => ['required', 'boolean']], [
            'tersedia.required' => 'Pilih tersedia atau tidak tersedia.',
        ]);

        $produk->update(['tersedia' => $request->boolean('tersedia')]);

        return response()->json([
            'message' => 'Ketersediaan berhasil diperbarui.',
            'item'    => $this->format($produk->fresh(['category', 'subcategory'])),
        ]);
    }

    public function destroy(Product $produk): JsonResponse
    {
        try {
            $produk->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'Produk sudah dipakai di pesanan dan tidak bisa dihapus. Nonaktifkan saja produknya.',
            ], 409);
        }

        FotoProduk::hapus($produk->foto_produk);

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }

    public function foto(Product $produk)
    {
        $path = FotoProduk::path($produk->foto_produk);
        abort_unless($path && is_file($path), 404);

        return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
    }

    private function format(Product $p): array
    {
        return [
            'id'         => $p->id_product,
            'sku'        => $p->sku,
            'nama'       => $p->nama_produk,
            'deskripsi'  => $p->deskripsi,
            'harga'      => (int) $p->harga,
            'id_ctg'     => $p->id_ctg,
            'kategori'   => optional($p->category)->category_name,
            'id_subctg'  => $p->id_subctg,
            'sub'        => optional($p->subcategory)->subcategory_name,
            'pakai_stok' => $p->pakai_stok,
            'stok'       => $p->stok,
            'tersedia'   => $p->tersedia,
            'is_active'  => $p->is_active,
            'status'     => $p->status,
            'foto'       => $p->foto_produk,
        ];
    }

    private function kategori(): array
    {
        return Category::with('subcategories')->orderBy('id_ctg')->get()->map(fn (Category $c) => [
            'id'        => $c->id_ctg,
            'nama'      => $c->category_name,
            'sub_label' => $c->category_name === 'Papan Bunga' ? 'Ukuran' : 'Jenis',
            'subs'      => $c->subcategories->map(fn (Subcategory $s) => [
                'id'         => $s->id_subctg,
                'nama'       => $s->subcategory_name,
                'pakai_stok' => $s->pakai_stok,
            ])->values(),
        ])->values()->all();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama_produk' => ['nullable', 'string', 'max:100'],
            'deskripsi'   => ['nullable', 'string'],
            'harga'       => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'id_ctg'      => ['required', Rule::exists('categories', 'id_ctg')],
            'id_subctg'   => ['nullable', 'integer'],
            'stok'        => ['nullable', 'integer', 'min:0'],
            'tersedia'    => ['nullable', 'boolean'],
            'foto_produk' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'harga.required'    => 'Harga wajib diisi.',
            'harga.numeric'     => 'Harga harus berupa angka.',
            'stok.integer'      => 'Stok harus berupa bilangan bulat.',
            'id_ctg.required'   => 'Pilih kategori.',
            'foto_produk.image' => 'File harus berupa gambar.',
            'foto_produk.max'   => 'Ukuran foto maksimal 2 MB.',
        ]);

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

        if (! $sub || $sub->pakai_stok) {
            if (blank($data['nama_produk'] ?? null)) {
                throw ValidationException::withMessages(['nama_produk' => 'Nama produk wajib diisi.']);
            }
        } else {
            $data['nama_produk'] = $this->namaOtomatis(Category::find($data['id_ctg']), $sub);
        }

        if ($sub && $sub->pakai_stok) {
            if (! isset($data['stok'])) {
                throw ValidationException::withMessages(['stok' => 'Stok wajib diisi.']);
            }
            $data['tersedia'] = $data['stok'] > 0;
        } else {
            $data['stok']     = 0;
            $data['tersedia'] = $request->boolean('tersedia');
        }

        unset($data['foto_produk']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function namaOtomatis(Category $kategori, Subcategory $sub): string
    {
        $k = $kategori->category_name;
        $j = $sub->subcategory_name;

        return str_starts_with(mb_strtolower($j), mb_strtolower($k)) ? $j : "{$k} {$j}";
    }
}
