{{--
  LAYOUT ADMIN: topbar + sidebar + area isi. Ditulis sekali, dipakai semua halaman admin.

  Cara pakai di halaman:
    @extends('layouts.admin')
    @section('title', 'Judul Tab Browser')
    @section('content')
        ...isi halaman...
    @endsection
--}}
@php
    // [label, ikon, route tujuan, pola route untuk menandai menu aktif]
    // Kalau nama route temanmu beda, cukup ubah di array ini.
    $menu = [
        ['Dashboard',          'home',     'admin.dashboard',       'admin.dashboard'],
        ['Laporan Penjualan',  'chart',    'admin.laporan.index',   'admin.laporan.*'],
        ['Pelanggan',          'user',     'admin.pelanggan.index', 'admin.pelanggan.*'],
        ['Pesanan',            'cart',     'admin.pesanan.index',   'admin.pesanan.*'],
        ['Produk',             'cube',     'admin.produk.index',    'admin.produk.*'],
        ['Stok',               'stack',    'admin.stok.index',      'admin.stok.*'],
        ['Manajemen Staff',    'users',    'admin.karyawan.index',  'admin.karyawan.*'],
        ['Manajemen Progress', 'progress', 'admin.progress.index',  'admin.progress.*'],
        ['Pengaturan',         'cog',      'admin.pengaturan.index','admin.pengaturan.*'],
    ];
    $namaAdmin = auth()->user()?->nama ?? 'Admin';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | Royal Florist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-neutral-800">

    {{-- TOPBAR --}}
    <header class="flex items-center justify-between bg-pinksoft px-6 py-3">
        <div>
            <p class="font-display text-2xl font-semibold leading-none text-maroon">Royal Florist</p>
            <p class="mt-1 text-[10px] text-maroon/70">Fresh Flower &bull; Special Moment</p>
        </div>

        <div class="flex items-center gap-4">
            <div class="hidden items-center gap-2 rounded-lg bg-white px-3 py-2 text-xs font-medium text-maroon sm:flex">
                <x-icon name="calendar" size="h-4 w-4" />
                {{ now()->startOfWeek()->translatedFormat('d') }} - {{ now()->endOfWeek()->translatedFormat('d F Y') }}
            </div>

            <button type="button" class="text-maroon" aria-label="Notifikasi">
                <x-icon name="bell" size="h-6 w-6" />
            </button>

            {{-- Dropdown tanpa JavaScript: <details> membuka/menutup sendiri --}}
            <details class="relative">
                <summary class="flex cursor-pointer list-none items-center gap-2 text-sm font-medium text-maroon">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-maroon text-sm font-semibold text-white">
                        {{ strtoupper(substr($namaAdmin, 0, 1)) }}
                    </span>
                    <span class="hidden sm:inline">{{ $namaAdmin }}</span>
                    <x-icon name="chevron" size="h-4 w-4" />
                </summary>
                <div class="absolute right-0 z-10 mt-2 w-40 rounded-xl border border-pinkline bg-white p-1 shadow-lg">
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full rounded-lg px-3 py-2 text-left text-sm text-maroon hover:bg-pinksoft">Keluar</button>
                        </form>
                    @endif
                </div>
            </details>
        </div>
    </header>

    <div class="flex flex-1">
        {{-- SIDEBAR --}}
        <aside class="hidden w-60 shrink-0 bg-maroon p-3 md:block">
            <nav class="space-y-1">
                @foreach ($menu as [$label, $ikon, $rute, $pola])
                    @php $aktif = request()->routeIs($pola); @endphp
                    <a href="{{ Route::has($rute) ? route($rute) : '#' }}"
                       class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm transition
                              {{ $aktif ? 'bg-white font-medium text-maroon' : 'text-pink-100 hover:bg-white/10' }}">
                        <x-icon :name="$ikon" size="h-5 w-5" />
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- ISI HALAMAN --}}
        <main class="min-w-0 flex-1 p-6">
            @if (session('success'))
                <div class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
