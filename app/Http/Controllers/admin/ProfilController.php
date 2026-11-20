<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('admin.profil', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id_user . ',id_user',
            'nmr_telepon' => 'required|string|max:15',
            'alamat' => 'required|string',
        ]);

        $user->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'nmr_telepon' => $request->nmr_telepon,
            'alamat' => $request->alamat,
        ]);

        return redirect()->route('admin.profil')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors([
                'password_lama' => 'Kata sandi saat ini tidak sesuai.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = Auth::user();

        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        $path = $request->file('foto_profil')->store('foto-profil', 'public');

        $user->update([
            'foto_profil' => $path,
        ]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function deletePhoto()
    {
        $user = Auth::user();

        if ($user->foto_profil) {
            Storage::disk('public')->delete($user->foto_profil);
        }

        $user->update([
            'foto_profil' => null,
        ]);

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }
}