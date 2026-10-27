<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $primaryKey = 'id_order';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diubah_pada';

    protected $fillable = [
        'no_invoice', 'id_user', 'metode_pembayaran', 'status_pembayaran',
        'status_pesanan', 'total_harga', 'bukti_pembayaran', 'dibayar_pada',
        'jadwal_pengambilan',
    ];

    protected function casts(): array
    {
        return [
            'dibayar_pada' => 'datetime',
            'jadwal_pengambilan' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_order', 'id_order');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'id_order', 'id_order');
    }
}
