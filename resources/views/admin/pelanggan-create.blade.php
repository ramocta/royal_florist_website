@extends('layouts.admin')

@section('title', 'Pelanggan Baru')

@section('content')
@php
    $input = 'w-full rounded-xl border border-pinkline bg-white px-4 py-2.5 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon';
    $fields = [
        ['nama', 'Nama Pelanggan', 'text', 'Contoh: Siti Rahmawati'],
        ['email', 'Email (Gmail)', 'email', 'contoh@gmail.com'],
        ['kontak', 'No. HP / Kontak', 'text', 'Contoh: 081234567890'],
    ];
@endphp

<div class="max-w-xl">
    <a href="{{ route('admin.pelanggan.index') }}" class="text-sm text-maroon hover:underline">&larr; Kembali ke daftar pelanggan</a>
    <h1 class="mt-2 font-display text-3xl font-semibold text-maroon">Pelanggan Baru</h1>
    <p class="mt-1 text-sm text-neutral-600">Pelanggan yang mendaftar di website otomatis masuk ke daftar. Gunakan form ini untuk menambah pelanggan secara manual.</p>

    <form method="POST" action="{{ route('admin.pelanggan.store') }}" class="mt-6 space-y-4 rounded-2xl border border-pinkline p-6">
        @csrf

        @foreach ($fields as [$name, $label, $type, $placeholder])
            <div>
                <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-maroon">{{ $label }}</label>
                <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}"
                       value="{{ old($name) }}" placeholder="{{ $placeholder }}" class="{{ $input }}">
                @error($name) <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        @endforeach

        <div class="flex gap-2 pt-2">
            <button type="submit" class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-medium text-white hover:opacity-90">Simpan</button>
            <a href="{{ route('admin.pelanggan.index') }}" class="rounded-xl bg-pinkcard px-5 py-2.5 text-sm font-medium text-maroon hover:bg-pinkline">Batal</a>
        </div>
    </form>
</div>
@endsection
