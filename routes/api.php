<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthControllerApi;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\KaryawanAuthController;
use App\Http\Controllers\Api\KaryawanBerandaController;

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

// ---- Mobile karyawan: Beranda (tambahan) ----
Route::prefix('karyawan')->group(function () {
    Route::post('/login', [KaryawanAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [KaryawanAuthController::class, 'logout']);
        Route::get('/beranda', [KaryawanBerandaController::class, 'index']);
    });
});