
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alamat_pengiriman', function (Blueprint $table) {
            $table->id('id_alamat');

            $table->unsignedBigInteger('id_user');

            $table->string('nama_penerima', 100);
            $table->string('nomor_hp', 20);
            $table->text('alamat_lengkap');
            $table->string('kota', 100);
            $table->text('catatan')->nullable();

            $table->boolean('is_utama')->default(false);

            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alamat_pengiriman');
    }
};
