<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    // Daftar ini hanya berisi karyawan. Admin (hanya 1) dan pelanggan sengaja tidak ikut.
    private const ROLES = ['karyawan'];

    // READ: daftar + statistik + pencarian + filter
    public function index(Request $request)
    {
        $stat = [
            'total'    => User::whereIn('role', self::ROLES)->count(),
            'aktif'    => User::whereIn('role', self::ROLES)->where('status', 'aktif')->count(),
            'nonaktif' => User::whereIn('role', self::ROLES)->where('status', 'nonaktif')->count(),
        ];

        $staff = User::whereIn('role', self::ROLES)
            ->when($request->q, fn ($query, $q) => $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            }))
            ->when($request->role, fn ($query, $role) => $query->where('role', $role))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('id_user')
            ->paginate(5)
            ->withQueryString(); // filter tidak hilang saat pindah halaman

        return view('admin.karyawan-index', compact('staff', 'stat'));
    }

    // CREATE: tampilkan form
    public function create()
    {
        return view('admin.karyawan-create');
    }

    // CREATE: simpan
    public function store(Request $request)
    {
        $data = $request->validate($this->aturan(), $this->pesan(), $this->label());

        // Kolom fillable di User.php masuk lewat konstruktor.
        // Password otomatis di-hash oleh cast 'hashed' di model.
        $user = new User([
            'nama'        => $data['nama'],
            'email'       => $data['email'],
            'nmr_telepon' => $data['nmr_telepon'],
            'alamat'      => $data['alamat'],
            'status'      => $data['status'],
            'password'    => $data['password'],
        ]);

        // role sengaja TIDAK fillable (supaya tidak bisa diisi dari request), jadi diisi manual di server
        $user->role = 'karyawan';
        $user->save();

        return redirect()->route('admin.karyawan.index')->with('success', 'Staff berhasil ditambahkan.');
    }

    // UPDATE: tampilkan form
    public function edit(User $karyawan)
    {
        $this->pastikanKaryawan($karyawan);
        return view('admin.karyawan-edit', compact('karyawan'));
    }

    // UPDATE: simpan
    public function update(Request $request, User $karyawan)
    {
        $this->pastikanKaryawan($karyawan);

        $data = $request->validate($this->aturan($karyawan), $this->pesan(), $this->label());

        $karyawan->fill([
            'nama'        => $data['nama'],
            'email'       => $data['email'],
            'nmr_telepon' => $data['nmr_telepon'],
            'alamat'      => $data['alamat'],
            'status'      => $data['status'],
        ]);

        if (! empty($data['password'])) {   // kosong = password tidak diganti
            $karyawan->password = $data['password']; // di-hash otomatis oleh cast 'hashed'
        }

        $karyawan->save();

        return redirect()->route('admin.karyawan.index')->with('success', 'Data staff diperbarui.');
    }

    // UPDATE: aktif <-> nonaktif (tombol ikon mata di daftar)
    public function toggleStatus(User $karyawan)
    {
        $this->pastikanKaryawan($karyawan);

        $karyawan->status = $karyawan->status === 'aktif' ? 'nonaktif' : 'aktif';
        $karyawan->save();

        return back()->with('success', "Status {$karyawan->nama} sekarang {$karyawan->status}.");
    }

    // DELETE
    public function destroy(User $karyawan)
    {
        $this->pastikanKaryawan($karyawan);

        try {
            $karyawan->delete();
        } catch (QueryException $e) {
            // Biasanya karena foreign key: staff ini masih terkait data lain (misalnya pesanan)
            return back()->with('error', 'Staff tidak bisa dihapus karena masih terkait data lain. Nonaktifkan saja.');
        }

        return redirect()->route('admin.karyawan.index')->with('success', 'Staff dihapus.');
    }

    // Export CSV (bisa dibuka di Excel)
    public function export()
    {
        $rows = User::whereIn('role', self::ROLES)->orderBy('id_user')->get();

        return response()->streamDownload(function () use ($rows) {
            echo "\xEF\xBB\xBF"; // BOM supaya Excel membaca huruf dengan benar
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nama', 'Email', 'Role', 'No. Telepon', 'Status']);
            foreach ($rows as $u) {
                fputcsv($out, [$u->nama, $u->email, $u->role, $u->nmr_telepon, $u->status]);
            }
            fclose($out);
        }, 'data-staff.csv', ['Content-Type' => 'text/csv']);
    }

    // Aturan validasi dipakai bersama oleh store dan update.
    // Batas panjang mengikuti kolom di tabel users (nama 100, email 100, nmr_telepon 15).
    private function aturan(?User $user = null): array
    {
        return [
            'nama'        => 'required|string|max:100',
            'email'       => ['required', 'email', 'max:100',
                $user
                    ? Rule::unique('users', 'email')->ignore($user->id_user, 'id_user')
                    : Rule::unique('users', 'email')],
            'nmr_telepon' => 'required|string|max:15',
            'alamat'      => 'required|string',
            'status'      => ['required', Rule::in(['aktif', 'nonaktif'])],
            // Saat edit, password boleh kosong (berarti tidak diganti)
            'password'    => $user ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
        ];
    }

    private function pesan(): array
    {
        return [
            'required'  => ':attribute wajib diisi.',
            'email'     => 'Format email tidak valid.',
            'unique'    => ':attribute sudah dipakai.',
            'max'       => ':attribute maksimal :max karakter.',
            'min'       => ':attribute minimal :min karakter.',
            'confirmed' => 'Konfirmasi password tidak sama.',
            'in'        => ':attribute tidak valid.',
            'string'    => ':attribute harus berupa teks.',
        ];
    }

    private function label(): array
    {
        return [
            'nama'        => 'Nama',
            'email'       => 'Email',
            'nmr_telepon' => 'No. telepon',
            'alamat'      => 'Alamat',
            'status'      => 'Status',
            'password'    => 'Password',
        ];
    }

    // Cegah ubah/hapus akun admin atau pelanggan lewat URL yang diketik manual
    private function pastikanKaryawan(User $user): void
    {
        abort_if($user->role !== 'karyawan', 404);
    }
}
