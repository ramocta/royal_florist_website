<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $kat = (string) $request->query('kategori', '');

        $query = Pelanggan::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('kontak', 'like', "%{$q}%");
            });
        }
        if ($kat !== '') {
            $query->where('kategori', $kat);
        }

        $pelanggans = $query->orderBy('id')->paginate(10)->withQueryString();

        return view('admin.pelanggan-index', [
            'pelanggans' => $pelanggans,
            'q'      => $q,
            'kat'    => $kat,
            'total'  => Pelanggan::count(),
            'vip'    => Pelanggan::where('kategori', 'VIP')->count(),
            'baru'   => Pelanggan::where('kategori', 'Baru')->count(),
            'rata'   => (float) Pelanggan::selectRaw('COALESCE(SUM(total_belanja)/NULLIF(SUM(total_pesanan),0),0) as rata')->value('rata'),
        ]);
    }

    // EXPORT: unduh CSV (ikut filter pencarian & kategori yang sedang aktif)
    public function export(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $kat = (string) $request->query('kategori', '');

        $query = Pelanggan::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('kontak', 'like', "%{$q}%");
            });
        }
        if ($kat !== '') {
            $query->where('kategori', $kat);
        }

        $rows = $query->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            echo "\xEF\xBB\xBF"; // BOM supaya Excel membaca huruf dengan benar
            $out = fopen('php://output', 'w');
            fputcsv($out, ['No', 'Nama', 'Kontak', 'Kategori', 'Total Pesanan', 'Total Belanja']);
            foreach ($rows as $i => $p) {
                fputcsv($out, [$i + 1, $p->nama, $p->kontak, $p->kategori, $p->total_pesanan, $p->total_belanja]);
            }
            fclose($out);
        }, 'data-pelanggan.csv', ['Content-Type' => 'text/csv']);
    }

    // CREATE: tampilkan form pelanggan baru
    public function create()
    {
        return view('admin.pelanggan-create');
    }

    // CREATE: simpan data
    public function store(Request $request)
    {
        $data = $request->validate($this->aturan(), $this->pesan(), $this->label());

        // total_pesanan & total_belanja otomatis 0 (default di migration)
        Pelanggan::create($data);

        return redirect()->route('admin.pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    // DETAIL
    public function show(Pelanggan $pelanggan)
    {
        return view('admin.pelanggan-show', compact('pelanggan'));
    }

    // EDIT: tampilkan form
    public function edit(Pelanggan $pelanggan)
    {
        return view('admin.pelanggan-edit', compact('pelanggan'));
    }

    // EDIT: simpan perubahan
    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $request->validate($this->aturan(), $this->pesan(), $this->label());

        $pelanggan->update($data);

        return redirect()->route('admin.pelanggan.show', $pelanggan)->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    private function aturan(): array
    {
        return [
            'nama'     => ['required', 'string', 'max:100'],
            'kontak'   => ['required', 'string', 'max:30'],
            'kategori' => ['required', 'in:VIP,Reguler,Baru'],
        ];
    }

    private function pesan(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max'      => ':attribute terlalu panjang (maks :max karakter).',
            'in'       => ':attribute tidak valid.',
        ];
    }

    private function label(): array
    {
        return ['nama' => 'Nama', 'kontak' => 'Kontak', 'kategori' => 'Kategori'];
    }
}
