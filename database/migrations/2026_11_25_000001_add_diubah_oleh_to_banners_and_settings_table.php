<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Mencatat admin/karyawan terakhir yang mengubah banner dan pengaturan toko.
 *
 * Kolom `diubah_oleh` dibuat nullable karena:
 *   - banner lama (hasil migrasi) dan nilai bawaan pengaturan tidak dibuat oleh akun tertentu;
 *   - nullOnDelete: kalau akun admin dihapus, banner/pengaturannya tetap ada (kolom jadi NULL).
 * Pola yang sama dipakai di order_status_history.diubah_oleh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (! Schema::hasColumn('banners', 'diubah_oleh')) {
                $table->foreignId('diubah_oleh')
                    ->nullable()
                    ->after('is_active')
                    ->constrained('users', 'id_user')
                    ->nullOnDelete();
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'diubah_oleh')) {
                $table->foreignId('diubah_oleh')
                    ->nullable()
                    ->after('setting_value')
                    ->constrained('users', 'id_user')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'diubah_oleh')) {
                $table->dropConstrainedForeignId('diubah_oleh');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'diubah_oleh')) {
                $table->dropConstrainedForeignId('diubah_oleh');
            }
        });
    }
};
