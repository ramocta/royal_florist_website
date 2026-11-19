<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthControllerApi;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\KaryawanAuthController;
use App\Http\Controllers\Api\KaryawanBerandaController;
use App\Http\Controllers\Api\KaryawanProfilController;
use App\Http\Controllers\Api\ProdukApiController;
use App\Http\Controllers\Api\OrderSummaryController;
use App\Http\Middleware\KaryawanAktif;

// Login dan register pelanggan
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Login karyawan
Route::post('/login-karyawan', [AuthControllerApi::class, 'login']);

// Daftar pesanan (tetap seperti semula)
Route::get('/orders', [OrderController::class, 'index']);

// Detail & ubah status pesanan (khusus karyawan yang sudah login)
Route::middleware(['auth:sanctum', KaryawanAktif::class])->group(function () {
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
});

// Route setelah login
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthControllerApi::class, 'logout']);
});

// Foto produk (publik, sama seperti katalog pelanggan)
Route::get('/produk-foto/{produk}', [ProdukApiController::class, 'foto']);

// Foto profil karyawan (publik, disajikan lewat Laravel agar CORS berlaku)
Route::get('/karyawan-foto/{id}', [KaryawanProfilController::class, 'fotoFile']);

// Mobile karyawan
Route::prefix('karyawan')->group(function () {
    Route::post('/login', [KaryawanAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', KaryawanAktif::class])->group(function () {
        Route::post('/logout', [KaryawanAuthController::class, 'logout']);
        Route::get('/beranda', [KaryawanBerandaController::class, 'index']);

        // Profil
        Route::get('/profil', [KaryawanProfilController::class, 'show']);
        Route::put('/profil', [KaryawanProfilController::class, 'update']);
        Route::post('/profil/foto', [KaryawanProfilController::class, 'foto']);

        Route::get('/produk', [ProdukApiController::class, 'index']);
        Route::post('/produk', [ProdukApiController::class, 'store']);
        Route::post('/produk/{produk}', [ProdukApiController::class, 'update']);
        Route::patch('/produk/{produk}/stok', [ProdukApiController::class, 'stok']);
        Route::patch('/produk/{produk}/ketersediaan', [ProdukApiController::class, 'ketersediaan']);
        Route::delete('/produk/{produk}', [ProdukApiController::class, 'destroy']);

        Route::get('/ringkasan', [OrderSummaryController::class, 'index']);
        Route::get('/pesanan', [OrderSummaryController::class, 'pesanan']);
    });
});