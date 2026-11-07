{{--
  Tab di atas halaman Pengaturan. Dipakai bersama oleh Profil Toko dan Manajemen Landing Page.
  Variabel: $aktif = 'profil' atau 'landing'
  Tab yang route-nya belum dibuat tampil redup dan tidak bisa diklik, jadi tidak error.
--}}
@php
    $tabs = [
        'profil'  => ['Profil Toko', 'admin.pengaturan.index'],
        'landing' => ['Manajemen Landing Page', 'admin.pengaturan.landing'],
    ];
@endphp
<div class="flex flex-wrap items-center gap-3">
    @foreach ($tabs as $kode => [$label, $rute])
        @if (Route::has($rute))
            <a href="{{ route($rute) }}"
               class="rounded-xl px-5 py-2.5 text-sm font-medium transition
                      {{ $aktif === $kode ? 'bg-maroon text-white' : 'border border-pinkline bg-white text-maroon hover:bg-pinksoft' }}">
                {{ $label }}
            </a>
        @else
            <span class="cursor-not-allowed rounded-xl border border-pinkline bg-white px-5 py-2.5 text-sm font-medium text-maroon opacity-50"
                  title="Belum tersedia">{{ $label }}</span>
        @endif
    @endforeach
</div>
