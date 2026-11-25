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
        @include('admin.pengaturan-tab', ['aktif' => 'landing'])
    </div>

    {{-- ================= BANNER SLIDER ================= --}}
    <section class="mt-5 max-w-4xl rounded-2xl bg-white p-6">
        <h2 class="font-display text-xl font-semibold text-maroon">Banner Slider</h2>
        <p class="text-xs text-neutral-500">
            Banner tampil bergantian (slide) di bagian atas beranda. Desain banner dibuat sendiri oleh pihak toko,
            lalu diunggah di sini. Tidak ada teks tambahan di atas gambar, jadi teks dan tombol cukup dimasukkan ke dalam desain.
        </p>
        <hr class="my-4 border-pinkline">

        {{-- Unggah (boleh pilih banyak file sekaligus) --}}
        <form method="POST" action="{{ route('admin.pengaturan.banner.tambah') }}" enctype="multipart/form-data" id="formBanner">
            @csrf
            <div class="rounded-xl border-2 border-dashed border-pinkline p-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="min-w-0 flex-1">
                        <p id="bannerNama" class="truncate text-sm font-semibold text-maroon">Belum ada gambar dipilih</p>
                        <p class="text-xs text-neutral-500">Rekomendasi 1600 x 600 px (rasio lebar) &bull; JPG/PNG/WEBP, maks. 2MB per gambar &bull; bisa pilih lebih dari satu</p>
                    </div>
                    <label class="cursor-pointer rounded-xl border border-pinkline bg-white px-5 py-2.5 text-sm font-medium text-maroon hover:bg-pinksoft">
                        Pilih Gambar
                        <input id="bannerInput" type="file" name="banner[]" multiple accept="image/png,image/jpeg,image/webp" class="sr-only">
                    </label>
                    <button id="bannerUnggah" type="submit" disabled
                            class="rounded-xl bg-maroon px-5 py-2.5 text-sm font-medium text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40">Unggah</button>
                </div>
            </div>
            @error('banner') <p class="{{ $err }}">{{ $message }}</p> @enderror
            @error('banner.*') <p class="{{ $err }}">{{ $message }}</p> @enderror
        </form>

        {{-- Daftar banner --}}
        <div class="mt-5 space-y-3">
            @forelse ($banners as $b)
                @php $url = $b->url; @endphp
                <div class="flex flex-wrap items-center gap-4 rounded-xl border border-pinkline p-3 {{ $b->is_active ? '' : 'opacity-60' }}">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-pinksoft text-xs font-semibold text-maroon">{{ $loop->iteration }}</span>

                    <div class="grid h-20 w-40 shrink-0 place-items-center overflow-hidden rounded-lg bg-pinkcard">
                        @if ($url)
                            <img src="{{ $url }}" alt="Banner {{ $loop->iteration }}" class="h-full w-full object-cover">
                        @else
                            <span class="px-2 text-center text-[11px] text-rose-600">File gambar tidak ditemukan</span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-maroon">{{ basename($b->gambar) }}</p>
                        <p class="text-xs {{ $b->is_active ? 'text-emerald-600' : 'text-neutral-500' }}">{{ $b->is_active ? 'Tampil di beranda' : 'Disembunyikan' }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('admin.pengaturan.banner.geser', $b) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="arah" value="naik">
                            <button title="Geser lebih awal" @disabled($loop->first)
                                    class="rounded-lg border border-pinkline px-3 py-2 text-sm text-maroon hover:bg-pinksoft disabled:cursor-not-allowed disabled:opacity-40">&uarr;</button>
                        </form>
                        <form method="POST" action="{{ route('admin.pengaturan.banner.geser', $b) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="arah" value="turun">
                            <button title="Geser lebih akhir" @disabled($loop->last)
                                    class="rounded-lg border border-pinkline px-3 py-2 text-sm text-maroon hover:bg-pinksoft disabled:cursor-not-allowed disabled:opacity-40">&darr;</button>
                        </form>
                        <form method="POST" action="{{ route('admin.pengaturan.banner.toggle', $b) }}">
                            @csrf @method('PATCH')
                            <button class="rounded-lg border border-pinkline px-3 py-2 text-xs font-medium text-maroon hover:bg-pinksoft">{{ $b->is_active ? 'Sembunyikan' : 'Tampilkan' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.pengaturan.banner.hapus', $b) }}" onsubmit="return confirm('Hapus banner ini?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="rounded-xl bg-pinksoft px-4 py-6 text-center text-sm text-neutral-500">
                    Belum ada banner. Unggah gambar di atas; selama kosong, beranda menampilkan banner bawaan.
                </p>
            @endforelse
        </div>
        <p class="mt-3 text-xs text-neutral-500">Urutan nomor = urutan slide. Kalau hanya ada satu banner aktif, banner tidak bergeser.</p>
    </section>

    {{-- ================= FOOTER ================= --}}
    <form method="POST" action="{{ route('admin.pengaturan.landing.update') }}" class="mt-5 max-w-4xl space-y-5">
        @csrf
        @method('PUT')

        <section class="rounded-2xl bg-white p-6">
            <h2 class="font-display text-xl font-semibold text-maroon">Footer &amp; Kontak</h2>
            <p class="text-xs text-neutral-500">Deskripsi singkat pada bagian bawah halaman.</p>
            <hr class="my-4 border-pinkline">

            <div>
                <label for="footer_deskripsi" class="{{ $label }}">Deskripsi Footer</label>
                <textarea id="footer_deskripsi" name="footer_deskripsi" rows="3" class="{{ $input }}">{{ old('footer_deskripsi', $s('footer_deskripsi')) }}</textarea>
                @error('footer_deskripsi') <p class="{{ $err }}">{{ $message }}</p> @enderror
            </div>

            <p class="mt-3 text-xs text-neutral-500">
                Alamat, email, WhatsApp, dan jam operasional di footer mengikuti isian di tab
                <a href="{{ route('admin.pengaturan.index') }}" class="font-medium text-maroon underline">Profil Toko</a>.
            </p>
        </section>

        <div class="flex items-center justify-between">
            {{-- Batal: muat ulang halaman, jadi perubahan yang belum disimpan dibuang --}}
            <a href="{{ route('admin.pengaturan.landing') }}"
               class="rounded-xl border border-pinkline bg-white px-6 py-2.5 text-sm font-medium text-maroon hover:bg-pinksoft">Batal</a>
            <button class="rounded-xl bg-maroon px-6 py-2.5 text-sm font-medium text-white hover:opacity-90">Simpan Perubahan</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    // Tampilkan nama file yang dipilih, dan aktifkan tombol Unggah hanya kalau sudah ada file
    document.getElementById('bannerInput').addEventListener('change', function () {
        var n = this.files.length;
        document.getElementById('bannerNama').textContent = n === 0 ? 'Belum ada gambar dipilih'
            : (n === 1 ? this.files[0].name : n + ' gambar dipilih');
        document.getElementById('bannerUnggah').disabled = n === 0;
    });
</script>
@endpush
