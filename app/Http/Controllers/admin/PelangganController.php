<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.pelanggan-index', [
            'pelanggans' => $this->filter($request)->paginate(10)->withQueryString(),
            'q'          => trim((string) $request->query('q', '')),
            'kat'        => (string) $request->query('kategori', ''),
            'total'      => Pelanggan::count(),
            'lama'       => Pelanggan::where('kategori', 'Lama')->count(),
            'baru'       => Pelanggan::where('kategori', 'Baru')->count(),
            'rata'       => (float) Pelanggan::selectRaw('COALESCE(SUM(total_belanja) / NULLIF(SUM(total_pesanan), 0), 0) as rata')->value('rata'),
        ]);
    }

    public function export(Request $request)
    {
        $rows = $this->filter($request)->get();

        return response()->streamDownload(function () use ($rows) {
            echo "\xEF\xBB\xBF"; // BOM agar Excel membaca huruf dengan benar
            $out = fopen('php://output', 'w');
            fputcsv($out, ['No', 'Nama', 'Kontak', 'Kategori', 'Total Pesanan', 'Total Belanja']);
            foreach ($rows as $i => $p) {
                fputcsv($out, [$i + 1, $p->nama, $p->kontak, $p->kategori, $p->total_pesanan, $p->total_belanja]);
            }
            fclose($out);
        }, 'data-pelanggan.csv', ['Content-Type' => 'text/csv']);
    }

    public function create()
    {
        return view('admin.pelanggan-create');
    }

    public function store(Request $request)
    {
        Pelanggan::create($this->validated($request));

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Pelanggan $pelanggan)
    {
        return view('admin.pelanggan-show', compact('pelanggan'));
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('admin.pelanggan-edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $pelanggan->update($this->validated($request));

        return redirect()->route('admin.pelanggan.show', $pelanggan)
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    private function filter(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $kat = (string) $request->query('kategori', '');

        return Pelanggan::query()
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('kontak', 'like', "%{$q}%");
            }))
            ->when($kat !== '', fn ($query) => $query->where('kategori', $kat))
            ->orderBy('id');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama'     => ['required', 'string', 'max:100'],
            'kontak'   => ['required', 'string', 'max:30'],
            'kategori' => ['required', 'in:Baru,Lama'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max'      => ':attribute maksimal :max karakter.',
            'in'       => ':attribute tidak valid.',
        ], [
            'nama'     => 'Nama',
            'kontak'   => 'Kontak',
            'kategori' => 'Kategori',
        ]);
    }
}
