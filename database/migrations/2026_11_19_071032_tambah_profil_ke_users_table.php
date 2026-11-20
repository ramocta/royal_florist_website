<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'jenis_kelamin')) {
                $table->string('jenis_kelamin', 20)->nullable();
            }
            if (! Schema::hasColumn('users', 'umur')) {
                $table->unsignedTinyInteger('umur')->nullable();
            }
            if (! Schema::hasColumn('users', 'foto')) {
                $table->string('foto')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'umur', 'foto']);
        });
    }
};