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
        $users = User::where('role', 'pelanggan')->take(3)->get();
        $produk = Product::take(3)->get();

        if ($users->isEmpty() || $produk->isEmpty()) {
            $this->command->warn('Butuh minimal 1 user pelanggan dan 1 produk.');
            return;
        }

        foreach ($users as $user) {
            $this->buatPesanan($user, $produk, "RF-DEMO-{$user->id_user}1", [
                'jenis_pengambilan' => 'dikirim',
                'alamat_tujuan'     => 'Siska Amelia (Jl. Raya Darmo No. 45, Surabaya)',
                'metode_pembayaran' => 'QRIS',
                'status_pembayaran' => 'lunas',
                'status_pesanan'    => 'dalam_pengiriman',
                'nama_kurir'        => 'Budi Santoso',
                'no_hp_kurir'       => '0812-3456-7890',
                'estimasi_tiba'     => now()->setTime(12, 0),
                'catatan_toko'      => 'Kurir sudah berangkat',
            ], 3, [
                'dikonfirmasi'     => '10:18',
                'diproses'         => '10:25',
                'dalam_pengiriman' => '11:32',
            ]);

            $this->buatPesanan($user, $produk, "RF-DEMO-{$user->id_user}2", [
                'jenis_pengambilan'  => 'ambil_di_toko',
                'metode_pembayaran'  => 'bayar_di_toko',
                'status_pembayaran'  => 'lunas',
                'status_pesanan'     => 'siap_diambil',
                'jadwal_pengambilan' => now()->addDays(2)->setTime(17, 0),
                'catatan_toko'       => 'Bunga siap diambil mulai pukul 11:30 WIB',
            ], 1, [
                'dikonfirmasi' => '09:40',
                'diproses'     => '09:50',
                'siap_diambil' => '15:30',
            ]);
        }

        $this->command->info('Pesanan contoh dibuat untuk ' . $users->pluck('nama')->join(', ') . '.');
    }

    private function buatPesanan(User $user, $produk, string $invoice, array $atribut, int $jumlahItem, array $riwayat): void
    {
        $items = $produk->take($jumlahItem);

        $order = Order::unguarded(fn () => Order::firstOrCreate(['no_invoice' => $invoice], $atribut + [
            'id_user'     => $user->id_user,
            'total_harga' => $items->sum('harga'),
        ]));

        if (! $order->wasRecentlyCreated) {
            return;
        }

        DB::table('orders')->where('id_order', $order->id_order)->update(['dibuat_pada' => now()->subDay()]);

        foreach ($items as $p) {
            $order->items()->create(['id_product' => $p->id_product, 'jumlah' => 1, 'harga_satuan' => $p->harga]);
        }

        foreach ($riwayat as $status => $jam) {
            DB::table('order_status_history')->insert([
                'id_order'       => $order->id_order,
                'status_pesanan' => $status,
                'diubah_oleh'    => $user->id_user,
                'diubah_pada'    => now()->setTimeFromTimeString($jam),
            ]);
        }
    }
}
