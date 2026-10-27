<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id('id_history');
            $table->foreignId('id_order')->constrained('orders', 'id_order');
            $table->enum('status_pesanan', [
                'menunggu_pembayaran',
                'menunggu_dikonfirmasi',
                'diproses',
                'siap_diambil',
                'selesai',
                'dibatalkan',
            ]);
            $table->text('catatan')->nullable();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users', 'id_user');
            $table->dateTime('diubah_pada')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
