<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\KategoriService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KategoriApiController extends Controller
{
    public function store(Request $request, KategoriService $layanan): JsonResponse
    {
        $kategori = $layanan->tambah($request);

        return response()->json(['message' => "Kategori {$kategori->category_name} ditambahkan."], 201);
    }
}
