@extends('layouts.admin')

@section('title', 'Pesanan')

{{-- Kalender di topbar: pilih tanggal pengiriman, daftar pesanan langsung menyesuaikan --}}
@section('top-tanggal')
    <form method="GET" action="{{ route('admin.pesanan.index') }}" class="date-pill" title="Filter tanggal pengiriman">
        <x-icon name="calendar" size="h-[18px] w-[18px]" />
        <input type="date" name="tanggal" value="{{ request('tanggal') }}" aria-label="Tanggal pengiriman"
               onchange="this.form.submit()">
        @if (request('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
        @endif
        @if (request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
    </form>
@endsection

@section('content')
@php
    // Label + warna badge (gaya sama dengan badge kategori di halaman Pelanggan)
    $status = [
        'menunggu_pembayaran'   => ['Menunggu Bayar',      'bg-neutral-100 text-neutral-700'],
        'menunggu_dikonfirmasi' => ['Menunggu Konfirmasi', 'bg-orange-100 text-orange-800'],
        'dikonfirmasi'          => ['Dikonfirmasi',        'bg-sky-100 text-sky-800'],
        'diproses'              => ['Diproses',            'bg-blue-100 text-blue-800'],
        'dalam_pengiriman'      => ['Dalam Pengiriman',    'bg-purple-100 text-purple-800'],
        'siap_diambil'          => ['Siap Kirim',          'bg-amber-100 text-amber-800'],
        'selesai'               => ['Selesai',             'bg-green-100 text-green-800'],
        'dibatalkan'            => ['Batal',               'bg-rose-100 text-rose-800'],
        'belum_memesan'         => ['Belum Memesan',       'bg-neutral-100 text-neutral-600'],
    ];
@endphp

<div class="space-y-5">

    {{-- JUDUL + TOMBOL --}}
    <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-pinkline bg-white px-6 py-5">
        <div>
            <h1 class="text-xl font-bold text-maroon">Daftar Pesanan</h1>
            <p class="mt-1 text-xs text-neutral-600">Pantau pesanan masuk, status pembuatan buket, jadwal pengiriman, dan pembayaran.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ Route::has('admin.pesanan.export') ? route('admin.pesanan.export', request()->only('q', 'status', 'tanggal')) : '#' }}"
               class="flex items-center gap-2 rounded-lg border border-pinkline bg-white px-4 py-2 text-xs font-semibold text-maroon hover:bg-pinksoft">
                <x-icon name="export" size="h-4 w-4" /> Export Data
            </a>
            <a href="{{ Route::has('admin.pesanan.create') ? route('admin.pesanan.create') : '#' }}"
               class="rounded-lg bg-maroon px-4 py-2 text-xs font-semibold text-white hover:opacity-90">
                + Pesanan Baru
            </a>
        </div>
    </section>

    {{-- KARTU STATISTIK (2 x 2 di HP, 4 sejajar di layar besar) --}}
    <section class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-maroon bg-white p-4">
            <span class="absolute right-4 top-4 hidden h-8 w-8 place-items-center rounded-full bg-pinksoft text-maroon sm:grid">
                <x-icon name="cart" size="h-4 w-4" />
            </span>
            <p class="text-xs text-neutral-600">Total Pesanan</p>
            <p class="mt-1.5 text-2xl font-bold text-neutral-900">{{ number_format($stat['total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-600">Pesanan bulan ini</p>
        </div>

        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-blue-500 bg-white p-4">
            <span class="absolute right-4 top-4 hidden h-8 w-8 place-items-center rounded-full bg-pinksoft text-maroon sm:grid">
                <x-icon name="progress" size="h-4 w-4" />
            </span>
            <p class="text-xs text-neutral-600">Perlu Diproses</p>
            <p class="mt-1.5 text-2xl font-bold text-neutral-900">{{ number_format($stat['diproses'], 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-blue-700">Sedang dirangkai florist</p>
        </div>

        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-amber-500 bg-white p-4">
            <span class="absolute right-4 top-4 hidden h-8 w-8 place-items-center rounded-full bg-pinksoft text-maroon sm:grid">
                <x-icon name="cube" size="h-4 w-4" />
            </span>
            <p class="text-xs text-neutral-600">Siap Dikirim</p>
            <p class="mt-1.5 text-2xl font-bold text-neutral-900">{{ number_format($stat['siap'], 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-amber-700">Menunggu kurir</p>
        </div>

        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-green-500 bg-white p-4">
            <span class="absolute right-4 top-4 hidden h-8 w-8 place-items-center rounded-full bg-pinksoft text-maroon sm:grid">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <p class="text-xs text-neutral-600">Selesai Hari Ini</p>
            <p class="mt-1.5 text-2xl font-bold text-neutral-900">{{ number_format($stat['selesai'], 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-green-700">Berhasil terkirim</p>
        </div>
    </section>

    {{-- TABEL --}}
    <section class="rounded-2xl border border-pinkline bg-white p-6">
        <form method="GET" action="{{ route('admin.pesanan.index') }}" class="mb-4 flex flex-wrap items-center justify-between gap-3">
            @if (request('tanggal'))
                <input type="hidden" name="tanggal" value="{{ request('tanggal') }}">
            @endif
            <div class="flex w-full max-w-md items-center gap-2 rounded-lg border border-pinkline bg-white px-3 py-2">
                <span class="text-neutral-500"><x-icon name="search" size="h-4 w-4" /></span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari No. ID / nama pemesan..."
                       class="w-full border-0 bg-transparent p-0 text-xs text-neutral-800 placeholder:text-neutral-500 focus:outline-none focus:ring-0">
            </div>

            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border border-pinkline bg-white py-2 pl-3 pr-8 text-xs text-neutral-800 focus:outline-none">
                <option value="">Semua Status</option>
                @foreach ($status as $kode => [$label])
                    <option value="{{ $kode }}" @selected(request('status') === $kode)>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        @if ($tanggal)
            <p class="mb-3 flex flex-wrap items-center gap-2 text-[11px] text-neutral-600">
                Menampilkan pesanan dengan tanggal pengiriman
                <b class="text-maroon">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</b>
                <a href="{{ route('admin.pesanan.index', request()->except('tanggal', 'page')) }}"
                   class="rounded-full bg-pinkcard px-2.5 py-0.5 font-semibold text-maroon hover:bg-pinkline">Hapus filter tanggal</a>
            </p>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] table-fixed text-left text-xs">
                <colgroup>
                    <col style="width: 56px">
                    <col style="width: 11%">
                    <col style="width: 17%">
                    <col style="width: 20%">
                    <col style="width: 12%">
                    <col style="width: 12%">
                    <col style="width: 15%">
                    <col style="width: 96px">
                </colgroup>
                <thead>
                    <tr class="bg-pinksoft text-maroon">
                        <th class="rounded-l-xl px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">ID Pesanan</th>
                        <th class="px-4 py-3 font-bold">Pelanggan</th>
                        <th class="px-4 py-3 font-bold">Item Produk</th>
                        <th class="px-4 py-3 font-bold">Tgl Pengiriman</th>
                        <th class="px-4 py-3 font-bold">Total</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="rounded-r-xl px-4 py-3 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesanan as $r)
                        @php
                            $o = $r['order'];
                            $nomor = $pesanan->firstItem() + $loop->index;
                        @endphp

                        @if ($o)
                            @php
                                [$label, $warna] = $status[$o->status_pesanan] ?? [$o->status_pesanan, 'bg-neutral-100 text-neutral-700'];
                                $pertama = $o->items->first();
                                $sisa = $o->items->count() - 1;
                            @endphp
                            <tr class="border-b border-pinkline/70 text-neutral-800 hover:bg-pinksoft/40">
                                <td class="px-4 py-3">{{ $nomor }}</td>
                                <td class="truncate px-4 py-3">#{{ $o->no_invoice }}</td>
                                <td class="truncate px-4 py-3 font-bold text-neutral-900">{{ $o->nama_pelanggan }}</td>
                                <td class="truncate px-4 py-3">
                                    {{ $pertama?->product?->nama_produk ?? '-' }}
                                    @if ($sisa > 0)
                                        <span class="text-[10px] text-neutral-500">+{{ $sisa }} lainnya</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $o->jadwal_pengambilan?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3 font-bold text-neutral-900">
                                    Rp {{ number_format($o->total_harga, 0, ',', '.') }}
                                    @if ($o->metode_pembayaran === 'DP')
                                        <span class="block text-[10px] font-normal text-neutral-500">DP Rp {{ number_format($o->jumlah_dp, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-[10px] font-semibold {{ $warna }}">{{ $label }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        {{-- Edit: buka form Pesanan Baru dalam mode edit --}}
                                        <a href="{{ route('admin.pesanan.edit', $o->id_order) }}" title="Edit" aria-label="Edit {{ $o->no_invoice }}"
                                           class="grid h-8 w-8 place-items-center rounded-lg bg-pinkcard text-maroon hover:bg-pinkline">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                            </svg>
                                        </a>
                                        {{-- Hapus: minta konfirmasi dulu --}}
                                        <form method="POST" action="{{ route('admin.pesanan.destroy', $o->id_order) }}"
                                              onsubmit="return confirm('Hapus pesanan #{{ $o->no_invoice }}? Data pesanan akan dihapus permanen dan stok produk dikembalikan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" aria-label="Hapus {{ $o->no_invoice }}"
                                                    class="grid h-8 w-8 place-items-center rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @else
                            {{-- Pelanggan yang sudah terdaftar tapi belum checkout. Kolom pesanan terisi otomatis setelah ia memesan. --}}
                            <tr class="border-b border-pinkline/70 text-neutral-400 hover:bg-pinksoft/40">
                                <td class="px-4 py-3 text-neutral-800">{{ $nomor }}</td>
                                <td class="px-4 py-3">-</td>
                                <td class="truncate px-4 py-3 font-bold text-neutral-900">{{ $r['pelanggan']->nama }}</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">-</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-neutral-100 px-3 py-1 text-[10px] font-semibold text-neutral-600">Belum Memesan</span>
                                </td>
                                <td class="px-4 py-3">
                                    {{-- Isi pesanan untuk pelanggan ini: tetap di halaman Pesanan (form Pesanan Baru) --}}
                                    <a href="{{ route('admin.pesanan.create', ['id_user' => $r['pelanggan']->id_user, 'mode' => 'ada']) }}"
                                       title="Isi pesanan" aria-label="Isi pesanan {{ $r['pelanggan']->nama }}"
                                       class="grid h-8 w-8 place-items-center rounded-lg bg-pinkcard text-maroon hover:bg-pinkline">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-neutral-500">Pesanan tidak ditemukan. Ubah kata kunci atau filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[11px] text-neutral-600">
                Menampilkan {{ $pesanan->count() }} dari {{ number_format($pesanan->total(), 0, ',', '.') }} data
            </p>
            <div class="flex items-center gap-1.5 text-xs">
                <a href="{{ $pesanan->previousPageUrl() ?? '#' }}" class="grid h-8 w-8 place-items-center rounded-lg border border-pinkline text-neutral-600 hover:bg-pinksoft">&lsaquo;</a>
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-maroon font-bold text-white">{{ $pesanan->currentPage() }}</span>
                <a href="{{ $pesanan->nextPageUrl() ?? '#' }}" class="grid h-8 w-8 place-items-center rounded-lg border border-pinkline text-neutral-600 hover:bg-pinksoft">&rsaquo;</a>
            </div>
        </div>
    </section>
</div>
@endsection