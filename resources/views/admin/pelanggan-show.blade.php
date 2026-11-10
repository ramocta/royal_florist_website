@extends('layouts.admin')

@section('title', 'Detail Pelanggan')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $badge = [
        'Lama' => 'bg-amber-100 text-amber-800',
        'Baru' => 'bg-blue-100 text-blue-800',
    ];
@endphp

<div style="max-width: 900px;">
    <a href="{{ route('admin.pelanggan.index') }}" class="text-sm text-maroon hover:underline">&larr; Kembali ke daftar pelanggan</a>

    <section class="mt-3 rounded-2xl border border-pinkline bg-white" style="overflow: hidden;">
        <div class="bg-pinksoft" style="height: 80px;"></div>

        <div style="padding: 0 28px 28px;">
            <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; margin-top: -40px;">
                <div style="display: flex; align-items: flex-end; gap: 16px;">
                    <div class="bg-maroon text-white"
                         style="width: 80px; height: 80px; flex: none; border-radius: 9999px; border: 4px solid #fff;
                                display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 600;">
                        {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
                    </div>
                    <div style="padding-bottom: 4px;">
                        <h1 class="text-2xl font-bold text-neutral-900">{{ $pelanggan->nama }}</h1>
                        <span class="mt-1 inline-block rounded-full px-3 py-1 text-[11px] font-semibold {{ $badge[$kategori] }}">
                            Pelanggan {{ $kategori }}
                        </span>
                    </div>
                </div>

                <a href="{{ route('admin.pelanggan.edit', $pelanggan) }}"
                   class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-medium text-white hover:opacity-90">
                    Edit Pelanggan
                </a>
            </div>

            <div class="border-t border-pinkline text-sm" style="display: flex; flex-wrap: wrap; gap: 20px 64px; margin-top: 24px; padding-top: 20px;">
                <div>
                    <p class="text-xs text-neutral-500">Email</p>
                    <p class="mt-1 font-semibold text-neutral-900">{{ $pelanggan->email ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">Kontak</p>
                    <p class="mt-1 font-semibold text-neutral-900">{{ $pelanggan->kontak }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">Terdaftar Sejak</p>
                    <p class="mt-1 font-semibold text-neutral-900">{{ $terdaftar->locale('id')->translatedFormat('d F Y') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-top: 20px;">
        <div class="rounded-2xl border border-l-4 border-pinkline border-l-maroon bg-white p-5">
            <p class="text-xs text-neutral-600">Total Pesanan</p>
            <p class="mt-2 text-2xl font-bold text-neutral-900">{{ number_format($totalPesanan, 0, ',', '.') }} <span class="text-sm font-medium text-neutral-500">pesanan</span></p>
        </div>
        <div class="rounded-2xl border border-l-4 border-pinkline border-l-amber-500 bg-white p-5">
            <p class="text-xs text-neutral-600">Total Belanja</p>
            <p class="mt-2 text-2xl font-bold text-neutral-900">{{ $rp($totalBelanja) }}</p>
        </div>
    </section>
</div>
@endsection
