<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id('id_banner');
            $table->string('gambar');                      // alamat file di storage/app/public, mis. "banners/AbC123.png"
            $table->unsignedInteger('urutan')->default(0); // makin kecil makin dulu tampil
            $table->boolean('is_active')->default(true);   // dimatikan = tidak tampil di beranda, tapi tidak dihapus
            $table->timestamps();
        });

        // Banner lama (satu gambar hero di tabel settings) dipindahkan jadi banner pertama,
        // supaya gambar yang sudah diunggah admin tidak hilang setelah migrasi.
        $lama = DB::table('settings')->where('setting_key', 'hero_gambar')->value('setting_value');
        if ($lama) {
            DB::table('banners')->insert([
                'gambar'     => $lama,
                'urutan'     => 1,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Isian hero lama di settings sudah tidak dipakai (teks dihapus, gambar pindah ke tabel banners).
        // File gambarnya tidak ikut dihapus, karena sekarang dipakai oleh banner di atas.
        DB::table('settings')->whereIn('setting_key', [
            'hero_judul', 'hero_deskripsi', 'hero_tombol_teks', 'hero_gambar',
        ])->delete();
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
