<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $table = 'banners';
    protected $primaryKey = 'id_banner';

    protected $fillable = ['gambar', 'urutan', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    // Alamat (URL) gambar. Null kalau file-nya tidak ada di storage, supaya beranda tidak menampilkan gambar rusak.
    public function getUrlAttribute(): ?string
    {
        return Storage::disk('public')->exists($this->gambar)
            ? asset('storage/' . $this->gambar)
            : null;
    }

    // Banner yang tampil di beranda: aktif, berurutan
    public function scopeTampil($query)
    {
        return $query->where('is_active', true)->orderBy('urutan')->orderBy('id_banner');
    }
}
