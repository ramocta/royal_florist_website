<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penanda: jenis ini dihitung per stok (tangkai) atau cukup tersedia / tidak
        Schema::table('subcategories', function (Blueprint $table) {
            $table->boolean('pakai_stok')->default(false)->after('subcategory_name');
        });

        // Ketersediaan produk (dipakai untuk jenis tanpa stok)
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('tersedia')->default(true)->after('stok');
        });

        // Hanya Buket Fresh Flower yang memakai stok
        DB::table('subcategories')->where('subcategory_name', 'Buket Fresh Flower')->update(['pakai_stok' => true]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tersedia');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropColumn('pakai_stok');
        });
    }
};
