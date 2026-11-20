<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['total_pesanan', 'total_belanja']);
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->unsignedInteger('total_pesanan')->default(0);
            $table->unsignedBigInteger('total_belanja')->default(0);
        });
    }
};
