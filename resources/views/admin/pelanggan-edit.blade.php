@extends('layouts.admin')

@section('title', 'Edit Pelanggan')

@section('content')
@php
    $input = 'w-full rounded-xl border border-pinkline bg-white px-4 py-2.5 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon';
@endphp

<div class="max-w-xl">
    <a href="{{ route('admin.pelanggan.index') }}" class="text-sm text-maroon hover:underline">&larr; Kembali ke daftar pelanggan</a>
    <h1 class="mt-2 font-display text-3xl font-semibold text-maroon">Edit Pelanggan</h1>

    <form method="POST" action="{{ route('admin.pelanggan.update', $pelanggan) }}" class="mt-6 space-y-4 rounded-2xl border border-pinkline p-6">
        @csrf
        @method('PUT')

        <div>
            <label for="nama" class="mb-1 block text-sm font-medium text-maroon">Nama Pelanggan</label>
            <input id="nama" type="text" name="nama" value="{{ old('nama', $pelanggan->nama) }}" class="{{ $input }}">
            @error('nama') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="kontak" class="mb-1 block text-sm font-medium text-maroon">No. HP / Kontak</label>
            <input id="kontak" type="text" name="kontak" value="{{ old('kontak', $pelanggan->kontak) }}" class="{{ $input }}">
            @error('kontak') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="kategori" class="mb-1 block text-sm font-medium text-maroon">Kategori</label>
            <select id="kategori" name="kategori" class="{{ $input }}">
                @foreach (['Baru', 'Lama'] as $c)
                    <option value="{{ $c }}" @selected(old('kategori', $pelanggan->kategori) === $c)>{{ $c }}</option>
                @endforeach
            </select>
            @error('kategori') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-2 pt-2">
            <button type="submit" class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-medium text-white hover:opacity-90">Simpan Perubahan</button>
            <a href="{{ route('admin.pelanggan.show', $pelanggan) }}" class="rounded-xl bg-pinkcard px-5 py-2.5 text-sm font-medium text-maroon hover:bg-pinkline">Batal</a>
        </div>
    </form>
</div>
@endsection
