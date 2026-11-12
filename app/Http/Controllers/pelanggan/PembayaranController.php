<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    // Batas waktu pembayaran sejak pesanan dibuat
    private const BATAS_JAM = 24;

    // Metode bayar yang ditangani halaman ini. Metode dipilih pelanggan di halaman checkout.
    private const LABEL_METODE = ['QRIS' => 'QRIS', 'transfer_bank' => 'Transfer Bank'];

    // Halaman pembayaran: QRIS atau rekening transfer bank (sesuai pilihan di checkout) + form unggah bukti
    public function show(Request $request, Order $order)
    {
        $this->pastikanMilikSendiri($request, $order);

        if ($tolak = $this->tolakJikaTidakBisaBayar($order)) {
            return $tolak;
        }

        $batas = $this->batas($order);

        return view('pelanggan.pembayaran', [
            'order'      => $order,
            'kadaluarsa' => now()->greaterThanOrEqualTo($batas),
            // Dikirim sebagai detik tersisa (bukan jam server), supaya hitung mundur tidak terpengaruh jam HP pengguna
            'sisaDetik'  => max(0, now()->diffInSeconds($batas, false)),
            'metode'     => $order->metode_pembayaran,
            'qris'       => $this->urlQris(),
            'nmid'       => Setting::ambil('qris_nmid'),
            'bank'       => Setting::rekeningBank(),   // null = rekening belum diisi admin
        ]);
    }

    // Terima bukti pembayaran
    public function kirim(Request $request, Order $order)
    {
        $this->pastikanMilikSendiri($request, $order);

        if ($tolak = $this->tolakJikaTidakBisaBayar($order)) {
            return $tolak;
        }

        if (now()->greaterThanOrEqualTo($this->batas($order))) {
            return back()->with('error', 'Batas waktu pembayaran sudah habis. Silakan buat pesanan baru.');
        }

        $data = $request->validate([
            'nama_pengirim' => ['required', 'string', 'max:100'],
            'aplikasi'      => ['required', 'string', 'max:50'],
            'bukti'         => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],   // 5120 KB = 5 MB
            'catatan'       => ['nullable', 'string', 'max:255'],
        ], [
            'required'  => ':attribute wajib diisi.',
            'max'       => [
                'string' => ':attribute maksimal :max karakter.',
                'file'   => 'Ukuran :attribute maksimal 5 MB.',
            ],
            'mimes'     => ':attribute harus berformat JPG, PNG, atau PDF.',
            'file'      => ':attribute harus berupa file.',
        ], [
            'nama_pengirim' => 'Nama pengirim',
            'aplikasi'      => 'Aplikasi pembayaran',
            'bukti'         => 'Bukti pembayaran',
            'catatan'       => 'Catatan',
        ]);

        // Disimpan di storage/app/public/bukti-pembayaran/ dengan nama acak (bukan nama asli dari pengguna)
        $path = $request->file('bukti')->store('bukti-pembayaran', 'public');

        try {
            $berhasil = DB::transaction(function () use ($order, $data, $path, $request) {
                // Baca ulang dan kunci barisnya: kalau tombol ditekan dua kali, hanya satu yang lolos
                $baru = Order::whereKey($order->getKey())->lockForUpdate()->first();

                if ($baru->status_pembayaran !== 'belum_dibayar' || $baru->status_pesanan === 'dibatalkan') {
                    return false;
                }

                $baru->update([
                    'bukti_pembayaran'  => $path,
                    'status_pembayaran' => 'proses',
                    'status_pesanan'    => 'menunggu_dikonfirmasi',
                    'dibayar_pada'      => now(),
                ]);

                // Data tambahan dari form dicatat di riwayat status, supaya kasir bisa membacanya saat memverifikasi
                $metode  = self::LABEL_METODE[$baru->metode_pembayaran];
                $catatan = "Bukti pembayaran {$metode} diunggah pelanggan. Pengirim: {$data['nama_pengirim']}. Bank/aplikasi: {$data['aplikasi']}.";
                if (! empty($data['catatan'])) {
                    $catatan .= " Catatan ke kasir: {$data['catatan']}";
                }

                OrderStatusHistory::create([
                    'id_order'       => $baru->id_order,
                    'status_pesanan' => 'menunggu_dikonfirmasi',
                    'catatan'        => $catatan,
                    'diubah_oleh'    => $request->user()->id_user,
                ]);

                return true;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);   // jangan tinggalkan file yatim kalau penyimpanan gagal
            throw $e;
        }

        if (! $berhasil) {
            Storage::disk('public')->delete($path);
            return redirect()->route('pelanggan.riwayat')->with('error', 'Pembayaran untuk pesanan ini sudah pernah dikirim.');
        }

        return redirect()->route('pelanggan.riwayat')
            ->with('success', "Bukti pembayaran pesanan {$order->no_invoice} terkirim. Kasir akan memverifikasinya segera.");
    }

    // Pesanan orang lain dibalas 404 (bukan 403), supaya nomor invoice tidak bisa ditebak-tebak
    private function pastikanMilikSendiri(Request $request, Order $order): void
    {
        abort_unless($order->id_user === $request->user()->id_user, 404);
    }

    // Halaman ini hanya untuk pesanan QRIS / transfer bank yang belum dibayar dan belum dibatalkan
    private function tolakJikaTidakBisaBayar(Order $order)
    {
        if (! array_key_exists($order->metode_pembayaran, self::LABEL_METODE)) {
            return redirect()->route('pelanggan.riwayat')->with('error', 'Pesanan ini tidak dibayar lewat QRIS atau transfer bank.');
        }
        if ($order->status_pesanan === 'dibatalkan') {
            return redirect()->route('pelanggan.riwayat')->with('error', 'Pesanan ini sudah dibatalkan.');
        }
        if ($order->status_pembayaran !== 'belum_dibayar') {
            return redirect()->route('pelanggan.riwayat')->with('error', 'Pembayaran untuk pesanan ini sudah dikirim.');
        }

        return null;
    }

    private function batas(Order $order)
    {
        return $order->dibuat_pada->copy()->addHours(self::BATAS_JAM);
    }

    // Gambar QRIS toko: dari Pengaturan (kunci qris_gambar) atau public/images/qris-default.png. Kosong = belum diatur.
    private function urlQris(): ?string
    {
        $berkas = Setting::ambil('qris_gambar');

        if ($berkas && Storage::disk('public')->exists($berkas)) {
            return asset('storage/' . $berkas);
        }

        return file_exists(public_path('images/qris-default.png')) ? asset('images/qris-default.png') : null;
    }
}
