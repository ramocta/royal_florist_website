<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    /* ===================== TAB: PROFIL TOKO ===================== */

    public function index()
    {
        return view('admin.pengaturan-profil');
    }

    public function update(Request $request)
    {
        // Hanya kunci yang ada di aturan yang lolos, jadi tidak ada isian liar yang bisa masuk tabel
        $data = $request->validate($this->aturanProfil(), $this->pesan(), $this->label());

        // Checkbox yang tidak dicentang tidak terkirim sama sekali, jadi ubah ke '1' atau '0' secara eksplisit
        $data['toko_buka'] = $request->boolean('toko_buka') ? '1' : '0';

        Setting::simpan($data);

        return redirect()->route('admin.pengaturan.index')->with('success', 'Pengaturan toko disimpan.');
    }

    /* ================= TAB: MANAJEMEN LANDING PAGE ================= */

    public function landing()
    {
        return view('admin.pengaturan-landing');
    }

    public function updateLanding(Request $request)
    {
        $data = $request->validate($this->aturanLanding(), $this->pesan(), $this->label());

        // Gambar bukan teks, jadi dikeluarkan dulu dari $data dan diproses terpisah
        unset($data['hero_gambar']);
        $gambarLama = Setting::ambil('hero_gambar');

        if ($request->hasFile('hero_gambar')) {
            // Disimpan di storage/app/public/landing/ dengan nama acak (aman dari nama file yang aneh atau bentrok).
            // Yang masuk database hanya alamat file-nya, misalnya "landing/AbC123.png"
            $data['hero_gambar'] = $request->file('hero_gambar')->store('landing', 'public');
        }

        Setting::simpan($data);

        // Hapus gambar lama SETELAH data baru tersimpan, supaya kalau penyimpanan gagal gambar lama masih ada
        if (isset($data['hero_gambar']) && $gambarLama) {
            Storage::disk('public')->delete($gambarLama);
        }

        return redirect()->route('admin.pengaturan.landing')->with('success', 'Landing page diperbarui.');
    }

    /* ========================= ATURAN ========================= */

    private function aturanProfil(): array
    {
        return [
            'toko_nama'            => 'required|string|max:100',
            'toko_email'           => 'required|email|max:100',
            'toko_whatsapp'        => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'toko_alamat'          => 'required|string|max:500',
            'toko_jam_senin_sabtu' => 'required|string|max:50',
            'toko_jam_minggu'      => 'required|string|max:50',
            'toko_buka'            => 'nullable|boolean',
            // Rekening transfer bank. Boleh dikosongkan: selama kosong, pelanggan tidak ditawari Transfer Bank di checkout.
            'bank_nama'            => 'nullable|string|max:50',
            'bank_rekening'        => ['nullable', 'string', 'max:30', 'regex:/^[0-9\s\-]+$/'],
            'bank_atas_nama'       => 'nullable|string|max:100',
        ];
    }

    private function aturanLanding(): array
    {
        return [
            'hero_judul'       => 'required|string|max:150',
            'hero_deskripsi'   => 'required|string|max:300',
            'hero_tombol_teks' => 'required|string|max:30',
            // max di sini dalam KB (2048 = 2 MB). SVG sengaja tidak diizinkan karena bisa memuat skrip.
            'hero_gambar'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'footer_deskripsi' => 'required|string|max:300',
        ];
    }

    private function pesan(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email'    => 'Format email tidak valid.',
            'max'      => [
                'string' => ':attribute maksimal :max karakter.',
                'file'   => 'Ukuran :attribute maksimal 2 MB.',
            ],
            'string'   => ':attribute harus berupa teks.',
            'regex'    => 'Format :attribute tidak valid (hanya angka, spasi, +, -, dan tanda kurung).',
            'bank_rekening.regex' => 'Nomor rekening hanya boleh berisi angka, spasi, dan tanda hubung (-).',
            'image'    => ':attribute harus berupa gambar.',
            'mimes'    => ':attribute harus berformat JPG atau PNG.',
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
            'bank_nama'            => 'Nama bank',
            'bank_rekening'        => 'Nomor rekening',
            'bank_atas_nama'       => 'Nama pemilik rekening',
            'hero_judul'           => 'Judul utama',
            'hero_deskripsi'       => 'Deskripsi banner',
            'hero_tombol_teks'     => 'Teks tombol',
            'hero_gambar'          => 'Gambar hero',
            'footer_deskripsi'     => 'Deskripsi footer',
        ];
    }
}