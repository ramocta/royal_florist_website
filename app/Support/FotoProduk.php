<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FotoProduk
{
    public const FOLDER = 'assets/produk';

    public static function simpan(UploadedFile $file): string
    {
        $nama = Str::uuid() . '.' . $file->extension();
        $file->move(public_path(self::FOLDER), $nama);

        return $nama;
    }

    public static function hapus(?string $nama): void
    {
        $path = self::path($nama);

        if ($path && is_file($path)) {
            unlink($path);
        }
    }

    public static function path(?string $nama): ?string
    {
        return $nama ? public_path(self::FOLDER . '/' . basename($nama)) : null;
    }

    public static function url(?string $nama): ?string
    {
        $path = self::path($nama);

        return $path && is_file($path) ? asset(self::FOLDER . '/' . basename($nama)) : null;
    }
}
