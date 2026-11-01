<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $kategori = ['Buket', 'Hantaran', 'Papan Bunga'];

    public function up(): void
    {
        // 1. Bersihkan kategori lama (varchar) yang tidak masuk daftar enum & belum dipakai produk
        DB::table('categories')
            ->whereNotIn('category_name', $this->kategori)
            ->whereNotIn('id_ctg', DB::table('products')->select('id_ctg'))
            ->delete();

        // 2. categories.category_name: varchar -> enum
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('category_name', $this->kategori)->change();
        });

        // 3. Subkategori: jenis (Buket/Hantaran) atau ukuran (Papan Bunga)
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id('id_subctg');
            $table->unsignedBigInteger('id_ctg');
            $table->string('subcategory_name', 50);
            $table->unique(['id_ctg', 'subcategory_name']);
            $table->foreign('id_ctg')->references('id_ctg')->on('categories')->cascadeOnDelete();
        });

        // 4. Produk menyimpan subkategori yang dipilih
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('id_subctg')->nullable()->after('id_ctg');
            $table->foreign('id_subctg')->references('id_subctg')->on('subcategories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['id_subctg']);
            $table->dropColumn('id_subctg');
        });

        Schema::dropIfExists('subcategories');

        Schema::table('categories', function (Blueprint $table) {
            $table->string('category_name', 50)->change();
        });
    }
};
