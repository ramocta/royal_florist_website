<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bunga_custom')) {
            return;
        }

        Schema::create('bunga_custom', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id('id_bunga');
            $table->string('nama');
            $table->integer('harga')->default(0);
            $table->string('satuan', 50)->default('tangkai');
            $table->integer('stok')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunga_custom');
    }
};