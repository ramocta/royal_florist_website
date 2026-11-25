@extends('layouts.admin')

@section('title', 'Pengaturan')

{{-- Sama seperti tab lain: latar pink dengan kartu putih --}}
@push('styles')
<style>
    .main-content { background: var(--pink-bg); }
</style>
@endpush

@section('content')
    @php
        $s = fn (string $kunci) => \App\Models\Setting::ambil($kunci);

        $input = 'w-full rounded-xl border border-pinkline bg-white px-4 py-2.5 text-sm focus:border-maroon focus:outline-none focus:ring-1 focus:ring-maroon';
        $label = 'mb-1 block text-xs font-semibold text-maroon';
        $err   = 'mt-1 text-xs text-rose-600';

        // Alamat QRIS yang sedang dipakai pelanggan. Kalau belum pernah diunggah, file-nya hilang, atau tidak ada gambar bawaan,
        // gambar tidak termuat dan kotak "Belum ada QRIS" di belakangnya yang terlihat.
        $gambarQris = \App\Models\Setting::gambar('qris_gambar', 'images/qris-default.png');
        $namaBerkas = $s('qris_gambar') ? basename($s('qris_gambar')) : 'Belum ada QRIS yang diunggah';
    @endphp

    <h1 class="font-display text-3xl font-semibold text-maroon">Pengaturan Sistem</h1>

    <div class="mt-4">
        @include('admin.pengaturan-tab', ['aktif' => 'qris'])
    </div>

    {{-- enctype multipart wajib supaya file gambar ikut terkirim --}}
    <form method="POST" action="{{ route('admin.pengaturan.qris.update') }}" enctype="multipart/form-data" class="mt-5 max-w-4xl space-y-5">
        @csrf
        @method('PUT')

        <section class="rounded-2xl bg-white p-6">
            <h2 class="font-display text-xl font-semibold text-maroon">QRIS Toko</h2>
            <p class="text-xs text-neutral-500">
                Kode QRIS ini yang ditampilkan ke pelanggan di halaman pembayaran. Unggah gambar baru kapan saja kalau QRIS toko berganti.
            </p>
            <hr class="my-4 border-pinkline">

            <div class="space-y-4">
                <div>
                    <span class="{{ $label }}">Gambar QRIS</span>
                    <div class="rounded-xl border-2 border-dashed border-pinkline p-4">
                        <div class="flex flex-wrap items-center gap-4">
                            {{-- Kotak persegi karena QR berbentuk persegi. Teks di belakang tertutup gambar kalau gambarnya ada --}}
                            <div class="relative grid h-40 w-40 shrink-0 place-items-center overflow-hidden rounded-xl bg-pinkcard">
                                <span class="px-3 text-center text-xs text-neutral-500">Belum ada QRIS</span>
                                <img id="qrisPreview" src="{{ $gambarQris }}" alt="QRIS toko"
                                     class="absolute inset-0 h-full w-full bg-white object-contain"
                                     onerror="this.style.display='none'">
                            </div>
                            <div class="min-w-0 flex-1">
                                <p id="qrisNama" class="truncate text-sm font-semibold text-maroon">{{ $namaBerkas }}</p>
                                <p class="text-xs text-neutral-500">PNG/JPG, maks. 2MB. Gunakan gambar yang tajam dan tidak terpotong supaya mudah di-scan.</p>
                            </div>
                            <label class="cursor-pointer rounded-xl border border-pinkline bg-white px-5 py-2.5 text-sm font-medium text-maroon hover:bg-pinksoft">
                                Ganti QRIS
                                <input id="qrisInput" type="file" name="qris_gambar" accept="image/png,image/jpeg" class="sr-only">
                            </label>
                        </div>
                    </div>
                    @error('qris_gambar') <p class="{{ $err }}">{{ $message }}</p> @enderror
                </div>

                <div class="sm:w-1/2">
                    <label for="qris_nmid" class="{{ $label }}">NMID (opsional)</label>
                    <input id="qris_nmid" type="text" name="qris_nmid" value="{{ old('qris_nmid', $s('qris_nmid')) }}"
                           maxlength="30" placeholder="Contoh: ID1234567890123" class="{{ $input }}">
                    <p class="mt-1 text-xs text-neutral-500">Nomor ID merchant yang tercetak di bawah QRIS. Kalau diisi, tampil di halaman pembayaran pelanggan.</p>
                    @error('qris_nmid') <p class="{{ $err }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <div class="flex items-center justify-between">
            {{-- Batal: muat ulang halaman, jadi perubahan yang belum disimpan dibuang --}}
            <a href="{{ route('admin.pengaturan.qris') }}"
               class="rounded-xl border border-pinkline bg-white px-6 py-2.5 text-sm font-medium text-maroon hover:bg-pinksoft">Batal</a>
            <button class="rounded-xl bg-maroon px-6 py-2.5 text-sm font-medium text-white hover:opacity-90">Simpan Perubahan</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    // Tampilkan pratinjau QRIS yang baru dipilih, sebelum disimpan
    document.getElementById('qrisInput').addEventListener('change', function () {
        var berkas = this.files[0];
        if (!berkas) return;
        var gambar = document.getElementById('qrisPreview');
        gambar.style.display = 'block';
        gambar.src = URL.createObjectURL(berkas);
        document.getElementById('qrisNama').textContent = berkas.name;
    });
</script>
@endpush
