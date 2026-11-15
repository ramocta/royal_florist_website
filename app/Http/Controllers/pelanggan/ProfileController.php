<?php

namespace App\Http\Controllers\pelanggan;

use App\Http\Controllers\Controller;
use App\Models\AlamatPengiriman;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Services\ProfileCustomerService;
use Illuminate\Http\Request;
use Exception;

class ProfileController extends Controller
{

    public function __construct(
        protected ProfileCustomerService $profileCustomerService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $alamatList = AlamatPengiriman::where('id_user', $user->getKey())
        ->orderByDesc('is_utama')
        ->latest()
        ->get();
        return view('pelanggan.profile.profile', compact('user', 'alamatList'));
    }


    public function update(UpdateProfileRequest $request)
    {
        try {
            $this->profileCustomerService->updateProfile(
                $request->user(),
                $request->validated(),
                $request->file('foto_profil')
            );

            return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
        } catch (Exception $e) {
            report($e);

            return redirect()->back()->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request)
{
    try {
        $this->profileCustomerService->updatePassword(
            $request->user(),
            $request->validated('password')
        );

        return redirect()->to(url()->previous() . '#sandi')
            ->with('success', 'Kata sandi berhasil diperbarui.');
    } catch (Exception $e) {
        report($e);

        return redirect()->to(url()->previous() . '#sandi')
            ->withErrors(['error' => 'Gagal memperbarui kata sandi.'], 'updatePassword');
    }
}

    public function deleteProfile(Request $request)
    {
        try {
            $this->profileCustomerService->deleteProfilePhoto($request->user());

            return redirect()
                ->back()
                ->with('success', 'Foto profil berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
