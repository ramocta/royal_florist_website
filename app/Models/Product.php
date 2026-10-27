<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $primaryKey = 'id_product';
    public $timestamps = false;

    protected $fillable = [
        'nama_produk', 'deskripsi', 'harga', 'foto_produk', 'stok', 'id_ctg', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_ctg', 'id_ctg');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_product', 'id_product');
    }
}
