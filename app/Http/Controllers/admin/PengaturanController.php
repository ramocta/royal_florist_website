<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
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
        return view('admin.pengaturan-landing', [
            'banners' => Banner::orderBy('urutan')->orderBy('id_banner')->get(),
        ]);
    }

    public function updateLanding(Request $request)
    {
        // Banner diatur lewat tombol sendiri (unggah / hapus / aktif-nonaktif / urutan), jadi di sini hanya footer
        $data = $request->validate($this->aturanLanding(), $this->pesan(), $this->label());

        Setting::simpan($data);

        return redirect()->route('admin.pengaturan.landing')->with('success', 'Landing page diperbarui.');
    }

    /* ---------- Banner slider (boleh lebih dari satu) ---------- */

    public function tambahBanner(Request $request)
    {
        $request->validate([
            'banner'   => 'required|array|min:1|max:10',
            // max dalam KB (2048 = 2 MB per gambar). SVG tidak diizinkan karena bisa memuat skrip.
            'banner.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'banner.required' => 'Pilih minimal satu gambar banner.',
            'banner.max'      => 'Maksimal 10 gambar sekali unggah.',
            'banner.*.image'  => 'Berkas harus berupa gambar.',
            'banner.*.mimes'  => 'Banner harus berformat JPG, PNG, atau WEBP.',
            'banner.*.max'    => 'Ukuran tiap banner maksimal 2 MB.',
        ]);

        // Banner baru ditaruh paling belakang
        $urutan = (int) Banner::max('urutan');

        foreach ($request->file('banner') as $berkas) {
            Banner::create([
                'gambar'    => $berkas->store('banners', 'public'),  // nama file diacak Laravel
                'urutan'    => ++$urutan,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.pengaturan.landing')
            ->with('success', count($request->file('banner')) . ' banner ditambahkan.');
    }

    public function toggleBanner(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return redirect()->route('admin.pengaturan.landing')
            ->with('success', $banner->is_active ? 'Banner diaktifkan.' : 'Banner disembunyikan dari beranda.');
    }

    // Geser banner ke atas / ke bawah dengan menukar nilai urutan dengan tetangganya
    public function geserBanner(Request $request, Banner $banner)
    {
        $arah = $request->input('arah') === 'naik' ? 'naik' : 'turun';

        $semua = Banner::orderBy('urutan')->orderBy('id_banner')->get()->values();
        $i = $semua->search(fn ($b) => $b->id_banner === $banner->id_banner);
        $j = $arah === 'naik' ? $i - 1 : $i + 1;

        if ($j >= 0 && $j < $semua->count()) {
            $semua->splice($i, 1);
            $semua->splice($j, 0, [$banner]);
            // Tulis ulang urutan 1..n supaya selalu rapi, tidak bergantung nilai lama
            foreach ($semua as $n => $b) {
                if ($b->urutan !== $n + 1) {
                    $b->update(['urutan' => $n + 1]);
                }
            }
        }

        return redirect()->route('admin.pengaturan.landing');
    }

    public function hapusBanner(Banner $banner)
    {
        $berkas = $banner->gambar;
        $banner->delete();
        Storage::disk('public')->delete($berkas);

        return redirect()->route('admin.pengaturan.landing')->with('success', 'Banner dihapus.');
    }

    /* ===================== TAB: PEMBAYARAN QRIS ===================== */

    public function qris()
    {
        return view('admin.pengaturan-qris');
    }

    public function updateQris(Request $request)
    {
        $data = $request->validate($this->aturanQris(), $this->pesan(), $this->label());

        // Gambar bukan teks, jadi dikeluarkan dulu dari $data dan diproses terpisah (pola yang sama dengan landing page)
        unset($data['qris_gambar']);
        $gambarLama = Setting::ambil('qris_gambar');

        if ($request->hasFile('qris_gambar')) {
            // Nama file diacak oleh Laravel, jadi aman dari nama aneh/bentrok. Database hanya menyimpan alamatnya, mis. "qris/AbC123.png"
            $data['qris_gambar'] = $request->file('qris_gambar')->store('qris', 'public');
        }

        Setting::simpan($data);

        // Hapus QRIS lama SETELAH yang baru tersimpan, supaya kalau penyimpanan gagal QRIS lama masih ada
        if (isset($data['qris_gambar']) && $gambarLama) {
            Storage::disk('public')->delete($gambarLama);
        }

        return redirect()->route('admin.pengaturan.qris')->with('success', 'QRIS toko diperbarui.');
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

    private function aturanQris(): array
    {
        return [
            // max dalam KB (2048 = 2 MB). SVG tidak diizinkan karena bisa memuat skrip.
            'qris_gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            // NMID (nomor ID merchant QRIS) opsional; kalau diisi akan tampil di halaman pembayaran pelanggan
            'qris_nmid'   => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9]+$/'],
        ];
    }

    private function aturanLanding(): array
    {
        return [
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
            'qris_nmid.regex'     => 'NMID hanya boleh berisi huruf dan angka, tanpa spasi.',
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
            'qris_gambar'          => 'Gambar QRIS',
            'qris_nmid'            => 'NMID',
            'footer_deskripsi'     => 'Deskripsi footer',
        ];
    }
}