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

        $gambarHero = \App\Models\Setting::gambar('hero_gambar', 'images/hero-default.png');
        $namaBerkas = basename($s('hero_gambar') ?? 'hero-default.png');
    @endphp

    <h1 class="font-display text-3xl font-semibold text-maroon">Pengaturan Sistem</h1>

    <div class="mt-4">
        @include('admin.pengaturan-tab', ['aktif' => 'landing'])
    </div>

    {{-- enctype multipart wajib supaya file gambar ikut terkirim --}}
    <form method="POST" action="{{ route('admin.pengaturan.landing.update') }}" enctype="multipart/form-data" class="mt-5 max-w-4xl space-y-5">
        @csrf
        @method('PUT')

        {{-- HERO --}}
        <section class="rounded-2xl bg-white p-6">
            <h2 class="font-display text-xl font-semibold text-maroon">Bagian Hero (Banner Utama)</h2>
            <p class="text-xs text-neutral-500">Konten utama yang pertama kali dilihat pengunjung di landing page.</p>
            <hr class="my-4 border-pinkline">

            <div class="space-y-4">
                <div>
                    <label for="hero_judul" class="{{ $label }}">Judul Utama</label>
                    <input id="hero_judul" type="text" name="hero_judul" value="{{ old('hero_judul', $s('hero_judul')) }}" class="{{ $input }}">
                    @error('hero_judul') <p class="{{ $err }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="hero_deskripsi" class="{{ $label }}">Deskripsi</label>
                    <textarea id="hero_deskripsi" name="hero_deskripsi" rows="3" class="{{ $input }}">{{ old('hero_deskripsi', $s('hero_deskripsi')) }}</textarea>
                    @error('hero_deskripsi') <p class="{{ $err }}">{{ $message }}</p> @enderror
                </div>

                <div class="sm:w-1/2">
                    <label for="hero_tombol_teks" class="{{ $label }}">Teks Tombol Utama</label>
                    <input id="hero_tombol_teks" type="text" name="hero_tombol_teks" value="{{ old('hero_tombol_teks', $s('hero_tombol_teks')) }}" class="{{ $input }}">
                    @error('hero_tombol_teks') <p class="{{ $err }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <span class="{{ $label }}">Gambar Hero</span>
                    <div class="rounded-xl border-2 border-dashed border-pinkline p-4">
                        <div class="flex flex-wrap items-center gap-4">
                            {{-- Kotak pink tetap tampil kalau gambar belum ada; gambar menutupinya kalau ada --}}
                            <div class="grid h-24 w-36 shrink-0 place-items-center overflow-hidden rounded-xl bg-pinkcard">
                                <img id="heroPreview" src="{{ $gambarHero }}" alt="Gambar hero" class="h-full w-full object-cover"
                                     onerror="this.style.display='none'">
                            </div>
                            <div class="min-w-0 flex-1">
                                <p id="heroNama" class="truncate text-sm font-semibold text-maroon">{{ $namaBerkas }}</p>
                                <p class="text-xs text-neutral-500">Rekomendasi 1200 x 800 px &bull; PNG/JPG, maks. 2MB</p>
                            </div>
                            <label class="cursor-pointer rounded-xl border border-pinkline bg-white px-5 py-2.5 text-sm font-medium text-maroon hover:bg-pinksoft">
                                Ganti Gambar
                                <input id="heroInput" type="file" name="hero_gambar" accept="image/png,image/jpeg" class="sr-only">
                            </label>
                        </div>
                    </div>
                    @error('hero_gambar') <p class="{{ $err }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- FOOTER --}}
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
    // Tampilkan pratinjau gambar yang baru dipilih, sebelum disimpan
    document.getElementById('heroInput').addEventListener('change', function () {
        var berkas = this.files[0];
        if (!berkas) return;
        var gambar = document.getElementById('heroPreview');
        gambar.style.display = 'block';
        gambar.src = URL.createObjectURL(berkas);
        document.getElementById('heroNama').textContent = berkas.name;
    });
</script>
@endpush
