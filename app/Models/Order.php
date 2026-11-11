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
        'no_invoice',
        'id_user',
        'metode_pembayaran',
        'jenis_pengambilan',
        'alamat_tujuan',
        'status_pembayaran',
        'status_pesanan',
        'total_harga',
        'jumlah_dp',
        'bukti_pembayaran',
        'dibayar_pada',
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

    // Data pelanggan di menu Pelanggan (nyambung lewat id_user, sama seperti Pelanggan::orders())
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_user', 'id_user');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_order', 'id_order');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'id_order', 'id_order');
    }

    // Data pelanggan untuk tabel daftar pesanan. Pesanan yang dibuat admin di toko tidak punya id_user, tampil "-".
    public function getNamaPelangganAttribute(): string
    {
        return $this->pelanggan->nama ?? $this->user->nama ?? '-';
    }

    public function getEmailPelangganAttribute(): string
    {
        return $this->pelanggan->email ?? $this->user->email ?? '-';
    }

    public function getKontakPelangganAttribute(): string
    {
        return $this->pelanggan->kontak ?? $this->user->nmr_telepon ?? '-';
    }

    public function getKategoriPelangganAttribute(): ?string
    {
        return $this->pelanggan->kategori ?? null;
    }
}