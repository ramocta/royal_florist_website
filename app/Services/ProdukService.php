<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Support\FotoProduk;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProdukService
{
    public const MAKS_BANYAK = 20;

    public function siapkan(array $input, ?UploadedFile $foto = null): array
    {
        $data = Validator::make($input + ['foto_produk' => $foto], $this->aturan(), $this->pesan())->validate();

        $kategori = Category::findOrFail($data['id_ctg']);
        $sub      = $this->cariSub($kategori, $data);

        $data['id_subctg'] = $sub?->id_subctg;
        $pakaiStok         = $sub ? $sub->pakai_stok : $kategori->pakai_stok;

        if (! $sub || $sub->pakai_stok) {
            if (blank($data['nama_produk'] ?? null)) {
                throw ValidationException::withMessages(['nama_produk' => 'Nama produk wajib diisi.']);
            }
        } else {
            $data['nama_produk'] = $this->namaOtomatis($kategori, $sub);
        }

        if ($pakaiStok) {
            if (! isset($data['stok'])) {
                throw ValidationException::withMessages(['stok' => 'Stok wajib diisi.']);
            }
            $data['tersedia'] = $data['stok'] > 0;
        } else {
            $data['stok']     = 0;
            $data['tersedia'] = $this->bool($input['tersedia'] ?? false);
        }

        unset($data['foto_produk']);
        $data['is_active'] = $this->bool($input['is_active'] ?? false);

        return $data;
    }

    /** Validasi semua baris dulu; bila ada yang salah, tidak ada satu pun yang disimpan. */
    public function siapkanBanyak(Request $request, bool $awali = false): array
    {
        $request->validate(
            ['items' => ['required', 'array', 'min:1', 'max:' . self::MAKS_BANYAK]],
            [
                'items.required' => 'Tambahkan minimal satu produk.',
                'items.max'      => 'Maksimal ' . self::MAKS_BANYAK . ' produk sekali simpan.',
            ]
        );

        $siap  = [];
        $galat = [];

        foreach (array_keys($request->input('items')) as $i) {
            $foto = $request->file("items.$i.foto_produk");

            try {
                $siap[] = [$this->siapkan((array) $request->input("items.$i"), $foto), $foto];
            } catch (ValidationException $e) {
                foreach ($e->errors() as $kolom => $pesan) {
                    $galat["items.$i.$kolom"] = $awali
                        ? array_map(fn ($m) => 'Produk ' . (((int) $i) + 1) . ': ' . $m, $pesan)
                        : $pesan;
                }
            }
        }

        if ($galat) {
            throw ValidationException::withMessages($galat);
        }

        return $siap;
    }

    public function simpanBanyak(array $siap): array
    {
        $tersimpan = [];

        try {
            return DB::transaction(function () use ($siap, &$tersimpan) {
                $hasil = [];

                foreach ($siap as [$data, $foto]) {
                    if ($foto) {
                        $data['foto_produk'] = $tersimpan[] = FotoProduk::simpan($foto);
                    }
                    $hasil[] = Product::create($data);
                }

                return $hasil;
            });
        } catch (\Throwable $e) {
            array_map([FotoProduk::class, 'hapus'], $tersimpan);
            throw $e;
        }
    }

    private function cariSub(Category $kategori, array $data): ?Subcategory
    {
        $subs = Subcategory::where('id_ctg', $kategori->id_ctg)->get();

        if ($subs->isEmpty()) {
            return null;
        }

        $sub = $subs->firstWhere('id_subctg', (int) ($data['id_subctg'] ?? 0));

        if (! $sub) {
            throw ValidationException::withMessages(['id_subctg' => 'Pilih jenis atau ukuran produk.']);
        }

        return $sub;
    }

    /** Buket Satin -> "Buket Satin", Box Akrilik -> "Hantaran Box Akrilik", Besar -> "Papan Bunga Besar" */
    private function namaOtomatis(Category $kategori, Subcategory $sub): string
    {
        $k = $kategori->category_name;
        $j = $sub->subcategory_name;

        return str_starts_with(mb_strtolower($j), mb_strtolower($k)) ? $j : "{$k} {$j}";
    }

    private function bool(mixed $nilai): bool
    {
        return filter_var($nilai, FILTER_VALIDATE_BOOLEAN);
    }

    private function aturan(): array
    {
        return [
            'nama_produk' => ['nullable', 'string', 'max:100'],
            'deskripsi'   => ['nullable', 'string'],
            'harga'       => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'id_ctg'      => ['required', Rule::exists('categories', 'id_ctg')],
            'id_subctg'   => ['nullable', 'integer'],
            'stok'        => ['nullable', 'integer', 'min:0'],
            'tersedia'    => ['nullable', 'boolean'],
            'foto_produk' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function pesan(): array
    {
        return [
            'harga.required'    => 'Harga wajib diisi.',
            'harga.numeric'     => 'Harga harus berupa angka.',
            'stok.integer'      => 'Stok harus berupa bilangan bulat.',
            'id_ctg.required'   => 'Pilih kategori.',
            'id_ctg.exists'     => 'Kategori tidak ditemukan.',
            'foto_produk.image' => 'File harus berupa gambar.',
            'foto_produk.mimes' => 'Foto harus berformat jpg, png, atau webp.',
            'foto_produk.max'   => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
