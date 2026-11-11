<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use App\Models\AlamatPengiriman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlamatPengirimanController extends Controller
{
    // Menambahkan alamat baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_penerima' => 'required|string|max:100',
            'nomor_hp' => 'required|string|max:20',
            'alamat_lengkap' => 'required|string',
            'kota' => 'required|string|max:100',
            'catatan' => 'nullable|string',
            'is_utama' => 'nullable|boolean',
        ]);

        $idUser = auth()->id();
        $jadikanUtama = $request->boolean('is_utama');

        DB::transaction(function () use ($validated, $idUser, $jadikanUtama) {
            $belumAdaAlamat = !AlamatPengiriman::where('id_user', $idUser)->exists();

            if ($jadikanUtama || $belumAdaAlamat) {
                AlamatPengiriman::where('id_user', $idUser)
                    ->update(['is_utama' => false]);
            }

            AlamatPengiriman::create([
                ...$validated,
                'id_user' => $idUser,
                'is_utama' => $jadikanUtama || $belumAdaAlamat,
            ]);
        });

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    // Mengubah alamat
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_penerima' => 'required|string|max:100',
            'nomor_hp' => 'required|string|max:20',
            'alamat_lengkap' => 'required|string',
            'kota' => 'required|string|max:100',
            'catatan' => 'nullable|string',
            'is_utama' => 'nullable|boolean',
        ]);

        $alamat = AlamatPengiriman::where('id_user', auth()->id())
            ->findOrFail($id);

        $jadikanUtama = $request->boolean('is_utama');

        DB::transaction(function () use ($alamat, $validated, $jadikanUtama) {
            if ($jadikanUtama) {
                AlamatPengiriman::where('id_user', auth()->id())
                    ->update(['is_utama' => false]);
            }

            $alamat->update([
                ...$validated,
                'is_utama' => $jadikanUtama,
            ]);
        });

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    // Menghapus alamat
    public function destroy($id)
    {
        $alamat = AlamatPengiriman::where('id_user', auth()->id())
            ->findOrFail($id);

        $alamat->delete();

        // Jika alamat utama dihapus, pilih alamat lain sebagai utama.
        if ($alamat->is_utama) {
            $alamatBaru = AlamatPengiriman::where('id_user', auth()->id())
                ->orderByDesc('created_at')
                ->first();

            if ($alamatBaru) {
                $alamatBaru->update(['is_utama' => true]);
            }
        }

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    // Memilih alamat utama
    public function jadikanUtama($id)
    {
        $alamat = AlamatPengiriman::where('id_user', auth()->id())
            ->findOrFail($id);

        DB::transaction(function () use ($alamat) {
            AlamatPengiriman::where('id_user', auth()->id())
                ->update(['is_utama' => false]);

            $alamat->update(['is_utama' => true]);
        });

        return back()->with('success', 'Alamat utama berhasil diubah.');
    }
}
