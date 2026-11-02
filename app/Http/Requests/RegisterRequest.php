<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'nmr_telepon' => 'required|string|regex:/^[0-9]+$/|min:10|max:15|unique:users,nmr_telepon',
            'alamat' => 'nullable|string|max:255',
        ];
    }
}