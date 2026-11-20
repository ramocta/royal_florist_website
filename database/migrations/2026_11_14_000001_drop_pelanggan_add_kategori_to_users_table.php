<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom kategori di users (hanya bermakna untuk role pelanggan)
        Schema::table('users', function (Blueprint $table) {
            $table->enum('kategori_pelanggan', ['Baru', 'Lama'])->default('Baru')->after('role');
        });

        // 2. Salin kategori lama dari tabel pelanggan ke akun users yang terhubung
        if (Schema::hasTable('pelanggan')) {
            DB::table('pelanggan')
                ->whereNotNull('id_user')
                ->get()
                ->each(function ($p) {
                    DB::table('users')
                        ->where('id_user', $p->id_user)
                        ->update(['kategori_pelanggan' => $p->kategori]);
                });
        }

        // 3. Hapus tabel pelanggan
        Schema::dropIfExists('pelanggan');
    }

    public function down(): void
    {
        // Struktur pelanggan dikembalikan seperti kondisi terakhir sebelum dihapus (datanya tidak ikut kembali)
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->nullable()->unique();
            $table->string('nama', 100);
            $table->string('email', 100)->nullable();
            $table->string('kontak', 30);
            $table->enum('kategori', ['Baru', 'Lama'])->default('Baru');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kategori_pelanggan');
        });
    }
};
