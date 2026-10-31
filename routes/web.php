<?php

use App\Http\Controllers\admin\KaryawanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Manajemen Staff (sisi admin)
// Middleware auth + role:admin dipasang nanti, setelah login selesai.
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('karyawan/export', [KaryawanController::class, 'export'])->name('karyawan.export');
    Route::patch('karyawan/{karyawan}/status', [KaryawanController::class, 'toggleStatus'])->name('karyawan.status');
    Route::resource('karyawan', KaryawanController::class)->except(['show']);
});