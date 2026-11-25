<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\KategoriService;
use App\Services\ProdukService;
use App\Services\StockManagementService;
use App\Support\FotoProduk;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdukApiController extends Controller
{
    public function __construct(
        private StockManagementService $layananStok,
        private ProdukService $produk,
    ) {
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
        if ($request->has('items')) {
            return $this->simpanBanyak($request);
        }

        $data = $this->produk->siapkan($request->except('foto_produk'), $request->file('foto_produk'));

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
        $data = $this->produk->siapkan($request->except('foto_produk'), $request->file('foto_produk'));

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
            'satuan'     => $p->satuan,
            'tersedia'   => $p->tersedia,
            'is_active'  => $p->is_active,
            'status'     => $p->status,
            'foto'       => $p->foto_produk,
        ];
    }

    private function kategori(): array
    {
        return app(KategoriService::class)->daftar();
    }

    private function simpanBanyak(Request $request): JsonResponse
    {
        $hasil = $this->produk->simpanBanyak($this->produk->siapkanBanyak($request, awali: true));

        return response()->json(['message' => count($hasil) . ' produk berhasil ditambahkan.'], 201);
    }
}
