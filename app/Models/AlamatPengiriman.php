<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlamatPengiriman extends Model
{
    protected $table = 'alamat_pengiriman';

    protected $primaryKey = 'id_alamat';

    protected $fillable = [
        'id_user',
        'nama_penerima',
        'nomor_hp',
        'alamat_lengkap',
        'kota',
        'catatan',
        'is_utama',
    ];

    protected function casts(): array
    {
        return [
            'is_utama' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}