@extends('layouts.admin')

@section('title', isset($order) ? 'Edit Pesanan' : 'Pesanan Baru')

@section('content')
@php
    $input = 'w-full rounded-xl border border-pinkline bg-white px-4 py-2.5 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon';
    $label = 'mb-1 block text-sm font-medium text-maroon';

    // Satu form untuk Pesanan Baru dan Edit Pesanan ($order terisi = mode edit)
    $edit = isset($order);
    $mode = old('mode_pelanggan', $edit ? ($order->id_user ? 'ada' : 'tanpa') : request('mode', 'ada'));
    $itemsLama = old('items', $edit
        ? $order->items->map(fn ($it) => ['id_product' => $it->id_product, 'jumlah' => $it->jumlah])->values()->all()
        : [['id_product' => '', 'jumlah' => 1]]);
    $nilai = fn ($kolom, $bawaan = null) => old($kolom, $edit ? ($order->{$kolom} ?? $bawaan) : $bawaan);

    $statusPesanan = [
        'menunggu_pembayaran' => 'Menunggu Pembayaran', 'menunggu_dikonfirmasi' => 'Menunggu Dikonfirmasi',
        'dikonfirmasi' => 'Dikonfirmasi', 'diproses' => 'Diproses', 'dalam_pengiriman' => 'Dalam Pengiriman',
        'siap_diambil' => 'Siap Diambil', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan',
    ];
    $metode = ['bayar_di_toko' => 'Tunai', 'QRIS' => 'QRIS', 'transfer_bank' => 'Transfer Bank', 'DP' => 'DP'];
    $bayar = ['belum_dibayar' => 'Belum Dibayar', 'proses' => 'Proses', 'lunas' => 'Lunas'];

    $dataBaru = [
        ['pelanggan_nama', 'Nama Pelanggan', 'text', 'Contoh: Siti Rahmawati'],
        ['pelanggan_email', 'Email (Gmail)', 'email', 'contoh@gmail.com'],
        ['pelanggan_telepon', 'No. HP', 'text', 'Contoh: 081234567890'],
        ['pelanggan_alamat', 'Alamat (opsional)', 'text', 'Alamat lengkap'],
        ['pelanggan_password', 'Password (opsional)', 'password', 'Kosongkan agar dibuat otomatis'],
    ];
@endphp

