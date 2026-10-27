<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $table = 'order_status_history';
    protected $primaryKey = 'id_history';
    public $timestamps = false;

    protected $fillable = ['id_order', 'status_pesanan', 'catatan', 'diubah_oleh'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh', 'id_user');
    }
}
