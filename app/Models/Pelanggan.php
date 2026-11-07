<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    public const HARI_BARU = 30;

    protected $table = 'pelanggan';

    protected $fillable = ['id_user', 'nama', 'email', 'kontak', 'kategori'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'id_user', 'id_user');
    }

    /** Salin akun pelanggan baru dari tabel users, lalu perbarui kategori Baru/Lama. */
    public static function sinkronDariUser(): void
    {
        $rows = User::where('role', 'pelanggan')
            ->get(['id_user', 'nama', 'email', 'nmr_telepon'])
            ->map(fn ($u) => [
                'id_user'    => $u->id_user,
                'nama'       => $u->nama,
                'email'      => $u->email,
                'kontak'     => $u->nmr_telepon,
                'kategori'   => 'Baru',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($rows) {
            static::insertOrIgnore($rows);
        }

        static::where('kategori', 'Baru')
            ->where('created_at', '<', now()->subDays(self::HARI_BARU))
            ->update(['kategori' => 'Lama']);
    }
}
