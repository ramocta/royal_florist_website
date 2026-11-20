<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('kategori', 20)->default('Baru')->change();
        });

        DB::table('pelanggan')->whereIn('kategori', ['VIP', 'Reguler'])->update(['kategori' => 'Lama']);

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->enum('kategori', ['Baru', 'Lama'])->default('Baru')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('kategori', 20)->default('Baru')->change();
        });

        DB::table('pelanggan')->where('kategori', 'Lama')->update(['kategori' => 'Reguler']);

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->enum('kategori', ['VIP', 'Reguler', 'Baru'])->default('Baru')->change();
        });
    }
};
