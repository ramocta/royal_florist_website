<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class KategoriService
{
    public const BAWAAN = ['Buket', 'Hantaran', 'Papan Bunga'];
    public const MAKS_JENIS = 20;

    public function daftar(): array
    {
        return Category::with('subcategories')->withCount('products')->orderBy('id_ctg')->get()
            ->map(fn (Category $c) => [
                'id'            => $c->id_ctg,
                'nama'          => $c->category_name,
                'sub_label'     => $c->category_name === 'Papan Bunga' ? 'Ukuran' : 'Jenis',
                'pakai_stok'    => $c->pakai_stok,
                'bawaan'        => $this->bawaan($c),
                'jumlah_produk' => $c->products_count,
                'subs'          => $c->subcategories->map(fn (Subcategory $s) => [
                    'id'         => $s->id_subctg,
                    'nama'       => $s->subcategory_name,
                    'pakai_stok' => $s->pakai_stok,
                ])->values(),
            ])->values()->all();
    }

    public function tambah(Request $request): Category
    {
        $this->bersihkan($request);
        $request->merge(['jenis' => $this->rapikanJenis($request->input('jenis'))]);

        $data = $request->validate([
            'nama'       => $this->aturanNama(),
            'pakai_stok' => ['required', 'boolean'],
            'jenis'      => ['array', 'max:' . self::MAKS_JENIS],
            'jenis.*'    => ['string', 'max:50'],
        ], $this->pesan());

        return DB::transaction(function () use ($data, $request) {
            $pakaiStok = $request->boolean('pakai_stok');

            $kategori = Category::create([
                'category_name' => $data['nama'],
                'pakai_stok'    => $pakaiStok,
            ]);

            $this->simpanJenis($kategori, $data['jenis'] ?? [], $pakaiStok);

            return $kategori;
        });
    }

    public function ubahNama(Request $request, Category $kategori): Category
    {
        abort_if($this->bawaan($kategori), 422, 'Kategori bawaan tidak bisa diubah.');
        $this->bersihkan($request);

        $data = $request->validate(['nama' => $this->aturanNama($kategori)], $this->pesan());
        $kategori->update(['category_name' => $data['nama']]);

        return $kategori;
    }

    public function hapus(Category $kategori): void
    {
        abort_if($this->bawaan($kategori), 422, 'Kategori bawaan tidak bisa dihapus.');
        abort_if($kategori->products()->exists(), 422, 'Kategori masih punya produk. Hapus atau pindahkan produknya dulu.');

        $kategori->delete();
    }

    /** Hanya kategori bawaan toko yang terkunci; kategori buatan karyawan boleh punya jenis. */
    public function bawaan(Category $kategori): bool
    {
        return in_array($kategori->category_name, self::BAWAAN, true);
    }

    private function aturanNama(?Category $kategori = null): array
    {
        return [
            'required',
            'string',
            'max:50',
            Rule::unique('categories', 'category_name')->ignore($kategori?->id_ctg, 'id_ctg'),
        ];
    }

    private function bersihkan(Request $request): void
    {
        $nama = preg_replace('/\s+/', ' ', trim((string) $request->input('nama')));
        $request->merge(['nama' => $nama]);
    }

    /** Buang jenis kosong dan duplikat (tanpa membedakan huruf besar/kecil, sama seperti collation DB). */
    private function rapikanJenis(mixed $jenis): array
    {
        return collect(is_array($jenis) ? $jenis : [])
            ->map(fn ($j) => is_scalar($j) ? preg_replace('/\s+/', ' ', trim((string) $j)) : '')
            ->filter(fn ($j) => $j !== '')
            ->unique(fn ($j) => mb_strtolower($j))
            ->values()
            ->all();
    }

    private function simpanJenis(Category $kategori, array $jenis, bool $pakaiStok): void
    {
        // Kolom pakai_stok di subcategories belum tentu ada di semua instalasi.
        $adaKolomStok = Schema::hasColumn('subcategories', 'pakai_stok');

        foreach ($jenis as $nama) {
            $atribut = ['subcategory_name' => $nama];

            if ($adaKolomStok) {
                $atribut['pakai_stok'] = $pakaiStok;
            }

            $kategori->subcategories()->forceCreate($atribut);
        }
    }

    private function pesan(): array
    {
        return [
            'nama.required'       => 'Nama kategori wajib diisi.',
            'nama.max'            => 'Nama kategori maksimal :max karakter.',
            'nama.unique'         => 'Nama kategori sudah dipakai.',
            'pakai_stok.required' => 'Pilih cara menghitung produk: stok atau keterangan ketersediaan.',
            'pakai_stok.boolean'  => 'Pilihan cara menghitung produk tidak valid.',
            'jenis.max'           => 'Jenis maksimal :max per kategori.',
            'jenis.*.max'         => 'Nama jenis maksimal :max karakter.',
        ];
    }
}
