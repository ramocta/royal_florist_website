<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    // Error disimpan di bag terpisah agar tidak tercampur dengan error biodata
    protected $errorBag = 'updatePassword';

    public function authorize(): bool
    {
        return true;
    }

    // Kembali ke tab kata sandi saat validasi gagal
    protected function getRedirectUrl(): string
    {
        return url()->previous() . '#sandi';
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ];
    }
}