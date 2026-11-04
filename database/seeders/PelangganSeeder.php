<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Siti Rahmawati', '0812-3456-7890', 'Lama', 24, 8750000],
            ['Budi Santoso',   '0857-1122-3344', 'Baru',  8, 2400000],
            ['Amanda Putri',   '0819-8877-6655', 'Lama', 18, 6120000],
            ['Dewi Lestari',   '0813-9900-1122', 'Baru',  1,  350000],
            ['Rian Hidayat',   '0878-5544-3322', 'Baru',  5, 1850000],
        ];

        foreach ($data as [$nama, $kontak, $kategori, $pesanan, $belanja]) {
            Pelanggan::create([
                'nama' => $nama, 'kontak' => $kontak, 'kategori' => $kategori,
                'total_pesanan' => $pesanan, 'total_belanja' => $belanja,
            ]);
        }
    }
}
