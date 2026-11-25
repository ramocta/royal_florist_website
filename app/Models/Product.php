<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\FotoProduk;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id_product';

    // Tabel products tidak punya created_at / updated_at
    public $timestamps = false;

    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'harga',
        'foto_produk',
        'stok',
        'tersedia',
        'id_ctg',
        'id_subctg',
        'is_active',
    ];

    protected $casts = [
        'harga'     => 'decimal:2',
        'stok'      => 'integer',
        'tersedia'  => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_ctg');
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'id_subctg');
    }

    /** SKU dibuat dari id: #PRD-001 */
    public function getSkuAttribute(): string
    {
        return '#PRD-' . str_pad($this->id_product, 3, '0', STR_PAD_LEFT);
    }

    /** true bila produk dihitung per stok: ikut jenis, atau ikut kategori bila tanpa jenis */
    public function getPakaiStokAttribute(): bool
    {
        $sumber = $this->subcategory ?? $this->category;

        return (bool) optional($sumber)->pakai_stok;
    }

    public function getSatuanAttribute(): string
    {
        return $this->id_subctg ? 'tangkai' : 'pcs';
    }

    /** Tersedia | Habis | Nonaktif */
    public function getStatusAttribute(): string
    {
        if (! $this->is_active) return 'Nonaktif';

        $ada = $this->pakai_stok ? $this->stok > 0 : $this->tersedia;

        return $ada ? 'Tersedia' : 'Habis';
    }

    public function getFotoUrlAttribute(): ?string
    {
        return FotoProduk::url($this->foto_produk);
    }
}
