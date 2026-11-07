<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('setting_key', 100)->primary(); // nama isian, contoh: toko_nama
            $table->text('setting_value')->nullable();     // isinya (teks, '1'/'0', atau alamat file gambar)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
