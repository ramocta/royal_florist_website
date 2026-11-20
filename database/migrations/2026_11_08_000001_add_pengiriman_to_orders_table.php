<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUS_BARU = [
        'menunggu_pembayaran', 'menunggu_dikonfirmasi', 'dikonfirmasi', 'diproses',
        'dalam_pengiriman', 'siap_diambil', 'selesai', 'dibatalkan',
    ];

    private const STATUS_LAMA = [
        'menunggu_pembayaran', 'menunggu_dikonfirmasi', 'diproses',
        'siap_diambil', 'selesai', 'dibatalkan',
    ];

    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status_pesanan', self::STATUS_BARU)->default('menunggu_pembayaran')->change();
        });

        Schema::table('order_status_history', function (Blueprint $table) {
            $table->enum('status_pesanan', self::STATUS_BARU)->change();
        });

        if (Schema::hasColumn('orders', 'jenis_pengambilan')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('jenis_pengambilan', ['ambil_di_toko', 'dikirim'])->default('ambil_di_toko')->after('metode_pembayaran');
            $table->string('alamat_tujuan')->nullable()->after('jenis_pengambilan');
            $table->string('nama_kurir', 100)->nullable();
            $table->string('no_hp_kurir', 20)->nullable();
            $table->dateTime('estimasi_tiba')->nullable();
            $table->string('foto_buket')->nullable();
            $table->text('catatan_toko')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['jenis_pengambilan', 'alamat_tujuan', 'nama_kurir', 'no_hp_kurir', 'estimasi_tiba', 'foto_buket', 'catatan_toko']);
        });

        foreach (['orders', 'order_status_history'] as $tabel) {
            DB::table($tabel)->where('status_pesanan', 'dikonfirmasi')->update(['status_pesanan' => 'diproses']);
            DB::table($tabel)->where('status_pesanan', 'dalam_pengiriman')->update(['status_pesanan' => 'siap_diambil']);
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status_pesanan', self::STATUS_LAMA)->default('menunggu_pembayaran')->change();
        });

        Schema::table('order_status_history', function (Blueprint $table) {
            $table->enum('status_pesanan', self::STATUS_LAMA)->change();
        });
    }
};
