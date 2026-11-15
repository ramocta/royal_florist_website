<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    $user = $this->user();

    return [
        'nama' => 'required|string|max:255',
        'email' => [
            'required', 'string', 'email', 'max:255',
            Rule::unique('users', 'email')->ignore($user->getKey(), $user->getKeyName()),
        ],
        'nmr_telepon' => [
            'nullable', 'string', 'regex:/^[0-9]+$/', 'min:10', 'max:15',
            Rule::unique('users', 'nmr_telepon')->ignore($user->getKey(), $user->getKeyName()),
        ],
        'alamat' => 'nullable|string',
        'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ];
}
}
