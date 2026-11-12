<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'setting_key';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['setting_key', 'setting_value'];

    /**
     * Nilai bawaan. Dipakai kalau admin belum pernah menyimpan isian itu,
     * jadi website tetap tampil utuh walau tabel settings masih kosong.
     * Satu-satunya tempat nilai bawaan ditulis, supaya tidak ada dua versi yang berbeda.
     */
    public const DEFAULTS = [
        // Profil Toko
        'toko_nama'            => 'Royal Florist',
        'toko_email'           => 'info@royalflorist.com',
        'toko_whatsapp'        => '+62 857-3589-6196',
        'toko_alamat'          => 'Jegles no 04, Jegles, Begadung, Kec. Nganjuk, Kabupaten Nganjuk, Jawa Timur 64114',
        'toko_jam_senin_sabtu' => 'Senin - Sabtu (08:00 - 20:00)',
        'toko_jam_minggu'      => 'Minggu (09:00 - 17:00)',
        'toko_buka'            => '1',

        // Landing page: banner (hero) dan footer
        'hero_judul'           => 'Abadikan Momen Berharga Dengan Sentuhan Rangkaian Bunga Elegan',
        'hero_deskripsi'       => 'Pilihan terbaik untuk Buket Bunga Segar, Papan Ucapan Acara, dan Seserahan Hantaran.',
        'hero_tombol_teks'     => 'Pesan Sekarang',
        'footer_deskripsi'     => 'Toko bunga dan florist profesional untuk berbagai momen spesial. Menyediakan rangkaian bunga segar berkualitas.',
    ];

    // Seluruh isi tabel dibaca sekali per request, lalu disimpan di sini supaya tidak query berulang
    private static ?array $cache = null;

    private static function semua(): array
    {
        return self::$cache ??= self::query()->pluck('setting_value', 'setting_key')->all();
    }

    // Ambil satu isian: dari database, kalau belum ada pakai $cadangan, kalau tidak ada pakai DEFAULTS
    public static function ambil(string $kunci, ?string $cadangan = null): ?string
    {
        return self::semua()[$kunci] ?? $cadangan ?? (self::DEFAULTS[$kunci] ?? null);
    }

    /**
     * Rekening tujuan Transfer Bank (diisi admin di Pengaturan > Profil Toko).
     * Mengembalikan null kalau nama bank atau nomor rekening belum diisi; saat itu
     * Transfer Bank tidak ditawarkan di checkout, jadi pelanggan tidak buntu di halaman pembayaran.
     */
    public static function rekeningBank(): ?array
    {
        $nama = trim((string) self::ambil('bank_nama'));
        $no   = trim((string) self::ambil('bank_rekening'));

        if ($nama === '' || $no === '') {
            return null;
        }

        return [
            'nama'      => $nama,
            'rekening'  => $no,
            'atas_nama' => trim((string) self::ambil('bank_atas_nama')),
        ];
    }

    /**
     * Alamat (URL) gambar yang diunggah admin, misalnya banner.
     * Kalau belum pernah diunggah, ATAU file-nya tidak ada di komputer ini
     * (folder storage tidak ikut Git), dipakai gambar bawaan di folder public/.
     * Jadi halaman tidak pernah menampilkan gambar rusak.
     */
    public static function gambar(string $kunci, string $bawaan): string
    {
        $berkas = self::ambil($kunci);

        if ($berkas && Storage::disk('public')->exists($berkas)) {
            return asset('storage/' . $berkas);
        }

        return asset($bawaan);
    }

    // Simpan banyak isian sekaligus. Baris ada -> diubah, belum ada -> dibuat.
    // DB::transaction: kalau satu gagal, semuanya dibatalkan, jadi tidak ada data setengah tersimpan.
    public static function simpan(array $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($data as $kunci => $nilai) {
                self::updateOrCreate(['setting_key' => $kunci], ['setting_value' => $nilai]);
            }
        });

        self::$cache = null; // paksa baca ulang supaya nilai terbaru yang dipakai
    }
}