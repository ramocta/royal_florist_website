<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KaryawanProfilController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['data' => $this->format($request->user())]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nama'          => 'required|string|max:100',
            'email'         => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($user->id_user, 'id_user'),
            ],
            'telepon'       => 'nullable|string|max:20',
            'alamat'        => 'nullable|string|max:255',
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'umur'          => 'nullable|integer|min:15|max:80',
        ], [
            'required' => ':attribute wajib diisi.',
            'email'    => 'Format email tidak valid.',
            'unique'   => ':attribute sudah dipakai akun lain.',
        ], [
            'nama'  => 'Nama lengkap',
            'email' => 'Email',
        ]);

        $user->update([
            'nama'          => $data['nama'],
            'email'         => $data['email'],
            'nmr_telepon'   => $data['telepon'] ?? null,
            'alamat'        => $data['alamat'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'umur'          => $data['umur'] ?? null,
        ]);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $this->format($user->fresh()),
        ]);
    }

    public function foto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'foto.required' => 'Foto wajib dipilih.',
            'foto.image'    => 'File harus berupa gambar.',
            'foto.max'      => 'Ukuran foto maksimal 2 MB.',
        ]);

        $user = $request->user();

        if ($user->foto) {
            Storage::disk('public')->delete($user->foto);
        }

        $path = $request->file('foto')->store('profil', 'public');
        $user->update(['foto' => $path]);

        return response()->json([
            'message' => 'Foto berhasil diperbarui.',
            'data'    => $this->format($user->fresh()),
        ]);
    }

    /** Publik: menyajikan file foto lewat Laravel (agar CORS ikut berlaku). */
    public function fotoFile($id)
    {
        $user = User::where('id_user', $id)->firstOrFail();

        abort_unless(
            $user->foto && Storage::disk('public')->exists($user->foto),
            404
        );

        return response()->file(Storage::disk('public')->path($user->foto));
    }

    private function format($user): array
    {
        return [
            'nama'          => $user->nama,
            'email'         => $user->email,
            'telepon'       => $user->nmr_telepon ?? '',
            'alamat'        => $user->alamat ?? '',
            'jenis_kelamin' => $user->jenis_kelamin,
            'umur'          => $user->umur,
            'foto_url'      => $user->foto
                ? url('/api/karyawan-foto/' . $user->id_user) . '?v=' . crc32($user->foto)
                : null,
        ];
    }
}