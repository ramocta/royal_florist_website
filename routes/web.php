<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\KaryawanController;
use App\Http\Controllers\admin\LaporanPenjualanController;
use App\Http\Controllers\admin\PesananController;
use App\Http\Controllers\admin\ProdukController;
use App\Http\Controllers\WebAuthController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Login, Registrasi, Logout
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.proses');
Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [WebAuthController::class, 'register'])->name('register.proses');
Route::post('/logout', [WebAuthController::class, 'logout'])->middleware('auth')->name('logout');

// Area admin: hanya untuk user yang sudah login dengan role admin atau karyawan.
// Kalau Manajemen Staff mau khusus admin saja, pindahkan route karyawan ke grup
// terpisah dengan RoleMiddleware::class . ':admin'.
Route::middleware(['auth', RoleMiddleware::class . ':admin,karyawan'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard -> http://localhost:8000/admin/dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Laporan Penjualan -> http://localhost:8000/admin/laporan-penjualan
        Route::get('laporan-penjualan', [LaporanPenjualanController::class, 'index'])->name('laporan.index');

        // Manajemen Staff (sisi admin)
        Route::get('karyawan/export', [KaryawanController::class, 'export'])->name('karyawan.export');
        Route::patch('karyawan/{karyawan}/status', [KaryawanController::class, 'toggleStatus'])->name('karyawan.status');
        Route::resource('karyawan', KaryawanController::class)->except(['show']);

        // Katalog Produk
        // Urutan penting: route statis (data, export, create) harus di atas {produk}
        Route::get('produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('produk/data', [ProdukController::class, 'data'])->name('produk.data');
        Route::get('produk/export', [ProdukController::class, 'export'])->name('produk.export');
        Route::get('produk/create', [ProdukController::class, 'create'])->name('produk.create');
        Route::post('produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::get('produk/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');

        // Pesanan -> http://localhost:8000/admin/pesanan
        // Route statis (create) harus di atas {pesanan} kalau nanti ada halaman detail
        Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::get('pesanan/create', [PesananController::class, 'create'])->name('pesanan.create');
        Route::post('pesanan', [PesananController::class, 'store'])->name('pesanan.store');
    });