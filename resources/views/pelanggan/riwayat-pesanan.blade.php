@php
    $namaUser = auth()->user()?->nama ?? 'Pelanggan';
    $nav = [
        ['Beranda', url('/'), request()->is('/')],
        ['Katalog', Route::has('pelanggan.katalog') ? route('pelanggan.katalog') : '#', request()->routeIs('pelanggan.katalog*')],
        ['Riwayat Pesanan', route('pelanggan.riwayat'), request()->routeIs('pelanggan.riwayat')],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Pesanan | Royal Florist</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col font-sans text-neutral-800" style="background: linear-gradient(180deg, #fdf3f6, #f6e3e9);">

    <header class="text-white" style="background: linear-gradient(90deg, #5a1230, #7a1f3d);">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ url('/') }}" class="flex items-center gap-2 font-display text-xl font-semibold">
                <svg width="30" height="30" viewBox="0 0 32 32" fill="none" aria-hidden="true"><circle cx="16" cy="9" r="6" fill="#f4b6c6"/><circle cx="9" cy="17" r="6" fill="#e9a4b8"/><circle cx="23" cy="17" r="6" fill="#f4b6c6"/><circle cx="16" cy="23" r="6" fill="#e9a4b8"/><circle cx="16" cy="16" r="4" fill="#fff"/></svg>
                Royal Florist
            </a>

            <nav class="hidden items-center gap-8 text-xs sm:flex">
                @foreach ($nav as [$label, $url, $aktif])
                    <a href="{{ $url }}" class="{{ $aktif ? 'font-semibold text-white' : 'text-pink-100 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <details class="relative">
                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold text-maroon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    Profil Saya
                </summary>
                <div class="absolute right-0 z-10 mt-2 w-40 rounded-xl border border-pinkline bg-white p-1 text-maroon shadow-lg">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-pinksoft">Keluar</button>
                    </form>
                </div>
            </details>
        </div>

        <nav class="flex justify-center gap-6 pb-3 text-xs sm:hidden">
            @foreach ($nav as [$label, $url, $aktif])
                <a href="{{ $url }}" class="{{ $aktif ? 'font-semibold' : 'text-pink-100' }}">{{ $label }}</a>
            @endforeach
        </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-6 py-8">
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

    $statusUi = [
        'menunggu_pembayaran'   => ['Menunggu Pembayaran', 'bg-amber-100 text-amber-800'],
        'menunggu_dikonfirmasi' => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-800'],
        'diproses'              => ['Sedang Diproses', 'bg-amber-100 text-amber-800'],
        'siap_diambil'          => ['Siap Diambil', 'bg-blue-100 text-blue-800'],
        'selesai'               => ['Selesai', 'bg-green-100 text-green-800'],
        'dibatalkan'            => ['Dibatalkan', 'bg-rose-100 text-rose-700'],
    ];
    $metode = ['bayar_di_toko' => 'Bayar di Toko', 'QRIS' => 'QRIS', 'transfer_bank' => 'Transfer Bank'];
    $bayarUi = [
        'belum_dibayar' => ['Belum Dibayar', 'text-rose-700'],
        'proses'        => ['Menunggu Verifikasi', 'text-amber-700'],
        'lunas'         => ['Lunas', 'text-green-700'],
    ];
    $tabs = ['semua' => 'Semua', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
@endphp

<h1 class="font-display text-3xl font-semibold text-maroon">Riwayat Pesanan</h1>
<p class="mt-1 text-xs text-neutral-600">Pantau proses pesanan bunga Anda beserta status pengiriman dan pengambilan di toko.</p>

<div class="mt-4 flex flex-wrap gap-2">
    @foreach ($tabs as $kunci => $label)
        <a href="{{ route('pelanggan.riwayat', ['status' => $kunci]) }}"
           class="rounded-full border px-4 py-1.5 text-xs font-semibold {{ $filter === $kunci ? 'border-maroon bg-maroon text-white' : 'border-pinkline bg-white text-neutral-800 hover:bg-pinkcard' }}">
            {{ $label }} ({{ $jumlah[$kunci] }})
        </a>
    @endforeach
</div>

<div class="mt-6 space-y-5">
    @forelse ($daftar as $o)
        @php
            $item     = $o->items->first();
            $produk   = $item?->product;
            $lainnya  = $o->items->count() - 1;
            [$statusLabel, $statusClass] = $statusUi[$o->status_pesanan];
            [$bayarLabel, $bayarClass]   = $bayarUi[$o->status_pembayaran];
            if ($o->status_pesanan === 'selesai' && $o->status_pembayaran === 'lunas') {
                $statusLabel = 'Selesai & Lunas';
            }
        @endphp

        <article class="overflow-hidden rounded-3xl bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 bg-pinksoft px-7 py-4 text-xs">
                <div class="flex flex-wrap items-center gap-x-8 gap-y-1">
                    <span class="font-bold uppercase text-neutral-900">Ambil Mandiri di Toko</span>
                    <span class="text-neutral-600">No. Pesanan: <b class="text-maroon">#{{ $o->no_invoice }}</b></span>
                    <span class="text-neutral-600">Tanggal: {{ $o->dibuat_pada->locale('id')->translatedFormat('d F Y, H:i') }} WIB</span>
                    @if ($o->jadwal_pengambilan)
                        <span class="text-neutral-600">Tanggal Ambil: {{ $o->jadwal_pengambilan->locale('id')->translatedFormat('d F Y') }}</span>
                    @endif
                </div>
                <span class="rounded-full px-4 py-1.5 text-[11px] font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="px-7 py-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-5">
                        <div class="shrink-0 overflow-hidden bg-pinkcard" style="width: 90px; height: 78px; border-radius: 14px;">
                            @if ($produk?->foto_url)
                                <img src="{{ $produk->foto_url }}" alt="{{ $produk->nama_produk }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @endif
                        </div>
                        <div>
                            <p class="text-base font-bold text-neutral-900">
                                {{ $produk?->nama_produk ?? 'Produk tidak tersedia' }}
                                @if ($lainnya > 0) (+{{ $lainnya }} Item lainnya) @endif
                            </p>
                            <p class="mt-1 text-xs text-neutral-600">Lokasi Ambil: Outlets Royal Florist</p>
                            <p class="mt-1 text-xs font-bold {{ $o->metode_pembayaran === 'bayar_di_toko' ? 'text-green-800' : 'text-maroon' }}">
                                Pembayaran {{ $metode[$o->metode_pembayaran] }}: {{ $bayarLabel }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-xs text-neutral-600">{{ $o->metode_pembayaran === 'bayar_di_toko' ? 'Total Tagihan Kasir:' : 'Total Pembayaran:' }}</p>
                        <p class="text-2xl font-bold {{ $o->metode_pembayaran === 'bayar_di_toko' ? 'text-neutral-900' : 'text-maroon' }}">{{ $rp($o->total_harga) }}</p>
                    </div>
                </div>

                @if ($o->bukti_pembayaran || in_array($o->status_pesanan, ['selesai', 'dibatalkan']))
                    <div class="mt-5 flex flex-wrap gap-3 border-t border-pinkline pt-5">
                        @if ($o->bukti_pembayaran)
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($o->bukti_pembayaran) }}" target="_blank"
                               class="rounded-full border-2 border-maroon px-7 py-2.5 text-xs font-bold text-maroon hover:bg-pinksoft">Lihat Bukti Bayar</a>
                        @endif
                        @if (in_array($o->status_pesanan, ['selesai', 'dibatalkan']))
                            <a href="{{ route('pelanggan.katalog') }}"
                               class="rounded-full bg-maroon px-7 py-2.5 text-xs font-bold text-white hover:opacity-90">Pesan Lagi &rarr;</a>
                        @endif
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="rounded-2xl bg-white px-6 py-14 text-center">
            <p class="text-sm font-semibold text-neutral-900">Belum ada pesanan</p>
            <p class="mt-1 text-xs text-neutral-600">Pesanan Anda akan muncul di sini setelah Anda berbelanja.</p>
            <a href="{{ route('pelanggan.katalog') }}" class="mt-4 inline-block rounded-full bg-maroon px-5 py-2 text-xs font-semibold text-white hover:opacity-90">Lihat Katalog</a>
        </div>
    @endforelse
</div>

@if ($daftar->hasPages())
    <div class="mt-6 flex justify-center gap-2 text-xs">
        <a href="{{ $daftar->previousPageUrl() ?? '#' }}" class="rounded-full border border-pinkline bg-white px-4 py-2 hover:bg-pinkcard">&lsaquo; Sebelumnya</a>
        <a href="{{ $daftar->nextPageUrl() ?? '#' }}" class="rounded-full border border-pinkline bg-white px-4 py-2 hover:bg-pinkcard">Berikutnya &rsaquo;</a>
    </div>
@endif
    </main>

    <footer class="text-white" style="background: linear-gradient(90deg, #5a1230, #7a1f3d);">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-6 py-6 text-[11px]">
            <p class="font-display text-sm font-semibold">Royal Florist</p>
            <p class="text-pink-100">&copy; {{ date('Y') }} Royal Florist. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
