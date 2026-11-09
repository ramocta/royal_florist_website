<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\KaryawanController;
use App\Http\Controllers\admin\LaporanPenjualanController;
use App\Http\Controllers\admin\PelangganController;
use App\Http\Controllers\admin\PesananController;
use App\Http\Controllers\admin\ProdukController;
use App\Http\Controllers\admin\ProfilController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\pelanggan\KatalogController;
use App\Http\Controllers\pelanggan\CheckoutController;
use App\Http\Controllers\pelanggan\RiwayatPesananController;
use App\Http\Controllers\pelanggan\CustomBuketController;
use App\Http\Controllers\admin\StockController;
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

        // Laporan Penjualan (Export PDF memakai route ini juga dengan ?format=json)
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

        // Rute Untuk Management Stock Produk
        Route::get('/stok', [StockController::class, 'index'])->name('stok.index');
        Route::post('/stok/{id}/adjust', [StockController::class, 'adjust'])->name('stok.adjust');
        Route::delete('/stok/{id}', [StockController::class, 'destroy'])->name('stok.destroy');

        // Manajemen Pelanggan
        Route::get('pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');
        Route::get('pelanggan/export', [PelangganController::class, 'export'])->name('pelanggan.export');
        Route::get('pelanggan/create', [PelangganController::class, 'create'])->name('pelanggan.create');
        Route::post('pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store');
        Route::get('pelanggan/{pelanggan}', [PelangganController::class, 'show'])->name('pelanggan.show');
        Route::get('pelanggan/{pelanggan}/edit', [PelangganController::class, 'edit'])->name('pelanggan.edit');
        Route::put('pelanggan/{pelanggan}', [PelangganController::class, 'update'])->name('pelanggan.update');
        Route::delete('pelanggan/{pelanggan}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');

        // Pesanan
        Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::get('pesanan/create', [PesananController::class, 'create'])->name('pesanan.create');
        Route::post('pesanan', [PesananController::class, 'store'])->name('pesanan.store');

        // Admin Profil
        Route::get('profil', [ProfilController::class, 'index'])->name('profil');
        Route::put('profil', [ProfilController::class, 'update'])->name('profil.update');
        Route::put('profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');
        Route::post('profil/foto', [ProfilController::class, 'updatePhoto'])->name('profil.foto.update');
        Route::delete('profil/foto', [ProfilController::class, 'deletePhoto'])->name('profil.foto.delete');

        // Pengaturan
        Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

        // Pengaturan
        Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::get('pengaturan/landing', [PengaturanController::class, 'landing'])->name('pengaturan.landing');
        Route::put('pengaturan/landing', [PengaturanController::class, 'updateLanding'])->name('pengaturan.landing.update');

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

        // Riwayat Pesanan
        Route::get('riwayat-pesanan', [RiwayatPesananController::class, 'index'])->name('riwayat');

        // Detail produk (aktifkan setelah method show() dan view-nya dibuat)
        // Route::get('produk/{produk}', [KatalogController::class, 'show'])->name('produk.show');

    });

/*
|--------------------------------------------------------------------------
| Custom Buket (khusus pelanggan) -> /custom-buket
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', RoleMiddleware::class . ':pelanggan'])->group(function () {
    Route::get('/custom-buket', [CustomBuketController::class, 'index'])->name('custom-buket.index');
    Route::post('/custom-buket', [CustomBuketController::class, 'store'])->name('custom-buket.store');
});

/*
|--------------------------------------------------------------------------
| Pelanggan Checkout
|--------------------------------------------------------------------------
*/

Route::get('/checkout', [CheckoutController::class, 'index'])
    ->name('checkout');

Route::post('/checkout', [CheckoutController::class, 'store'])
    ->name('checkout.store');