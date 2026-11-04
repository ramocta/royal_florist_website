@extends('layouts.admin')

@section('title', 'Pelanggan')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

    $badge = [
        'Lama' => 'bg-amber-100 text-amber-800',
        'Baru' => 'bg-blue-100 text-blue-800',
    ];
@endphp

<div class="space-y-5">

    <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-pinkline bg-white px-6 py-5">
        <div>
            <h1 class="text-xl font-bold text-maroon">Daftar Pelanggan</h1>
            <p class="mt-1 text-xs text-neutral-600">Kelola data pelanggan, riwayat transaksi, status keanggotaan, dan preferensi pesanan.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pelanggan.export', request()->only(['q', 'kategori'])) }}" class="flex items-center gap-2 rounded-lg border border-pinkline bg-white px-4 py-2 text-xs font-semibold text-maroon hover:bg-pinksoft">
                <x-icon name="export" size="h-4 w-4" /> Export Data
            </a>
            <a href="{{ route('admin.pelanggan.create') }}" class="rounded-lg bg-maroon px-4 py-2 text-xs font-semibold text-white hover:opacity-90">
                + Pelanggan Baru
            </a>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-maroon bg-white p-4">
            <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-pinksoft text-maroon">
                <x-icon name="users" size="h-4 w-4" />
            </span>
            <p class="text-xs text-neutral-600">Total Pelanggan</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900">{{ number_format($total, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-600">Terdaftar di sistem</p>
        </div>

        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-amber-500 bg-white p-4">
            <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-pinksoft text-maroon">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3z"/></svg>
            </span>
            <p class="text-xs text-neutral-600">Pelanggan Lama</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900">{{ number_format($lama, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-amber-700">Transaksi &gt; 5 Juta/bln</p>
        </div>

        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-maroon bg-white p-4">
            <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-pinksoft text-maroon">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1M9 10a3 3 0 100-6 3 3 0 000 6zM19 8v6M16 11h6"/></svg>
            </span>
            <p class="text-xs text-neutral-600">Pelanggan Baru</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900">{{ number_format($baru, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-600">Bulan ini</p>
        </div>

        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-green-600 bg-white p-4">
            <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-pinksoft text-maroon">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M15 7h6v6"/></svg>
            </span>
            <p class="text-xs text-neutral-600">Rata-rata Transaksi</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900">{{ $rp($rata) }}</p>
            <p class="mt-1 text-[11px] text-green-700">Rata-rata per pesanan</p>
        </div>
    </section>

    <section class="rounded-2xl border border-pinkline bg-white p-6">
        <form method="GET" class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex w-full max-w-md items-center gap-2 rounded-lg border border-pinkline bg-white px-3 py-2">
                <span class="text-neutral-500"><x-icon name="search" size="h-4 w-4" /></span>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama atau no. HP..."
                       class="w-full border-0 bg-transparent p-0 text-xs text-neutral-800 placeholder:text-neutral-500 focus:outline-none focus:ring-0">
            </div>

            <select name="kategori" onchange="this.form.submit()"
                    class="rounded-lg border border-pinkline bg-white py-2 pl-3 pr-8 text-xs text-neutral-800 focus:outline-none">
                <option value="">Semua Kategori</option>
                @foreach (['Lama', 'Baru'] as $c)
                    <option value="{{ $c }}" @selected($kat === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-xs">
                <thead>
                    <tr class="bg-pinksoft text-maroon">
                        <th class="rounded-l-xl px-4 py-3.5 font-bold">No</th>
                        <th class="px-4 py-3.5 font-bold">Nama Pelanggan</th>
                        <th class="px-4 py-3.5 font-bold">Kontak</th>
                        <th class="px-4 py-3.5 font-bold">Kategori</th>
                        <th class="px-4 py-3.5 font-bold">Total Pesanan</th>
                        <th class="px-4 py-3.5 font-bold">Total Belanja</th>
                        <th class="rounded-r-xl px-4 py-3.5 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pelanggans as $i => $p)
                        <tr class="border-b border-pinkline/70 text-neutral-800 hover:bg-pinksoft/40">
                            <td class="px-4 py-4">{{ $pelanggans->firstItem() + $i }}</td>
                            <td class="px-4 py-4 font-bold text-neutral-900">{{ $p->nama }}</td>
                            <td class="px-4 py-4">{{ $p->kontak }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-3 py-1 text-[10px] font-semibold {{ $badge[$p->kategori] ?? 'bg-neutral-100 text-neutral-700' }}">
                                    {{ $p->kategori }}
                                </span>
                            </td>
                            <td class="px-4 py-4">{{ $p->total_pesanan }} Pesanan</td>
                            <td class="px-4 py-4 font-bold text-neutral-900">{{ $rp($p->total_belanja) }}</td>
                            <td class="px-4 py-4 font-bold text-maroon">
                                <a href="{{ route('admin.pelanggan.show', $p) }}" class="hover:underline">Detail</a> |
                                <form method="POST" action="{{ route('admin.pelanggan.destroy', $p) }}" class="inline"
                                      onsubmit="return confirm('Hapus pelanggan {{ addslashes($p->nama) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-bold text-rose-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-neutral-500">Belum ada data pelanggan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[11px] text-neutral-600">
                Menampilkan {{ $pelanggans->count() }} dari {{ number_format($pelanggans->total(), 0, ',', '.') }} pelanggan
            </p>
            <div class="flex items-center gap-1.5 text-xs">
                <a href="{{ $pelanggans->previousPageUrl() ?? '#' }}" class="grid h-8 w-8 place-items-center rounded-lg border border-pinkline text-neutral-600 hover:bg-pinksoft">&lsaquo;</a>
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-maroon font-bold text-white">{{ $pelanggans->currentPage() }}</span>
                <a href="{{ $pelanggans->nextPageUrl() ?? '#' }}" class="grid h-8 w-8 place-items-center rounded-lg border border-pinkline text-neutral-600 hover:bg-pinksoft">&rsaquo;</a>
            </div>
        </div>
    </section>
</div>
@endsection
