<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileCustomerService
{
    public function updateProfile(User $user, array $data, ?UploadedFile $fotoProfil = null): User
    {
        // Hanya ambil field yang benar-benar dikirim, tidak menimpa data lain dengan null
        $updateData = Arr::only($data, ['nama', 'email', 'nmr_telepon', 'alamat']);

        if ($fotoProfil) {
            $this->deletePhotoFile($user);
            $updateData['foto_profil'] = $fotoProfil->store('profiles', 'public');
        }

        $user->update($updateData);

        return $user->fresh();
    }

    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    public function deleteProfilePhoto(User $user): User
    {
        $this->deletePhotoFile($user);

        $user->update(['foto_profil' => null]);

        return $user->fresh();
    }

    private function deletePhotoFile(User $user): void
    {
        if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
            Storage::disk('public')->delete($user->foto_profil);
        }
    }
}