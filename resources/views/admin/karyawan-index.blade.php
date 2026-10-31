@extends('layouts.admin')

@section('title', 'Manajemen Staff')

@section('content')
    {{-- JUDUL + TOMBOL --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="text-maroon"><x-icon name="user" size="h-9 w-9" /></span>
            <div>
                <h1 class="font-display text-3xl font-semibold text-maroon">Manajemen Staff</h1>
                <p class="text-sm text-neutral-600">Kelola data, akses, dan status staff Royal Florist.</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.karyawan.create') }}"
               class="flex items-center gap-2 rounded-xl bg-maroon px-4 py-2.5 text-sm font-medium text-white hover:opacity-90">
                <x-icon name="plus" size="h-4 w-4" /> Tambah Staff
            </a>
            <a href="{{ route('admin.karyawan.export') }}"
               class="flex items-center gap-2 rounded-xl bg-pinkcard px-4 py-2.5 text-sm font-medium text-maroon hover:bg-pinkline">
                <x-icon name="export" size="h-4 w-4" /> Export Data
            </a>
        </div>
    </div>

    {{-- KARTU STATISTIK (widget Admin dihapus) --}}
    @php
        $kartu = [
            ['Total Staff',    'user',  $stat['total']],
            ['Staff Aktif',    'check', $stat['aktif']],
            ['Staff Nonaktif', 'x',     $stat['nonaktif']],
        ];
    @endphp
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach ($kartu as [$label, $ikon, $nilai])
            <div class="rounded-2xl bg-pinkcard p-5">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-maroon/80 text-white">
                        <x-icon :name="$ikon" size="h-5 w-5" />
                    </span>
                    <span class="text-sm font-semibold text-maroon">{{ $label }}</span>
                </div>
                <p class="mt-2 text-3xl font-bold text-maroon">{{ $nilai }}</p>
            </div>
        @endforeach
    </div>

    {{-- DAFTAR STAFF --}}
    <section class="mt-6 rounded-2xl border border-pinkline p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="flex items-center gap-2 font-display text-xl font-semibold text-maroon">
                <x-icon name="user" size="h-6 w-6" /> Daftar Staff
            </h2>

            {{-- Filter: method GET supaya hasilnya ikut tampil di URL dan tetap terbawa saat pindah halaman --}}
            <form method="GET" action="{{ route('admin.karyawan.index') }}" class="flex flex-wrap items-center gap-2 text-sm">
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">
                        <x-icon name="search" size="h-4 w-4" />
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email..."
                           class="w-56 rounded-lg border border-pinkline py-2 pl-9 pr-3 focus:border-maroon focus:outline-none">
                </div>
                <select name="role" onchange="this.form.submit()" class="rounded-lg border border-pinkline px-3 py-2">
                    <option value="">Semua Role</option>
                    <option value="karyawan" @selected(request('role') === 'karyawan')>Staff</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="rounded-lg border border-pinkline px-3 py-2">
                    <option value="">Semua Status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </form>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead>
                    <tr class="bg-pinkcard text-xs font-semibold text-maroon">
                        <th class="rounded-l-xl px-4 py-3">No</th>
                        <th class="px-4 py-3">Nama Staff</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">No. Telepon</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="rounded-r-xl px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $u)
                        <tr class="border-b border-pinkline/60 last:border-0">
                            <td class="px-4 py-3">{{ $staff->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-full bg-pinkcard text-sm font-semibold text-maroon">
                                        {{ strtoupper(substr($u->nama, 0, 1)) }}
                                    </span>
                                    {{ $u->nama }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-neutral-600">{{ $u->email }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-pinkcard px-3 py-1 text-xs font-medium text-maroon">
                                    Staff
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $u->nmr_telepon ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-xs font-medium
                                    {{ $u->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-rose-100 text-rose-500' }}">
                                    {{ ucfirst($u->status ?? 'aktif') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    {{-- Aktifkan / nonaktifkan --}}
                                    <form method="POST" action="{{ route('admin.karyawan.status', $u) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="grid h-8 w-8 place-items-center rounded-lg bg-pinkcard text-maroon hover:bg-maroon hover:text-white"
                                                title="{{ $u->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <x-icon name="eye" size="h-4 w-4" />
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.karyawan.edit', $u) }}" title="Edit"
                                       class="grid h-8 w-8 place-items-center rounded-lg bg-pinkcard text-maroon hover:bg-maroon hover:text-white">
                                        <x-icon name="edit" size="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.karyawan.destroy', $u) }}"
                                          onsubmit="return confirm('Hapus staff ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button title="Hapus"
                                                class="grid h-8 w-8 place-items-center rounded-lg bg-pinkcard text-maroon hover:bg-maroon hover:text-white">
                                            <x-icon name="trash" size="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-neutral-500">
                                Staff tidak ditemukan. Ubah kata kunci atau filter, atau tambah staff baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-neutral-600">
            <span>Menampilkan {{ $staff->count() }} dari {{ $staff->total() }} staff</span>
            {{ $staff->links() }}
        </div>
    </section>
@endsection
