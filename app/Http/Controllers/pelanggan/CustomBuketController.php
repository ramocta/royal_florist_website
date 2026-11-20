<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomBuketController extends Controller
{
    private const ASSEMBLY_FEE = 20000;

    /** Jumlah awal tiap bunga saat halaman dibuka (sesuai desain). */
    private const DEFAULT_QTY = ['mawar' => 3, 'lily' => 2, 'tulip' => 0, 'baby' => 1];

    private function wraps(): array
    {
        return [
            'burgundy' => ['name' => 'Soft Pink & Burgundy', 'outer' => '#8b2547', 'inner' => '#f4a8c0'],
            'pink'     => ['name' => 'Soft Pink',            'outer' => '#f4a8c0', 'inner' => '#fbeaf0'],
            'white'    => ['name' => 'Putih Bersih',         'outer' => '#ffffff', 'inner' => '#f3e6ec'],
            'black'    => ['name' => 'Hitam Elegan',         'outer' => '#2a1220', 'inner' => '#6b3a52'],
            'gold'     => ['name' => 'Gold Mewah',           'outer' => '#d4af37', 'inner' => '#f5e6a8'],
        ];
    }

    private function ribbons(): array
    {
        return [
            'satin' => ['name' => 'Satin Putih',  'price' => 0,     'color' => '#ffffff'],
            'gold'  => ['name' => 'Gold Elegant', 'price' => 10000, 'color' => '#d4af37'],
        ];
    }

    public function index()
    {
        $flowers = DB::table('bunga_custom')
            ->where('is_active', 1)
            ->where('stok', '>', 0)
            ->orderBy('id_bunga')
            ->get();

        return view('pelanggan.custom-buket', [
            'flowers'     => $flowers,
            'defaults'    => self::DEFAULT_QTY,
            'wraps'       => $this->wraps(),
            'ribbons'     => $this->ribbons(),
            'assemblyFee' => self::ASSEMBLY_FEE,
        ]);
    }

    public function store(Request $request)
    {
        $wraps   = $this->wraps();
        $ribbons = $this->ribbons();

        $data = $request->validate([
            'flowers'      => ['required', 'array'],
            'flowers.*'    => ['integer', 'min:0', 'max:100'],
            'wrap'         => ['required', Rule::in(array_keys($wraps))],
            'ribbon'       => ['required', Rule::in(array_keys($ribbons))],
            'card_message' => ['nullable', 'string', 'max:200'],
            'card_from'    => ['nullable', 'string', 'max:60'],
        ]);

        // Hanya ambil bunga yang jumlahnya > 0
        $pilihan = collect($data['flowers'])->filter(fn ($qty) => (int) $qty > 0);

        if ($pilihan->isEmpty()) {
            throw ValidationException::withMessages(['flowers' => 'Pilih minimal 1 bunga.']);
        }

        DB::transaction(function () use ($pilihan, $data, $wraps, $ribbons) {
            // Kunci baris bunga supaya stok yang dicek tidak berubah di tengah jalan
            $bunga = DB::table('bunga_custom')
                ->whereIn('id_bunga', $pilihan->keys()->all())
                ->where('is_active', 1)
                ->lockForUpdate()
                ->get()
                ->keyBy(fn ($b) => (int) $b->id_bunga);

            $subtotal = 0;
            $rows = [];

            foreach ($pilihan as $id => $qty) {
                $b = $bunga->get((int) $id);

                if (!$b) {
                    throw ValidationException::withMessages(['flowers' => 'Ada bunga yang tidak tersedia lagi.']);
                }
                if ((int) $qty > (int) $b->stok) {
                    throw ValidationException::withMessages([
                        'flowers' => "Stok {$b->nama} tinggal {$b->stok}.",
                    ]);
                }

                // Harga SELALU dihitung dari database, bukan dari browser
                $subtotal += (float) $b->harga * (int) $qty;
                $rows[] = [
                    'id_bunga'     => (int) $b->id_bunga,
                    'jumlah'       => (int) $qty,
                    'harga_satuan' => $b->harga,
                ];
            }

            $total = $subtotal + $ribbons[$data['ribbon']]['price'] + self::ASSEMBLY_FEE;

            $idCustom = DB::table('custom_buket')->insertGetId([
                'id_user'      => auth()->id(),
                'wrapping'     => $wraps[$data['wrap']]['name'],
                'pita'         => $ribbons[$data['ribbon']]['name'],
                'kartu_ucapan' => $data['card_message'] ?? null,
                'kartu_dari'   => $data['card_from'] ?? null,
                'biaya_rakit'  => self::ASSEMBLY_FEE,
                'total_harga'  => $total,
                'status'       => 'keranjang',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            foreach ($rows as &$row) {
                $row['id_custom'] = $idCustom;
            }
            unset($row);

            DB::table('custom_buket_items')->insert($rows);
        });

        return redirect()->route('custom-buket.index')
            ->with('success', 'Buket custom berhasil ditambahkan ke keranjang!');
    }
}