<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('id_order');
            $table->string('no_invoice', 20)->unique();
            $table->foreignId('id_user')->constrained('users', 'id_user');
            $table->enum('metode_pembayaran', ['bayar_di_toko', 'QRIS', 'transfer_bank']);
            $table->enum('status_pembayaran', ['belum_dibayar', 'proses', 'lunas'])->default('belum_dibayar');
            $table->enum('status_pesanan', [
                'menunggu_pembayaran',
                'menunggu_dikonfirmasi',
                'diproses',
                'siap_diambil',
                'selesai',
                'dibatalkan',
            ])->default('menunggu_pembayaran');
            $table->decimal('total_harga', 12, 2);
            $table->string('bukti_pembayaran')->nullable();
            $table->dateTime('dibayar_pada')->nullable();
            $table->dateTime('jadwal_pengambilan')->nullable();
            $table->dateTime('dibuat_pada')->useCurrent();
            $table->dateTime('diubah_pada')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
