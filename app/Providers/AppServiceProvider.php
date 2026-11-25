<?php

namespace App\Providers;

use App\Services\NotifikasiAdminService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Setiap kali layout admin dirender, kirim variabel $notifikasi ke dalamnya.
        // Jadi lonceng di header terisi di SEMUA halaman admin tanpa mengubah satu per satu controller-nya.
        View::composer('layouts.admin', function ($view) {
            $view->with('notifikasi', app(NotifikasiAdminService::class)->ambil());
        });
    }
}
