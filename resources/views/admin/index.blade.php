{{--
  Simpan di: resources/views/admin/pelanggan/index.blade.php
  Route    : admin.pelanggan.index
--}}
@extends('layouts.admin')

@section('title', 'Pelanggan')

@section('content')
@php
    /*
      DATA CONTOH. Kalau controller sudah ada, kirim dari controller:
        return view('admin.pelanggan.index', compact('pelanggan', 'stat'));
      lalu hapus blok data contoh di bawah ini.
    */
    $stat = $stat ?? [
        'total'  => 1248,
        'lama'   => 142,
        'baru'   => 38,
        'rata'   => 485000,
    ];

    $pelanggan = $pelanggan ?? collect([
        (object) ['nama' => 'Siti Rahmawati', 'hp' => '0812-3456-7890', 'kategori' => 'Lama', 'pesanan' => 24, 'belanja' => 8750000],
        (object) ['nama' => 'Budi Santoso',   'hp' => '0857-1122-3344', 'kategori' => 'Baru', 'pesanan' => 8,  'belanja' => 2400000],
        (object) ['nama' => 'Amanda Putri',   'hp' => '0819-8877-6655', 'kategori' => 'Lama', 'pesanan' => 18, 'belanja' => 6120000],
        (object) ['nama' => 'Dewi Lestari',   'hp' => '0813-9900-1122', 'kategori' => 'Baru', 'pesanan' => 1,  'belanja' => 350000],
        (object) ['nama' => 'Rian Hidayat',   'hp' => '0878-5544-3322', 'kategori' => 'Baru', 'pesanan' => 5,  'belanja' => 1850000],
    ]);

    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

<div class="space-y-5">

    {{-- HEADER --}}
    <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-pinkline bg-white px-6 py-5">
        <div>
            <h1 class="text-xl font-bold text-maroon">Daftar Pelanggan</h1>
            <p class="mt-1 text-xs text-neutral-600">Kelola data pelanggan, riwayat transaksi, status keanggotaan, dan preferensi pesanan.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="#" class="flex items-center gap-2 rounded-lg border border-pinkline bg-white px-4 py-2 text-xs font-semibold text-maroon hover:bg-pinksoft">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
                Export Data
            </a>
            <a href="#" class="rounded-lg bg-maroon px-4 py-2 text-xs font-semibold text-white hover:opacity-90">
                + Pelanggan Baru
            </a>
        </div>
    </section>

    {{-- KARTU STATISTIK --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Total Pelanggan --}}
        <div class="relative rounded-2xl border border-pinkline border-l-4 border-l-maroon bg-white p-4">
            <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-pinksoft text-maroon">
                <x-icon name="users" size="h-4 w-4" />
            </span>
            <p class="text-xs text-neutral-600">Total Pelanggan</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900">{{ number_format($stat['total'], 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-600">Terdaftar di sistem</p>
        </div>

        {{-- Pelanggan Lama --}}
        <div class="relative rounded-2xl border border-pinkline border-l-4 border-l-amber-500 bg-white p-4">
            <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-pinksoft text-maroon">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linejoin="round" d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3z"/></svg>
            </span>
            <p class="text-xs text-neutral-600">Pelanggan Lama</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900">{{ number_format($stat['lama'], 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-amber-700">Transaksi &gt; 5 Juta/bln</p>
        </div>

        {{-- Pelanggan Baru --}}
        <div class="relative rounded-2xl border border-pinkline border-l-4 border-l-maroon bg-white p-4">
            <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-pinksoft text-maroon">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1M9 10a3 3 0 100-6 3 3 0 000 6zM19 8v6M16 11h6"/></svg>
            </span>
            <p class="text-xs text-neutral-600">Pelanggan Baru</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900">{{ number_format($stat['baru'], 0, ',', '.') }}</p>
            <p class="mt-1 text-[11px] text-neutral-600">Bulan ini</p>
        </div>

        {{-- Rata-rata Transaksi --}}
        <div class="relative rounded-2xl border border-pinkline border-l-4 border-l-green-600 bg-white p-4">
            <span class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-pinksoft text-maroon">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M15 7h6v6"/></svg>
            </span>
            <p class="text-xs text-neutral-600">Rata-rata Transaksi</p>
            <p class="mt-2 text-3xl font-bold text-neutral-900">{{ $rp($stat['rata']) }}</p>
            <p class="mt-1 text-[11px] text-green-700">&uarr; +12% dari bulan lalu</p>
        </div>
    </section>

    {{-- TABEL PELANGGAN --}}
    <section class="rounded-2xl border border-pinkline bg-white p-6">
        {{-- Pencarian + filter --}}
        <form method="GET" class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex w-full max-w-md items-center gap-2 rounded-lg border border-pinkline bg-white px-3 py-2 text-sm">
                <svg class="h-4 w-4 text-neutral-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau HP..."
                       class="w-full border-0 bg-transparent p-0 text-xs text-neutral-800 placeholder:text-neutral-500 focus:outline-none focus:ring-0">
            </div>

            <select name="kategori" onchange="this.form.submit()"
                    class="rounded-lg border border-pinkline bg-white py-2 pl-3 pr-8 text-xs text-neutral-800 focus:outline-none">
                <option value="">Semua Kategori</option>
                <option value="lama" @selected(request('kategori') === 'lama')>Lama</option>
                <option value="baru" @selected(request('kategori') === 'baru')>Baru</option>
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
                    @forelse ($pelanggan as $i => $p)
                        <tr class="border-b border-pinkline/70 text-neutral-800">
                            <td class="px-4 py-4">{{ $i + 1 }}</td>
                            <td class="px-4 py-4 font-bold text-neutral-900">{{ $p->nama }}</td>
                            <td class="px-4 py-4">{{ $p->hp }}</td>
                            <td class="px-4 py-4">
                                @if (strtolower($p->kategori) === 'lama')
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-[10px] font-semibold text-amber-800">Lama</span>
                                @else
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-[10px] font-semibold text-blue-800">Baru</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">{{ $p->pesanan }} Pesanan</td>
                            <td class="px-4 py-4 font-bold text-neutral-900">{{ $rp($p->belanja) }}</td>
                            <td class="px-4 py-4 font-bold text-maroon">
                                <a href="#" class="hover:underline">Detail</a>
                                <span class="mx-0.5">|</span>
                                <a href="#" class="hover:underline">Edit</a>
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

        {{-- Footer tabel + paginasi --}}
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[11px] text-neutral-600">
                Menampilkan {{ $pelanggan->count() }} dari {{ number_format($stat['total'], 0, ',', '.') }} pelanggan
            </p>

            {{-- Kalau pakai paginate() di controller, ganti blok ini dengan: {{ $pelanggan->links() }} --}}
            <div class="flex items-center gap-1.5 text-xs">
                <span class="grid h-8 w-8 place-items-center rounded-lg border border-pinkline text-neutral-500">&lsaquo;</span>
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-maroon font-bold text-white">1</span>
                <span class="grid h-8 w-8 place-items-center rounded-lg border border-pinkline text-neutral-500">&rsaquo;</span>
            </div>
        </div>
    </section>
</div>
@endsection
