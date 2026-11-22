<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('category_name', 50)->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('pakai_stok')->default(false)->after('category_name');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('pakai_stok');
        });
    }
};
