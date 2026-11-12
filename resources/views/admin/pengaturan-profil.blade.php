@extends('layouts.admin')

@section('title', 'Pengaturan')

{{-- Di desain Figma halaman Pengaturan berlatar pink dengan kartu putih --}}
@push('styles')
<style>
    .main-content { background: var(--pink-bg); }
</style>
@endpush

@section('content')
    @php
        // Baca nilai tersimpan; kalau belum pernah disimpan, otomatis pakai nilai bawaan dari model Setting
        $s = fn (string $kunci) => \App\Models\Setting::ambil($kunci);

        $input = 'w-full rounded-xl border border-pinkline bg-white px-4 py-2.5 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon';
        $label = 'mb-1 block text-xs font-semibold text-maroon';
        $err   = 'mt-1 text-xs text-rose-600';
    @endphp

    <h1 class="font-display text-3xl font-semibold text-maroon">Pengaturan Sistem</h1>

    <div class="mt-4">
        @include('admin.pengaturan-tab', ['aktif' => 'profil'])
    </div>

    <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="mt-5">
        @csrf
        @method('PUT')

        <div class="grid items-start gap-5 lg:grid-cols-[1fr_300px]">

            {{-- INFORMASI TOKO --}}
            <section class="rounded-2xl bg-white p-6">
                <h2 class="font-display text-xl font-semibold text-maroon">Informasi Toko</h2>
                <p class="text-xs text-neutral-500">Detail informasi toko yang akan ditampilkan pada website dan landing page.</p>
                <hr class="my-4 border-pinkline">

                <div class="space-y-4">
                    <div>
                        <label for="toko_nama" class="{{ $label }}">Nama Toko</label>
                        <input id="toko_nama" type="text" name="toko_nama" value="{{ old('toko_nama', $s('toko_nama')) }}" class="{{ $input }}">
                        @error('toko_nama') <p class="{{ $err }}">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="toko_email" class="{{ $label }}">Email Toko</label>
                            <input id="toko_email" type="email" name="toko_email" value="{{ old('toko_email', $s('toko_email')) }}" class="{{ $input }}">
                            @error('toko_email') <p class="{{ $err }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="toko_whatsapp" class="{{ $label }}">Nomor WhatsApp / HP</label>
                            <input id="toko_whatsapp" type="text" name="toko_whatsapp" value="{{ old('toko_whatsapp', $s('toko_whatsapp')) }}" class="{{ $input }}">
                            @error('toko_whatsapp') <p class="{{ $err }}">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="toko_alamat" class="{{ $label }}">Alamat Lengkap Toko</label>
                        <textarea id="toko_alamat" name="toko_alamat" rows="3" class="{{ $input }}">{{ old('toko_alamat', $s('toko_alamat')) }}</textarea>
                        @error('toko_alamat') <p class="{{ $err }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <span class="{{ $label }}">Jam Operasional Toko</span>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <input type="text" name="toko_jam_senin_sabtu" value="{{ old('toko_jam_senin_sabtu', $s('toko_jam_senin_sabtu')) }}"
                                       class="{{ $input }}" aria-label="Jam operasional Senin sampai Sabtu">
                                @error('toko_jam_senin_sabtu') <p class="{{ $err }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <input type="text" name="toko_jam_minggu" value="{{ old('toko_jam_minggu', $s('toko_jam_minggu')) }}"
                                       class="{{ $input }}" aria-label="Jam operasional Minggu">
                                @error('toko_jam_minggu') <p class="{{ $err }}">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-6 border-pinkline">

                {{-- Rekening tujuan Transfer Bank. Tampil di halaman pembayaran pelanggan yang memilih Transfer Bank. --}}
                <h2 class="font-display text-xl font-semibold text-maroon">Rekening Transfer Bank</h2>
                <p class="text-xs text-neutral-500">Pelanggan yang memilih Transfer Bank di checkout akan melihat rekening ini. Kosongkan nama bank atau nomor rekening untuk menyembunyikan pilihan Transfer Bank.</p>

                <div class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="bank_nama" class="{{ $label }}">Nama Bank</label>
                            <input id="bank_nama" type="text" name="bank_nama" value="{{ old('bank_nama', $s('bank_nama')) }}" maxlength="50" placeholder="Contoh: BCA" class="{{ $input }}">
                            @error('bank_nama') <p class="{{ $err }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="bank_rekening" class="{{ $label }}">Nomor Rekening</label>
                            <input id="bank_rekening" type="text" name="bank_rekening" value="{{ old('bank_rekening', $s('bank_rekening')) }}" maxlength="30" inputmode="numeric" class="{{ $input }}">
                            @error('bank_rekening') <p class="{{ $err }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="bank_atas_nama" class="{{ $label }}">Atas Nama</label>
                        <input id="bank_atas_nama" type="text" name="bank_atas_nama" value="{{ old('bank_atas_nama', $s('bank_atas_nama')) }}" maxlength="100" class="{{ $input }}">
                        @error('bank_atas_nama') <p class="{{ $err }}">{{ $message }}</p> @enderror
                    </div>
                </div>

                <hr class="my-6 border-pinkline">
                <div class="flex items-center justify-between">
                    {{-- Batal: muat ulang halaman, jadi perubahan yang belum disimpan dibuang --}}
                    <a href="{{ route('admin.pengaturan.index') }}"
                       class="rounded-xl border border-pinkline bg-white px-6 py-2.5 text-sm font-medium text-maroon hover:bg-pinksoft">Batal</a>
                    <button class="rounded-xl bg-maroon px-6 py-2.5 text-sm font-medium text-white hover:opacity-90">Simpan Perubahan</button>
                </div>
            </section>

            {{-- STATUS TOKO --}}
            <aside class="rounded-2xl bg-white p-6">
                <h2 class="font-display text-xl font-semibold text-maroon">Status Toko</h2>
                <p class="text-xs text-neutral-500">Kontrol visibilitas penerimaan pesanan baru.</p>

                <div class="mt-4 flex items-center justify-between rounded-xl bg-pinksoft px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-maroon">Status Toko Buka</p>
                        <p class="text-xs text-neutral-500">Menerima pesanan masuk</p>
                    </div>

                    {{-- Toggle. Input tersembunyi '0' dikirim lebih dulu; kalau dicentang, '1' menimpanya --}}
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="hidden" name="toko_buka" value="0">
                        <input type="checkbox" name="toko_buka" value="1" class="peer sr-only"
                               @checked(old('toko_buka', $s('toko_buka')) == '1')>
                        <span class="h-6 w-11 rounded-full bg-neutral-300 transition
                                     after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition
                                     peer-checked:bg-green-600 peer-checked:after:translate-x-5"></span>
                    </label>
                </div>
                @error('toko_buka') <p class="{{ $err }}">{{ $message }}</p> @enderror
            </aside>
        </div>
    </form>
@endsection
