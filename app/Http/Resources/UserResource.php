<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 *
 * @package App\Http\Resources
 */
class UserResource extends JsonResource
{
    /**
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_user' => $this->id_user,
            'nama' => $this->nama,
            'email' => $this->email,
            'nmr_telepon' => $this->nmr_telepon,
            'alamat' => $this->alamat,
            'role' => $this->role,
        ];
    }
}
