@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
@php
    // Label + warna badge untuk tiap status_pesanan di database
    $status = [
        'menunggu_pembayaran'   => ['Menunggu Bayar',  'bg-neutral-100 text-neutral-600'],
        'menunggu_dikonfirmasi' => ['Menunggu Konfirmasi', 'bg-orange-100 text-orange-600'],
        'diproses'              => ['Diproses',        'bg-blue-100 text-blue-600'],
        'siap_diambil'          => ['Siap Kirim',      'bg-yellow-100 text-yellow-700'],
        'selesai'               => ['Selesai',         'bg-green-100 text-green-700'],
        'dibatalkan'            => ['Batal',           'bg-rose-100 text-rose-600'],
    ];

    // [judul, angka, keterangan, warna keterangan]
    $kartu = [
        ['Total Pesanan',    $stat['total'],    'Pesanan bulan ini',        'text-neutral-500'],
        ['Perlu Diproses',   $stat['diproses'], 'Sedang dirangkai florist', 'text-blue-600'],
        ['Siap Dikirim',     $stat['siap'],     'Menunggu kurir',           'text-yellow-600'],
        ['Selesai Hari Ini', $stat['selesai'],  'Berhasil terkirim',        'text-green-600'],
    ];
@endphp

{{-- -m-6 membatalkan padding <main> di layout supaya latar pink memenuhi area isi --}}
<div class="-m-6 min-h-full bg-pinksoft p-6">

    {{-- JUDUL + TOMBOL --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-maroon">Daftar Pesanan</h1>
            <p class="mt-1 text-xs text-neutral-500">
                Pantau pesanan masuk, status pembuatan buket, jadwal pengiriman, dan pembayaran.
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ Route::has('admin.pesanan.export') ? route('admin.pesanan.export') : '#' }}"
               class="flex items-center gap-2 rounded-lg border border-pinkline bg-white px-4 py-2.5 text-xs font-semibold text-maroon hover:bg-pinkcard">
                <x-icon name="export" size="h-4 w-4" /> Export Data
            </a>
            <a href="{{ Route::has('admin.pesanan.create') ? route('admin.pesanan.create') : '#' }}"
               class="flex items-center gap-1 rounded-lg bg-maroon px-5 py-2.5 text-xs font-semibold text-white hover:opacity-90">
                <x-icon name="plus" size="h-4 w-4" /> Pesanan Baru
            </a>
        </div>
    </div>

    {{-- KARTU STATISTIK --}}
    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kartu as [$judul, $angka, $ket, $warna])
            <div class="rounded-xl bg-white p-5">
                <p class="text-xs font-semibold text-maroon">{{ $judul }}</p>
                <p class="mt-1 text-3xl font-bold text-maroon">{{ $angka }}</p>
                <p class="mt-3 text-[11px] font-medium {{ $warna }}">{{ $ket }}</p>
            </div>
        @endforeach
    </div>

    {{-- TABEL PESANAN --}}
    <section class="mt-6 rounded-xl bg-white p-6">

        {{-- Filter: method GET supaya terbawa di URL dan saat pindah halaman --}}
        <form method="GET" action="{{ route('admin.pesanan.index') }}"
              class="flex flex-wrap items-center justify-between gap-3">
            <div class="relative w-full max-w-md">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">
                    <x-icon name="search" size="h-4 w-4" />
                </span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari No. ID / nama pemesan..."
                       class="w-full rounded-lg border border-pinkline py-2.5 pl-9 pr-3 text-xs placeholder-neutral-400 focus:border-maroon focus:outline-none">
            </div>

            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border border-pinkline bg-white px-4 py-2.5 text-xs text-maroon focus:border-maroon focus:outline-none">
                <option value="">Semua Status</option>
                @foreach ($status as $kode => [$label])
                    <option value="{{ $kode }}" @selected(request('status') === $kode)>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-xs">
                <thead>
                    <tr class="bg-pinksoft font-semibold text-maroon">
                        <th class="rounded-l-lg px-4 py-3">No</th>
                        <th class="px-4 py-3">ID Pesanan</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Item Produk</th>
                        <th class="px-4 py-3">Tgl Pengiriman</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="rounded-r-lg px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesanan as $o)
                        @php
                            [$label, $warna] = $status[$o->status_pesanan] ?? [$o->status_pesanan, 'bg-neutral-100 text-neutral-600'];
                            $pertama = $o->items->first();
                            $sisa = $o->items->count() - 1;
                        @endphp
                        <tr class="border-b border-pinkline/60 text-neutral-700 last:border-0">
                            <td class="px-4 py-4">{{ $pesanan->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-4">#{{ $o->no_invoice }}</td>
                            <td class="px-4 py-4 font-semibold text-maroon">{{ $o->user->nama ?? '-' }}</td>
                            <td class="px-4 py-4 text-neutral-500">
                                {{ $pertama?->product?->nama_produk ?? '-' }}
                                @if ($sisa > 0)
                                    <span class="text-[10px]">+{{ $sisa }} lainnya</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">{{ $o->jadwal_pengambilan?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-4 font-semibold text-maroon">Rp {{ number_format($o->total_harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-block min-w-[84px] rounded-full px-3 py-1.5 text-center text-[11px] font-semibold {{ $warna }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-semibold text-maroon">
                                <a href="#" class="hover:underline">Detail</a>
                                <span class="mx-0.5">|</span>
                                <a href="#" class="hover:underline">Cetak</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-neutral-500">
                                Pesanan tidak ditemukan. Ubah kata kunci atau filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-16 flex flex-wrap items-center justify-between gap-3 text-xs text-neutral-500">
            <span>Menampilkan {{ $pesanan->count() }} dari {{ $pesanan->total() }} pesanan</span>
            {{ $pesanan->links() }}
        </div>
    </section>
</div>
@endsection
