<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\KategoriService;
use App\Services\ProdukService;
use App\Support\ExcelExport;
use App\Support\FotoProduk;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    private const CATEGORY_NAME = 'category_name';
    private const PER_PAGE = 5;
    private const KATEGORI_BUKET = 'Buket';
    private const KATEGORI_PAPAN = 'Papan Bunga';

    public function __construct(private ProdukService $produk)
    {
    }

    //halaman

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

    //simpan / ubah / hapus

    public function store(Request $request)
    {
        if ($request->has('items')) {
            return $this->simpanBanyak($request);
        }

        $data = $this->produk->siapkan($request->except('foto_produk'), $request->file('foto_produk'));

        if ($request->hasFile('foto_produk')) {
            $data['foto_produk'] = FotoProduk::simpan($request->file('foto_produk'));
        }

        Product::create($data);

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $produk)
    {
        $data = $this->produk->siapkan($request->except('foto_produk'), $request->file('foto_produk'));

        if ($request->hasFile('foto_produk')) {
            FotoProduk::hapus($produk->foto_produk);
            $data['foto_produk'] = FotoProduk::simpan($request->file('foto_produk'));
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

        FotoProduk::hapus($produk->foto_produk);

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }

    //data realtime (JSON) 

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
                'satuan'   => $p->satuan,
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
        $baris = Product::with(['category', 'subcategory'])
            ->orderBy('id_product')
            ->get()
            ->map(fn ($p) => [
                $p->sku,
                $p->nama_produk,
                optional($p->category)->{self::CATEGORY_NAME},
                optional($p->subcategory)->subcategory_name,
                $p->harga,
                $p->pakai_stok ? $p->stok : null,
                $p->pakai_stok ? $p->satuan : null,
                $p->status,
            ]);

        return ExcelExport::download('produk-royal-florist', 'Katalog Produk', [
            ['SKU', 14, 'tengah'],
            ['Nama Produk', 34],
            ['Kategori', 20],
            ['Jenis', 24],
            ['Harga Jual', 18, 'rupiah'],
            ['Stok', 10, 'angka'],
            ['Satuan', 12, 'tengah'],
            ['Status', 14, 'status'],
        ], $baris);
    }

    //helper

    private function categories(): array
    {
        return app(KategoriService::class)->daftar();
    }

    private function simpanBanyak(Request $request)
    {
        $hasil = $this->produk->simpanBanyak($this->produk->siapkanBanyak($request));
        $pesan = count($hasil) . ' produk berhasil ditambahkan.';

        if (! $request->expectsJson()) {
            return redirect()->route('admin.produk.index')->with('success', $pesan);
        }

        session()->flash('success', $pesan);

        return response()->json(['message' => $pesan, 'redirect' => route('admin.produk.index')], 201);
    }

    private function countByCategory(string $nama): int
    {
        return Product::whereHas('category', fn ($q) => $q->where(self::CATEGORY_NAME, $nama))->count();
    }
}
