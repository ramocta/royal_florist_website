<?php

use App\Support\FotoProduk;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        File::ensureDirectoryExists(public_path(FotoProduk::FOLDER));

        foreach (DB::table('products')->whereNotNull('foto_produk')->get() as $produk) {
            $nama   = basename($produk->foto_produk);
            $tujuan = public_path(FotoProduk::FOLDER . '/' . $nama);
            $lama   = storage_path('app/public/' . $produk->foto_produk);

            if (! is_file($tujuan) && is_file($lama)) {
                File::copy($lama, $tujuan);
            }

            DB::table('products')
                ->where('id_product', $produk->id_product)
                ->update(['foto_produk' => is_file($tujuan) ? $nama : null]);
        }
    }

    public function down(): void
    {
    }
};
