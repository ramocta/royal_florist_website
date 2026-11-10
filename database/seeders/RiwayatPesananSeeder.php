<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiwayatPesananSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('nama', 'like', 'Davina%')->first()
            ?? User::where('role', 'pelanggan')->first();
        $produk = Product::take(3)->get();

        if (! $user || $produk->isEmpty()) {
            $this->command->warn('Butuh minimal 1 user pelanggan dan 1 produk.');
            return;
        }

        $contoh = [
            ['RF-DEMO-001', 'QRIS',          'lunas',  'siap_diambil',          now()->subDays(1), 2],
            ['RF-DEMO-002', 'bayar_di_toko', 'lunas',  'selesai',               now()->subDays(8), 1],
            ['RF-DEMO-003', 'transfer_bank', 'proses', 'menunggu_dikonfirmasi', now(),             3],
        ];

        foreach ($contoh as [$invoice, $metode, $bayar, $status, $tanggal, $jumlahItem]) {
            $items = $produk->take($jumlahItem);
            $total = $items->sum('harga');

            $order = Order::firstOrCreate(['no_invoice' => $invoice], [
                'id_user'            => $user->id_user,
                'metode_pembayaran'  => $metode,
                'status_pembayaran'  => $bayar,
                'status_pesanan'     => $status,
                'total_harga'        => $total,
                'jadwal_pengambilan' => $tanggal->copy()->addDays(2)->setTime(10, 0),
            ]);

            if (! $order->wasRecentlyCreated) {
                continue;
            }

            DB::table('orders')->where('id_order', $order->id_order)->update(['dibuat_pada' => $tanggal]);

            foreach ($items as $p) {
                $order->items()->create([
                    'id_product'   => $p->id_product,
                    'jumlah'       => 1,
                    'harga_satuan' => $p->harga,
                ]);
            }
        }

        $this->command->info("Pesanan contoh dibuat untuk {$user->nama}.");
    }
}