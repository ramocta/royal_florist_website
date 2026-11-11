<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthControllerApi;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\KaryawanAuthController;
use App\Http\Controllers\Api\KaryawanBerandaController;
use App\Http\Controllers\Api\ProdukApiController;
use App\Http\Middleware\KaryawanAktif;

// Login dan register pelanggan
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Login karyawan
Route::post('/login-karyawan', [AuthControllerApi::class, 'login']);

// Daftar pesanan
Route::get('/orders', [OrderController::class, 'index']);

// Route setelah login
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthControllerApi::class, 'logout']);
});

// Foto produk (publik, sama seperti katalog pelanggan)
Route::get('/produk-foto/{produk}', [ProdukApiController::class, 'foto']);

// Mobile karyawan
Route::prefix('karyawan')->group(function () {
    Route::post('/login', [KaryawanAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', KaryawanAktif::class])->group(function () {
        Route::post('/logout', [KaryawanAuthController::class, 'logout']);
        Route::get('/beranda', [KaryawanBerandaController::class, 'index']);

        Route::get('/produk', [ProdukApiController::class, 'index']);
        Route::post('/produk', [ProdukApiController::class, 'store']);
        Route::post('/produk/{produk}', [ProdukApiController::class, 'update']);
        Route::patch('/produk/{produk}/stok', [ProdukApiController::class, 'stok']);
        Route::patch('/produk/{produk}/ketersediaan', [ProdukApiController::class, 'ketersediaan']);
        Route::delete('/produk/{produk}', [ProdukApiController::class, 'destroy']);
    });
});
