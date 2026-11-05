<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthControllerApi;

// Route::post('/register', [AuthControllerApi::class, 'register']);
Route::post('/login-karyawan', [AuthControllerApi::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthControllerApi::class, 'logout']);
});
