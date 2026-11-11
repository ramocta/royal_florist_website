@extends('layouts.admin')

@section('title', 'Pesanan Baru')

@section('content')
@php
    $input = 'w-full rounded-xl border border-pinkline bg-white px-4 py-2.5 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon';

    // Baris item: ambil dari old() kalau validasi gagal, kalau tidak mulai dengan 1 baris kosong
    $baris = old('items', [['id_product' => '', 'jumlah' => 1]]);

    $metode = ['bayar_di_toko' => 'Tunai', 'QRIS' => 'QRIS', 'transfer_bank' => 'Transfer Bank', 'DP' => 'DP (Uang Muka)'];
    $bayar  = ['belum_dibayar' => 'Belum Dibayar', 'proses' => 'Proses', 'lunas' => 'Lunas'];
    $status = [
        'menunggu_pembayaran'   => 'Menunggu Pembayaran',
        'menunggu_dikonfirmasi' => 'Menunggu Dikonfirmasi',
        'dikonfirmasi'          => 'Dikonfirmasi',
        'diproses'              => 'Diproses',
        'dalam_pengiriman'      => 'Dalam Pengiriman',
        'siap_diambil'          => 'Siap Kirim',
        'selesai'               => 'Selesai',
        'dibatalkan'            => 'Batal',
    ];
@endphp

<div class="max-w-3xl">
    <a href="{{ route('admin.pesanan.index') }}" class="text-sm text-maroon hover:underline">&larr; Kembali ke daftar pesanan</a>
    <h1 class="mt-2 font-display text-3xl font-semibold text-maroon">Pesanan Baru</h1>

    <form method="POST" action="{{ route('admin.pesanan.store') }}" class="mt-6 space-y-5 rounded-2xl border border-pinkline p-6">
        @csrf

        {{-- ITEM PRODUK (baris bisa ditambah/dihapus) --}}
        <div>
            <label class="mb-1 block text-sm font-medium text-maroon">Item Produk</label>
            @error('items') <p class="mb-1 text-xs text-rose-600">{{ $message }}</p> @enderror

            <div id="daftar-item" class="space-y-2">
                @foreach ($baris as $i => $b)
                    <div class="item-baris">
                        <div class="flex items-center gap-2">
                            <select name="items[{{ $i }}][id_product]" class="{{ $input }}">
                                <option value="">-- Pilih produk --</option>
                                @foreach ($produk as $pr)
                                    <option value="{{ $pr->id_product }}" @selected(($b['id_product'] ?? '') == $pr->id_product)>
                                        {{ $pr->nama_produk }} - Rp {{ number_format($pr->harga, 0, ',', '.') }}@if ($pr->pakai_stok) (stok {{ $pr->stok }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" min="1" name="items[{{ $i }}][jumlah]" value="{{ $b['jumlah'] ?? 1 }}"
                                   class="{{ $input }} w-24" aria-label="Jumlah">
                            <button type="button" class="hapus-item shrink-0 rounded-lg bg-pinkcard px-3 py-2 text-xs font-semibold text-maroon hover:bg-maroon hover:text-white">Hapus</button>
                        </div>
                        @error("items.$i.id_product") <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        @error("items.$i.jumlah") <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            <button type="button" id="tambah-item" class="mt-2 text-sm font-medium text-maroon hover:underline">+ Tambah produk</button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="metode_pembayaran" class="mb-1 block text-sm font-medium text-maroon">Metode Pembayaran</label>
                <select id="metode_pembayaran" name="metode_pembayaran" class="{{ $input }}">
                    @foreach ($metode as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('metode_pembayaran', 'bayar_di_toko') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('metode_pembayaran') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Muncul hanya kalau metode pembayaran = DP --}}
            <div id="kolom-dp" class="{{ old('metode_pembayaran') === 'DP' ? '' : 'hidden' }}">
                <label for="jumlah_dp" class="mb-1 block text-sm font-medium text-maroon">Jumlah DP (Rp)</label>
                <input id="jumlah_dp" type="number" min="1" name="jumlah_dp" value="{{ old('jumlah_dp') }}"
                       placeholder="Contoh: 100000" class="{{ $input }}">
                @error('jumlah_dp') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status_pembayaran" class="mb-1 block text-sm font-medium text-maroon">Status Pembayaran</label>
                <select id="status_pembayaran" name="status_pembayaran" class="{{ $input }}">
                    @foreach ($bayar as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('status_pembayaran', 'belum_dibayar') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status_pembayaran') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status_pesanan" class="mb-1 block text-sm font-medium text-maroon">Status Pesanan</label>
                <select id="status_pesanan" name="status_pesanan" class="{{ $input }}">
                    @foreach ($status as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('status_pesanan', 'menunggu_dikonfirmasi') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status_pesanan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="jadwal_pengambilan" class="mb-1 block text-sm font-medium text-maroon">Jadwal Pengiriman</label>
                <input id="jadwal_pengambilan" type="datetime-local" name="jadwal_pengambilan"
                       value="{{ old('jadwal_pengambilan') }}" class="{{ $input }}">
                @error('jadwal_pengambilan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <p class="text-xs text-neutral-500">Total harga dihitung otomatis dari harga produk x jumlah. Stok berkurang otomatis hanya untuk produk yang dihitung per stok. Untuk pembayaran DP, status pembayaran otomatis Proses sampai dilunasi.</p>

        <div class="flex gap-2">
            <button class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-medium text-white hover:opacity-90">Simpan Pesanan</button>
            <a href="{{ route('admin.pesanan.index') }}" class="rounded-xl bg-pinkcard px-5 py-2.5 text-sm font-medium text-maroon hover:bg-pinkline">Batal</a>
        </div>
    </form>
</div>

{{-- JavaScript kecil hanya untuk tambah/hapus baris produk --}}
<script>
    (function () {
        // Metode DP: tampilkan kolom jumlah DP, status pembayaran otomatis proses
        const metode = document.getElementById('metode_pembayaran');
        const kolomDp = document.getElementById('kolom-dp');
        const statusBayar = document.getElementById('status_pembayaran');

        function aturDp() {
            const dp = metode.value === 'DP';
            kolomDp.classList.toggle('hidden', !dp);
            if (dp) statusBayar.value = 'proses';
            statusBayar.classList.toggle('pointer-events-none', dp);
            statusBayar.classList.toggle('opacity-60', dp);
            statusBayar.tabIndex = dp ? -1 : 0;
        }
        metode.addEventListener('change', aturDp);
        aturDp();

        const daftar = document.getElementById('daftar-item');

        // Beri nomor ulang items[0], items[1], ... supaya urut setelah tambah/hapus
        function nomori() {
            daftar.querySelectorAll('.item-baris').forEach((baris, i) => {
                baris.querySelectorAll('select, input').forEach(el => {
                    el.name = el.name.replace(/items\[\d+\]/, 'items[' + i + ']');
                });
            });
        }

        document.getElementById('tambah-item').addEventListener('click', () => {
            const baru = daftar.querySelector('.item-baris').cloneNode(true);
            baru.querySelector('select').value = '';
            baru.querySelector('input').value = 1;
            baru.querySelectorAll('p').forEach(p => p.remove()); // buang pesan error hasil salinan
            daftar.appendChild(baru);
            nomori();
        });

        daftar.addEventListener('click', (e) => {
            if (!e.target.classList.contains('hapus-item')) return;
            if (daftar.querySelectorAll('.item-baris').length > 1) {
                e.target.closest('.item-baris').remove();
                nomori();
            }
        });
    })();
</script>
@endsection