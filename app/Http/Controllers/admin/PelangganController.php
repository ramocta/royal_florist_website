<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        Pelanggan::sinkronDariUser();

        return view('admin.pelanggan-index', [
            'pelanggans' => $this->filter($request)->paginate(10)->withQueryString(),
            'q'     => trim((string) $request->query('q', '')),
            'kat'   => (string) $request->query('kategori', ''),
            'total' => Pelanggan::count(),
            'lama'  => Pelanggan::where('kategori', 'Lama')->count(),
            'baru'  => Pelanggan::where('kategori', 'Baru')->count(),
        ]);
    }

    public function export(Request $request)
    {
        Pelanggan::sinkronDariUser();

        $rows = $this->filter($request)->get();

        return response()->streamDownload(function () use ($rows) {
            echo "\xEF\xBB\xBF"; // BOM agar Excel membaca huruf dengan benar
            $out = fopen('php://output', 'w');
            fputcsv($out, ['No', 'Nama', 'Email', 'Kontak', 'Kategori', 'Terdaftar']);
            foreach ($rows as $i => $p) {
                fputcsv($out, [$i + 1, $p->nama, $p->email, $p->kontak, $p->kategori, $p->created_at->format('Y-m-d')]);
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
        Pelanggan::create($request->validate([
            'nama'   => ['required', 'string', 'max:100'],
            'email'  => ['required', 'email', 'max:100', 'unique:pelanggan,email'],
            'kontak' => ['required', 'string', 'max:30'],
        ], $this->pesan(), $this->label()));

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Pelanggan $pelanggan)
    {
        $aktif = $pelanggan->orders()->where('status_pesanan', '!=', 'dibatalkan');

        return view('admin.pelanggan-show', [
            'pelanggan'    => $pelanggan,
            'kategori'     => $pelanggan->kategori,
            'terdaftar'    => $pelanggan->created_at,
            'totalPesanan' => (clone $aktif)->count(),
            'totalBelanja' => (float) (clone $aktif)->sum('total_harga'),
        ]);
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('admin.pelanggan-edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $pelanggan->update($request->validate([
            'nama'   => ['required', 'string', 'max:100'],
            'email'  => ['required', 'email', 'max:100', Rule::unique('pelanggan', 'email')->ignore($pelanggan->id)],
            'kontak' => ['required', 'string', 'max:30'],
        ], $this->pesan(), $this->label()));

        return redirect()->route('admin.pelanggan.show', $pelanggan)
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        if ($pelanggan->id_user) {
            return back()->with('error', 'Pelanggan ini memiliki akun terdaftar sehingga tidak dapat dihapus.');
        }

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
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('kontak', 'like', "%{$q}%");
            }))
            ->when(in_array($kat, ['Baru', 'Lama'], true), fn ($query) => $query->where('kategori', $kat))
            ->orderBy('id');
    }

    private function pesan(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max'      => ':attribute maksimal :max karakter.',
            'email'    => 'Format email tidak valid.',
            'unique'   => ':attribute sudah terdaftar.',
        ];
    }

    private function label(): array
    {
        return ['nama' => 'Nama', 'email' => 'Email', 'kontak' => 'Kontak'];
    }
}
