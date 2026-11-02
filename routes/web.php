<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\KaryawanController;
use App\Http\Controllers\admin\LaporanPenjualanController;
use App\Http\Controllers\admin\PesananController;
use App\Http\Controllers\admin\ProdukController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\pelanggan\KatalogController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman umum
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Login, Registrasi, Logout
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.proses');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Area Admin & Karyawan -> /admin/*
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', RoleMiddleware::class . ':admin,karyawan'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Laporan Penjualan
        Route::get('laporan-penjualan', [LaporanPenjualanController::class, 'index'])->name('laporan.index');

        // Manajemen Staff
        Route::get('karyawan/export', [KaryawanController::class, 'export'])->name('karyawan.export');
        Route::patch('karyawan/{karyawan}/status', [KaryawanController::class, 'toggleStatus'])->name('karyawan.status');
        Route::resource('karyawan', KaryawanController::class)->except(['show']);

        // Katalog Produk (route statis harus di atas {produk})
        Route::get('produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('produk/data', [ProdukController::class, 'data'])->name('produk.data');
        Route::get('produk/export', [ProdukController::class, 'export'])->name('produk.export');
        Route::get('produk/create', [ProdukController::class, 'create'])->name('produk.create');
        Route::post('produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::get('produk/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');

        // Pesanan
        Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::get('pesanan/create', [PesananController::class, 'create'])->name('pesanan.create');
        Route::post('pesanan', [PesananController::class, 'store'])->name('pesanan.store');
    });

/*
|--------------------------------------------------------------------------
| Area Pelanggan -> /pelanggan/*
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', RoleMiddleware::class . ':pelanggan'])
    ->prefix('pelanggan')
    ->name('pelanggan.')
    ->group(function () {

        // Katalog
        Route::get('katalog', [KatalogController::class, 'index'])->name('katalog');
        Route::get('katalog/data', [KatalogController::class, 'data'])->name('katalog.data');

        // Detail produk (aktifkan setelah method show() dan view-nya dibuat)
        // Route::get('produk/{produk}', [KatalogController::class, 'show'])->name('produk.show');
    });