<div class="max-w-3xl">
    <a href="{{ route('admin.pesanan.index') }}" class="text-sm text-maroon hover:underline">&larr; Kembali ke daftar pesanan</a>
    <h1 class="mt-2 font-display text-3xl font-semibold text-maroon">
        {{ $edit ? 'Edit Pesanan #' . $order->no_invoice : 'Pesanan Baru' }}
    </h1>

    <form id="formPesanan" method="POST"
          action="{{ $edit ? route('admin.pesanan.update', $order->id_order) : route('admin.pesanan.store') }}"
          class="mt-6 space-y-5 rounded-2xl border border-pinkline p-6">
        @csrf
        @if ($edit) @method('PUT') @endif

        {{-- PELANGGAN --}}
        <div id="blokPelanggan" class="rounded-2xl border border-pinkline p-4">
            <p class="mb-2 text-sm font-medium text-maroon">Pelanggan</p>

            <div class="mb-3 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                @foreach (['ada' => 'Pilih pelanggan', 'baru' => 'Tambah pelanggan baru + pesanan', 'tanpa' => 'Tanpa data pelanggan'] as $nilaiMode => $teks)
                    <label class="flex items-center gap-2">
                        <input type="radio" name="mode_pelanggan" value="{{ $nilaiMode }}" @checked($mode === $nilaiMode) class="accent-maroon"> {{ $teks }}
                    </label>
                @endforeach
            </div>

            <div data-mode="ada">
                <select name="id_user" class="{{ $input }}">
                    <option value="">-- Pilih pelanggan --</option>
                    @foreach ($pelangganList as $p)
                        <option value="{{ $p->id_user }}" @selected((string) old('id_user', $edit ? $order->id_user : request('id_user')) === (string) $p->id_user)>
                            {{ $p->nama }} &middot; {{ $p->email }}{{ $p->nmr_telepon ? ' · ' . $p->nmr_telepon : '' }}
                        </option>
                    @endforeach
                </select>
                @error('id_user') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div data-mode="baru" class="grid gap-3 sm:grid-cols-2">
                @foreach ($dataBaru as [$nama, $teks, $tipe, $placeholder])
                    <div class="{{ $nama === 'pelanggan_alamat' ? 'sm:col-span-2' : '' }}">
                        <label for="{{ $nama }}" class="mb-1 block text-xs text-neutral-600">{{ $teks }}</label>
                        <input id="{{ $nama }}" type="{{ $tipe }}" name="{{ $nama }}" value="{{ $tipe === 'password' ? '' : old($nama) }}"
                               placeholder="{{ $placeholder }}" class="{{ $input }}">
                        @error($nama) <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
                <p class="text-[11px] text-neutral-500 sm:col-span-2">Pelanggan baru otomatis masuk ke daftar Pelanggan dengan kategori Baru.</p>
            </div>
        </div>

        {{-- PESANAN --}}
        <div id="blokPesanan" class="space-y-5">
            <div>
                <label class="{{ $label }}">Item Produk</label>
                <div id="daftarItem" class="space-y-2">
                    @foreach ($itemsLama as $i => $it)
                        <div class="baris-item flex items-start gap-2">
                            <select name="items[{{ $i }}][id_product]" class="{{ $input }}">
                                <option value="">-- Pilih produk --</option>
                                @foreach ($produk as $p)
                                    <option value="{{ $p->id_product }}" @selected((string) ($it['id_product'] ?? '') === (string) $p->id_product)>
                                        {{ $p->nama_produk }} — Rp {{ number_format($p->harga, 0, ',', '.') }}{{ $p->pakai_stok ? ' (stok ' . $p->stok . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" min="1" name="items[{{ $i }}][jumlah]" value="{{ $it['jumlah'] ?? 1 }}" class="{{ $input }}">
                            <button type="button" class="hapus-item rounded-xl bg-pinkcard px-4 py-2.5 text-sm font-medium text-maroon hover:bg-pinkline">Hapus</button>
                        </div>
                        @if ($errors->has("items.$i.id_product") || $errors->has("items.$i.jumlah"))
                            <p class="text-xs text-rose-600">{{ $errors->first("items.$i.id_product") ?: $errors->first("items.$i.jumlah") }}</p>
                        @endif
                    @endforeach
                </div>
                @error('items') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                <button type="button" id="tambahItem" class="mt-2 text-sm text-maroon hover:underline">+ Tambah produk</button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="metode_pembayaran" class="{{ $label }}">Metode Pembayaran</label>
                    <select id="metode_pembayaran" name="metode_pembayaran" class="{{ $input }}">
                        @foreach ($metode as $k => $v) <option value="{{ $k }}" @selected($nilai('metode_pembayaran', 'bayar_di_toko') === $k)>{{ $v }}</option> @endforeach
                    </select>
                    @error('metode_pembayaran') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status_pembayaran" class="{{ $label }}">Status Pembayaran</label>
                    <select id="status_pembayaran" name="status_pembayaran" class="{{ $input }}">
                        @foreach ($bayar as $k => $v) <option value="{{ $k }}" @selected($nilai('status_pembayaran', 'belum_dibayar') === $k)>{{ $v }}</option> @endforeach
                    </select>
                    @error('status_pembayaran') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div id="blokDp" class="sm:col-span-2">
                    <label for="jumlah_dp" class="{{ $label }}">Jumlah DP (Rp)</label>
                    <input id="jumlah_dp" type="number" min="1" name="jumlah_dp" value="{{ old('jumlah_dp', $edit && $order->jumlah_dp ? (int) $order->jumlah_dp : '') }}" class="{{ $input }}">
                    @error('jumlah_dp') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status_pesanan" class="{{ $label }}">Status Pesanan</label>
                    <select id="status_pesanan" name="status_pesanan" class="{{ $input }}">
                        @foreach ($statusPesanan as $k => $v) <option value="{{ $k }}" @selected($nilai('status_pesanan', 'menunggu_dikonfirmasi') === $k)>{{ $v }}</option> @endforeach
                    </select>
                    @error('status_pesanan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jadwal_pengambilan" class="{{ $label }}">Jadwal Pengiriman</label>
                    <input id="jadwal_pengambilan" type="datetime-local" name="jadwal_pengambilan" value="{{ old('jadwal_pengambilan', $edit ? $order->jadwal_pengambilan?->format('Y-m-d\\TH:i') : '') }}" class="{{ $input }}">
                    @error('jadwal_pengambilan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <p class="text-xs text-neutral-600">Total harga dihitung otomatis dari harga produk x jumlah.@if ($edit) Saat disimpan, stok dari item lama dikembalikan lalu dihitung ulang dari item yang baru.@endif Stok berkurang otomatis hanya untuk produk yang dihitung per stok. Untuk pembayaran DP, status pembayaran otomatis Proses sampai dilunasi.</p>
        </div>

        <div class="flex gap-2 pt-1">
            <button type="submit" id="tombolSimpan" class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-medium text-white hover:opacity-90">{{ $edit ? 'Simpan Perubahan' : 'Simpan Pesanan' }}</button>
            <a href="{{ route('admin.pesanan.index') }}" class="rounded-xl bg-pinkcard px-5 py-2.5 text-sm font-medium text-maroon hover:bg-pinkline">Batal</a>
        </div>
    </form>
</div>

<template id="templateItem">
    <div class="baris-item flex items-start gap-2">
        <select name="items[__i__][id_product]" class="{{ $input }}">
            <option value="">-- Pilih produk --</option>
            @foreach ($produk as $p)
                <option value="{{ $p->id_product }}">{{ $p->nama_produk }} — Rp {{ number_format($p->harga, 0, ',', '.') }}{{ $p->pakai_stok ? ' (stok ' . $p->stok . ')' : '' }}</option>
            @endforeach
        </select>
        <input type="number" min="1" name="items[__i__][jumlah]" value="1" class="{{ $input }}">
        <button type="button" class="hapus-item rounded-xl bg-pinkcard px-4 py-2.5 text-sm font-medium text-maroon hover:bg-pinkline">Hapus</button>
    </div>
</template>

<script>
    (function () {
        const form = document.getElementById('formPesanan');
        const blokPelanggan = document.getElementById('blokPelanggan');
        const daftar = document.getElementById('daftarItem');
        const templat = document.getElementById('templateItem');
        const metode = document.getElementById('metode_pembayaran');
        const blokDp = document.getElementById('blokDp');
        let nomor = daftar.querySelectorAll('.baris-item').length;

        function sinkron() {
            const m = blokPelanggan.querySelector('[name=mode_pelanggan]:checked').value;

            blokPelanggan.querySelectorAll('[data-mode]').forEach(function (el) {
                const tampil = el.dataset.mode === m;
                el.style.display = tampil ? '' : 'none';
                el.querySelectorAll('input, select').forEach(function (i) { i.disabled = !tampil; });
            });

            const dp = metode.value === 'DP';
            blokDp.style.display = dp ? '' : 'none';
            blokDp.querySelectorAll('input').forEach(function (i) { i.disabled = !dp; });
        }

        document.getElementById('tambahItem').addEventListener('click', function () {
            daftar.insertAdjacentHTML('beforeend', templat.innerHTML.replaceAll('__i__', nomor++));
        });

        form.addEventListener('click', function (e) {
            if (!e.target.classList.contains('hapus-item')) return;
            const baris = e.target.closest('.baris-item');
            if (daftar.querySelectorAll('.baris-item').length > 1) baris.remove();
        });

        form.addEventListener('change', sinkron);
        sinkron();
    })();
</script>
@endsection