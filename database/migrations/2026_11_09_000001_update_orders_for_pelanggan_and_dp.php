<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pesanan yang dibuat admin di toko tidak punya akun pelanggan, jadi id_user boleh kosong
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user')->nullable()->change();
        });

        // Nominal uang muka (hanya terisi kalau metode pembayarannya DP)
        if (! Schema::hasColumn('orders', 'jumlah_dp')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('jumlah_dp', 12, 2)->nullable()->after('total_harga');
            });
        }

        // Tambah pilihan 'DP' di metode pembayaran
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE orders MODIFY metode_pembayaran ENUM('bayar_di_toko','QRIS','transfer_bank','DP') NOT NULL");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::table('orders')->where('metode_pembayaran', 'DP')->update(['metode_pembayaran' => 'bayar_di_toko']);
            DB::statement("ALTER TABLE orders MODIFY metode_pembayaran ENUM('bayar_di_toko','QRIS','transfer_bank') NOT NULL");
        }

        if (Schema::hasColumn('orders', 'jumlah_dp')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('jumlah_dp');
            });
        }
    }
};