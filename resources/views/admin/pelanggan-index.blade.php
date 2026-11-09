@extends('layouts.admin')

@section('title', 'Pelanggan')

@section('content')
@php
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

    <section class="grid gap-4 sm:grid-cols-3">
        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-maroon bg-white p-4">
            <span class="absolute right-4 top-4 grid h-8 w-8 place-items-center rounded-full bg-pinksoft text-maroon">
                <x-icon name="users" size="h-4 w-4" />
            </span>
            <p class="text-xs text-neutral-600">Total Pelanggan</p>
            <p class="mt-1.5 text-2xl font-bold text-neutral-900">{{ number_format($total, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-600">Terdaftar di sistem</p>
        </div>

        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-amber-500 bg-white p-4">
            <span class="absolute right-4 top-4 grid h-8 w-8 place-items-center rounded-full bg-pinksoft text-maroon">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3z"/></svg>
            </span>
            <p class="text-xs text-neutral-600">Pelanggan Lama</p>
            <p class="mt-1.5 text-2xl font-bold text-neutral-900">{{ number_format($lama, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-amber-700">Terdaftar lebih dari 30 hari</p>
        </div>

        <div class="relative rounded-2xl border border-l-4 border-pinkline border-l-maroon bg-white p-4">
            <span class="absolute right-4 top-4 grid h-8 w-8 place-items-center rounded-full bg-pinksoft text-maroon">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1M9 10a3 3 0 100-6 3 3 0 000 6zM19 8v6M16 11h6"/></svg>
            </span>
            <p class="text-xs text-neutral-600">Pelanggan Baru</p>
            <p class="mt-1.5 text-2xl font-bold text-neutral-900">{{ number_format($baru, 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-600">Terdaftar dalam 30 hari terakhir</p>
        </div>

    </section>

    <section class="rounded-2xl border border-pinkline bg-white p-6">
        <form method="GET" class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex w-full max-w-md items-center gap-2 rounded-lg border border-pinkline bg-white px-3 py-2">
                <span class="text-neutral-500"><x-icon name="search" size="h-4 w-4" /></span>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, email, atau no. HP..."
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
            <table class="w-full min-w-[720px] table-fixed text-left text-xs">
                <colgroup>
                    <col style="width: 56px">
                    <col style="width: 24%">
                    <col style="width: 30%">
                    <col style="width: 17%">
                    <col style="width: 13%">
                    <col style="width: 96px">
                </colgroup>
                <thead>
                    <tr class="bg-pinksoft text-maroon">
                        <th class="rounded-l-xl px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">Nama Pelanggan</th>
                        <th class="px-4 py-3 font-bold">Email</th>
                        <th class="px-4 py-3 font-bold">Kontak</th>
                        <th class="px-4 py-3 font-bold">Kategori</th>
                        <th class="rounded-r-xl px-4 py-3 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pelanggans as $i => $p)
                        @php $kategori = $p->kategori; @endphp
                        <tr class="border-b border-pinkline/70 text-neutral-800 hover:bg-pinksoft/40">
                            <td class="px-4 py-3">{{ $pelanggans->firstItem() + $i }}</td>
                            <td class="truncate px-4 py-3 font-bold text-neutral-900">{{ $p->nama }}</td>
                            <td class="truncate px-4 py-3">{{ $p->email ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $p->kontak ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-[10px] font-semibold {{ $badge[$kategori] }}">{{ $kategori }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.pelanggan.show', $p) }}" title="Detail" aria-label="Detail {{ $p->nama }}"
                                       class="grid h-8 w-8 place-items-center rounded-lg bg-pinkcard text-maroon hover:bg-pinkline">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.pelanggan.destroy', $p) }}"
                                          onsubmit="return confirm('Hapus pelanggan {{ addslashes($p->nama) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" aria-label="Hapus {{ $p->nama }}"
                                                class="grid h-8 w-8 place-items-center rounded-lg bg-pinkcard text-maroon hover:bg-rose-100 hover:text-rose-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-neutral-500">Belum ada data pelanggan.</td>
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
