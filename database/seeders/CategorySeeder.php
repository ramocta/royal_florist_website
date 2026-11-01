<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /** Jenis yang dihitung per stok (tangkai). Selain ini hanya tersedia / tidak. */
    private const PAKAI_STOK = ['Buket Fresh Flower'];

    public function run(): void
    {
        $data = [
            'Buket'       => ['Buket Satin', 'Buket Uang', 'Buket Snack', 'Buket Fresh Flower'],
            'Hantaran'    => ['Box Akrilik', 'Box Fiber'],
            'Papan Bunga' => ['Besar', 'Kecil'],
        ];

        foreach ($data as $kategori => $subs) {
            DB::table('categories')->insertOrIgnore(['category_name' => $kategori]);
            $id = DB::table('categories')->where('category_name', $kategori)->value('id_ctg');

            foreach ($subs as $sub) {
                DB::table('subcategories')->insertOrIgnore(['id_ctg' => $id, 'subcategory_name' => $sub]);
                DB::table('subcategories')
                    ->where('id_ctg', $id)->where('subcategory_name', $sub)
                    ->update(['pakai_stok' => in_array($sub, self::PAKAI_STOK, true)]);
            }
        }
    }
}
