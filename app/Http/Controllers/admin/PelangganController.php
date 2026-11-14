<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    /** Akun yang terdaftar kurang dari sekian hari dianggap pelanggan Baru, selebihnya Lama. */
    private const HARI_BARU = 30;

    public function index(Request $request)
    {
        $this->perbaruiKategori();

        return view('admin.pelanggan-index', [
            'pelanggans' => $this->filter($request)->paginate(10)->withQueryString(),
            'q'     => trim((string) $request->query('q', '')),
            'kat'   => (string) $request->query('kategori', ''),
            'total' => $this->pelanggan()->count(),
            'lama'  => $this->pelanggan()->where('kategori_pelanggan', 'Lama')->count(),
            'baru'  => $this->pelanggan()->where('kategori_pelanggan', 'Baru')->count(),
        ]);
    }

    public function export(Request $request)
    {
        $this->perbaruiKategori();

        $rows = $this->filter($request)->get();

        return response()->streamDownload(function () use ($rows) {
            echo "\xEF\xBB\xBF"; // BOM agar Excel membaca huruf dengan benar
            $out = fopen('php://output', 'w');
            fputcsv($out, ['No', 'Nama', 'Email', 'Kontak', 'Alamat', 'Kategori']);
            foreach ($rows as $i => $p) {
                fputcsv($out, [$i + 1, $p->nama, $p->email, $p->nmr_telepon, $p->alamat, $p->kategori_pelanggan]);
            }
            fclose($out);
        }, 'data-pelanggan.csv', ['Content-Type' => 'text/csv']);
    }

    public function show(User $pelanggan)
    {
        abort_unless($pelanggan->role === 'pelanggan', 404);

        $this->perbaruiKategori();
        $pelanggan->refresh();

        $aktif = $pelanggan->orders()->where('status_pesanan', '!=', 'dibatalkan');

        return view('admin.pelanggan-show', [
            'pelanggan'    => $pelanggan,
            'kategori'     => $pelanggan->kategori_pelanggan,
            'terdaftar'    => \Illuminate\Support\Carbon::parse($pelanggan->created_at),
            'totalPesanan' => (clone $aktif)->count(),
            'totalBelanja' => (float) (clone $aktif)->sum('total_harga'),
        ]);
    }

    public function destroy(User $pelanggan)
    {
        abort_unless($pelanggan->role === 'pelanggan', 404);

        if ($pelanggan->orders()->exists()) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena sudah memiliki pesanan.');
        }

        try {
            $pelanggan->delete();
        } catch (QueryException) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena masih terhubung dengan data lain.');
        }

        return redirect()->route('admin.pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
    }

    private function filter(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $kat = (string) $request->query('kategori', '');

        return $this->pelanggan()
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('nmr_telepon', 'like', "%{$q}%");
            }))
            ->when(in_array($kat, ['Baru', 'Lama'], true), fn ($query) => $query->where('kategori_pelanggan', $kat))
            ->orderBy('id_user');
    }

    /** Samakan kolom kategori_pelanggan dengan tanggal terdaftar (created_at). */
    private function perbaruiKategori(): void
    {
        $batas = now()->subDays(self::HARI_BARU);

        $this->pelanggan()->where('created_at', '<', $batas)->where('kategori_pelanggan', '!=', 'Lama')
            ->update(['kategori_pelanggan' => 'Lama']);

        $this->pelanggan()->where('created_at', '>=', $batas)->where('kategori_pelanggan', '!=', 'Baru')
            ->update(['kategori_pelanggan' => 'Baru']);
    }

    private function pelanggan()
    {
        return User::where('role', 'pelanggan');
    }
}
