<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\KategoriService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function __construct(private KategoriService $layanan) {}

    public function data(): JsonResponse
    {
        return response()->json($this->layanan->daftar());
    }

    public function store(Request $request): JsonResponse
    {
        $kategori = $this->layanan->tambah($request);

        return response()->json(['message' => "Kategori {$kategori->category_name} ditambahkan."], 201);
    }

    public function update(Request $request, Category $kategori): JsonResponse
    {
        $kategori = $this->layanan->ubahNama($request, $kategori);

        return response()->json(['message' => "Kategori diubah menjadi {$kategori->category_name}."]);
    }

    public function destroy(Category $kategori): JsonResponse
    {
        $this->layanan->hapus($kategori);

        return response()->json(['message' => 'Kategori dihapus.']);
    }
}
