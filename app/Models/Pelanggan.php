<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SEMENTARA: tabel `pelanggan` sudah dihapus (data pelanggan kini di tabel `users`).
 * Class ini dipertahankan hanya agar kode lama yang masih memanggilnya tidak error.
 * Hapus file ini setelah tidak ada lagi yang memakai `Pelanggan`.
 */
class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    public static function sinkronDariUser(): void
    {
        // tidak melakukan apa-apa
    }
}
