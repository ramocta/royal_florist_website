<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kontak', 30);
            $table->enum('kategori', ['VIP', 'Reguler', 'Baru'])->default('Baru');
            $table->unsignedInteger('total_pesanan')->default(0);
            $table->unsignedBigInteger('total_belanja')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
