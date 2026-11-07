<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    // Tab "Profil Toko": tampilkan form (nilainya dibaca langsung di view lewat Setting::ambil)
    public function index()
    {
        return view('admin.pengaturan-profil');
    }

    // Tab "Profil Toko": simpan
    public function update(Request $request)
    {
        // Hanya kunci yang ada di aturan() yang lolos, jadi tidak ada isian liar yang bisa masuk tabel
        $data = $request->validate($this->aturan(), $this->pesan(), $this->label());

        // Checkbox yang tidak dicentang tidak terkirim sama sekali, jadi ubah ke '1' atau '0' secara eksplisit
        $data['toko_buka'] = $request->boolean('toko_buka') ? '1' : '0';

        Setting::simpan($data);

        return redirect()->route('admin.pengaturan.index')->with('success', 'Pengaturan toko disimpan.');
    }

    private function aturan(): array
    {
        return [
            'toko_nama'            => 'required|string|max:100',
            'toko_email'           => 'required|email|max:100',
            'toko_whatsapp'        => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'toko_alamat'          => 'required|string|max:500',
            'toko_jam_senin_sabtu' => 'required|string|max:50',
            'toko_jam_minggu'      => 'required|string|max:50',
            'toko_buka'            => 'nullable|boolean',
        ];
    }

    private function pesan(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email'    => 'Format email tidak valid.',
            'max'      => ':attribute maksimal :max karakter.',
            'string'   => ':attribute harus berupa teks.',
            'regex'    => 'Format :attribute tidak valid (hanya angka, spasi, +, -, dan tanda kurung).',
        ];
    }

    private function label(): array
    {
        return [
            'toko_nama'            => 'Nama toko',
            'toko_email'           => 'Email toko',
            'toko_whatsapp'        => 'Nomor WhatsApp',
            'toko_alamat'          => 'Alamat toko',
            'toko_jam_senin_sabtu' => 'Jam operasional Senin-Sabtu',
            'toko_jam_minggu'      => 'Jam operasional Minggu',
        ];
    }
}
