@php
    // Dibaca dari Pengaturan admin; kalau belum pernah disimpan, otomatis pakai nilai bawaan di model Setting
    $s = fn (string $kunci) => \App\Models\Setting::ambil($kunci);

    // Poin keunggulan: tulisan tetap (tidak diedit admin). Ubah teksnya di sini.
    $keunggulan = [
        ['bunga', 'Bunga 100% Segar',          'Dijamin langsung dari vendor terbaik'],
        ['kilat', 'Pengiriman Kilat Hari Ini', 'Area Surabaya & sekitarnya'],
        ['hadiah', 'Gratis Kartu Ucapan',      'Bisa ditulis disertai pesan custom'],
    ];

    // Ikon kecil (garis, warna mengikuti teks sekitarnya)
    $ikon = [
        'bunga'  => '<circle cx="12" cy="7" r="3.2"/><circle cx="7" cy="12" r="3.2"/><circle cx="17" cy="12" r="3.2"/><circle cx="12" cy="17" r="3.2"/><circle cx="12" cy="12" r="1.6"/>',
        'kilat'  => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
        'hadiah' => '<rect x="3" y="8" width="18" height="13" rx="1.5"/><path d="M12 8v13M3 12h18M12 8c-2-4-6-3-5 0 .5 1.5 3 1 5 0zM12 8c2-4 6-3 5 0-.5 1.5-3 1-5 0z"/>',
        'pin'    => '<path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'telp'   => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
        'surel'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'jam'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'pensil' => '<path d="M4 20h4L19 9l-4-4L4 16z"/>',
    ];
    $svg = fn (string $k, int $u = 18) => '<svg width="'.$u.'" height="'.$u.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$ikon[$k].'</svg>';

    // Nomor WhatsApp -> alamat wa.me (hanya angka, awalan 0 diganti 62)
    $wa = preg_replace('/\D/', '', $s('toko_whatsapp') ?? '');
    if (str_starts_with($wa, '0')) { $wa = '62' . substr($wa, 1); }

    $placeholder = '<svg width="46" height="46" viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="9" r="6" fill="#f4b6c6"/><circle cx="9" cy="17" r="6" fill="#e9a4b8"/><circle cx="23" cy="17" r="6" fill="#f4b6c6"/><circle cx="16" cy="23" r="6" fill="#e9a4b8"/><circle cx="16" cy="16" r="4" fill="#fff"/></svg>';
@endphp
@extends('layouts.pelanggan')

@section('title', $s('toko_nama') . ' - Beranda')

@push('head')
<style>
    /* Pita yang muncul kalau admin mematikan "Status Toko Buka" di Pengaturan */
    .closed-bar { margin-bottom:18px; padding:10px 16px; border-radius:14px; background:#fff3cd; color:#7a5a00; text-align:center; font-size:13px; font-weight:500; }

    .sec { margin-top:52px; }
    .sec-head { text-align:center; margin-bottom:26px; }
    .sec-head h2, .row-head h2 { font-family:'Playfair Display', serif; font-size:30px; color:var(--maroon); margin:0; }
    .sec-head p { margin:6px 0 0; font-size:14px; color:var(--muted); }
    .row-head { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; margin-bottom:20px; }
    .row-head a { font-size:13px; font-weight:600; color:var(--maroon); text-decoration:none; white-space:nowrap; }
    .row-head a:hover { text-decoration:underline; }

    .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 26px; border-radius:99px; border:1.5px solid transparent; font-size:14px; font-weight:600; text-decoration:none; cursor:pointer; font-family:inherit; }
    .btn.white { background:#fff; color:var(--maroon); }
    .btn.white:hover { background:var(--blush); }
    .btn.ghost { border-color:rgba(255,255,255,.6); color:#fff; background:transparent; }
    .btn.ghost:hover { background:rgba(255,255,255,.12); }
    .btn.solid { background:var(--maroon); color:#fff; width:100%; padding:12px 16px; border-radius:14px; font-size:13.5px; }
    .btn.solid:hover { background:var(--maroon-dk); }
    .btn.solid.kecil { padding:10px 12px; font-size:12.5px; }

    /* Hero */
    .hero { display:grid; grid-template-columns:1.2fr 1fr; align-items:center; gap:28px; padding:50px 54px; border-radius:26px; color:#fff; background:linear-gradient(135deg, #6a1730, #8a2543); box-shadow:0 18px 40px rgba(83,26,53,.22); overflow:hidden; }
    .hero-badge { margin:0; font-size:11px; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:#f4b6c6; }
    .hero h1 { margin:14px 0 0; font-family:'Playfair Display', serif; font-size:40px; line-height:1.2; }
    .hero .desc { margin:14px 0 0; max-width:460px; font-size:14.5px; line-height:1.65; color:rgba(255,255,255,.86); }
    .hero-btns { display:flex; flex-wrap:wrap; gap:12px; margin-top:28px; }
    .hero-img { display:grid; place-items:center; min-height:160px; }
    .hero-img img { max-width:100%; max-height:330px; object-fit:contain; border-radius:18px; }

    /* Keunggulan */
    .fitur-row { display:grid; grid-template-columns:repeat(3, 1fr); gap:14px; margin-top:22px; }
    .fitur { display:flex; align-items:center; gap:12px; padding:14px 16px; background:#fff; border-radius:16px; box-shadow:0 6px 18px rgba(83,26,53,.06); }
    .fitur .ic { flex-shrink:0; width:40px; height:40px; border-radius:50%; background:var(--blush-dk); color:var(--maroon); display:grid; place-items:center; }
    .fitur b { display:block; font-size:13.5px; color:var(--maroon); }
    .fitur span { font-size:12px; color:var(--muted); }

    /* Kartu kategori & produk */
    .kat-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; }
    .pc-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; }
    .kat, .pc { display:flex; flex-direction:column; background:#fff; border-radius:22px; padding:14px; box-shadow:0 10px 26px rgba(83,26,53,.08); }
    .pic { display:grid; place-items:center; overflow:hidden; border-radius:16px; background:var(--blush-dk); }
    .kat .pic { aspect-ratio:4 / 3; }
    .pc .pic { aspect-ratio:1 / 1; }
    .pic img { width:100%; height:100%; object-fit:cover; }
    .kat h3 { margin:16px 4px 0; font-family:'Playfair Display', serif; font-size:19px; color:var(--maroon); }
    .kat p { flex:1; margin:6px 4px 16px; font-size:12.5px; line-height:1.5; color:var(--muted); }
    .pc h3 { margin:12px 4px 0; font-size:14px; font-weight:600; color:var(--maroon); }
    .pc .spec { margin:2px 4px 0; font-size:11.5px; color:var(--muted); }
    .pc .harga { flex:1; margin:8px 4px 12px; font-size:14.5px; font-weight:700; color:var(--maroon); }

    /* Fitur spesial */
    .spesial { padding:44px 52px; border-radius:26px; color:#fff; background:linear-gradient(135deg, #6a1730, #8a2543); box-shadow:0 18px 40px rgba(83,26,53,.22); }
    .spesial h2 { margin:10px 0 0; font-family:'Playfair Display', serif; font-size:30px; line-height:1.25; }
    .spesial .desc { margin:10px 0 0; max-width:520px; font-size:14px; line-height:1.6; color:rgba(255,255,255,.86); }
    .spesial .btn { margin-top:22px; }

    /* Footer lengkap khusus beranda */
    .lf { margin-top:56px; }
    .lf-in { max-width:1240px; margin:0 auto; padding:44px 24px 28px; display:grid; grid-template-columns:1.4fr 1fr 1.5fr; gap:36px; }
    .lf h4 { margin:0 0 14px; font-size:14px; color:#fff; }
    .lf p { margin:12px 0 0; max-width:300px; font-size:13px; line-height:1.65; color:rgba(255,255,255,.78); }
    .lf ul { list-style:none; margin:0; padding:0; display:grid; gap:10px; font-size:13px; color:rgba(255,255,255,.82); }
    .lf li { display:flex; gap:10px; align-items:flex-start; }
    .lf li svg { flex-shrink:0; margin-top:2px; }
    .lf a { text-decoration:none; }
    .lf a:hover { color:#fff; text-decoration:underline; }
    .lf-copy { border-top:1px solid rgba(255,255,255,.16); padding:16px 24px; text-align:center; font-size:12px; color:rgba(255,255,255,.7); }

    @media (max-width:860px) {
        .sec { margin-top:40px; }
        .hero { grid-template-columns:1fr; padding:30px 22px; gap:18px; }
        .hero h1 { font-size:28px; }
        .hero-img { order:-1; min-height:0; }
        .hero-img img { max-height:210px; }
        .fitur-row { grid-template-columns:1fr; }
        .kat-grid { grid-template-columns:1fr; }
        .pc-grid { grid-template-columns:repeat(2, 1fr); gap:12px; }
        .sec-head h2, .row-head h2 { font-size:24px; }
        .spesial { padding:30px 22px; }
        .spesial h2 { font-size:24px; }
        .lf-in { grid-template-columns:1fr; gap:26px; padding:34px 20px 20px; }
    }
</style>
@endpush

@section('content')

    @if ($s('toko_buka') === '0')
        <div class="closed-bar">Toko sedang tutup. Pesanan baru belum dapat diterima saat ini.</div>
    @endif

    {{-- ===== HERO: judul, deskripsi, gambar, dan teks tombol diatur admin di Pengaturan > Manajemen Landing Page ===== --}}
    <section class="hero">
        <div>
            <p class="hero-badge">Premium Florist &amp; Hantaran Pernikahan</p>
            <h1>{{ $s('hero_judul') }}</h1>
            <p class="desc">{{ $s('hero_deskripsi') }}</p>
            <div class="hero-btns">
                <a class="btn white" href="{{ route('pelanggan.katalog') }}">{{ $s('hero_tombol_teks') }}</a>
                <a class="btn ghost" href="{{ route('custom-buket.index') }}">Custom Buket &rsaquo;</a>
            </div>
        </div>
        <div class="hero-img">
            {{-- Kalau admin belum mengunggah gambar, dipakai public/images/hero-default.png --}}
            <img src="{{ \App\Models\Setting::gambar('hero_gambar', 'images/hero-default.png') }}" alt="Banner {{ $s('toko_nama') }}" onerror="this.style.display='none'">
        </div>
    </section>

    {{-- ===== KEUNGGULAN ===== --}}
    <section class="fitur-row">
        @foreach ($keunggulan as [$ik, $judul, $teks])
            <div class="fitur">
                <span class="ic">{!! $svg($ik, 20) !!}</span>
                <div><b>{{ $judul }}</b><span>{{ $teks }}</span></div>
            </div>
        @endforeach
    </section>

    {{-- ===== KATEGORI PRODUK UTAMA (dari tabel categories) ===== --}}
    @if (count($kategori))
        <section class="sec">
            <div class="sec-head">
                <h2>Kategori Produk Utama</h2>
                <p>Temukan rangkaian bunga dan seserahan khusus untuk momen spesial Anda</p>
            </div>
            <div class="kat-grid">
                @foreach ($kategori as $k)
                    <div class="kat">
                        <div class="pic">
                            @if ($k['gambar'])
                                <img src="{{ $k['gambar'] }}" alt="{{ $k['judul'] }}" loading="lazy" onerror="this.style.display='none'">
                            @else
                                {!! $placeholder !!}
                            @endif
                        </div>
                        <h3>{{ $k['judul'] }}</h3>
                        <p>{{ $k['deskripsi'] }}</p>
                        <a class="btn solid" href="{{ route('pelanggan.katalog') }}">{{ $k['tombol'] }}</a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== PRODUK TERLARIS MINGGU INI (dihitung dari order_items 7 hari terakhir) ===== --}}
    @if (count($terlaris))
        <section class="sec">
            <div class="row-head">
                <h2>Produk Terlaris Minggu Ini</h2>
                <a href="{{ route('pelanggan.katalog') }}">Lihat Semua Katalog &rarr;</a>
            </div>
            <div class="pc-grid">
                @foreach ($terlaris as $p)
                    <div class="pc">
                        <div class="pic">
                            @if ($p['gambar'])
                                <img src="{{ $p['gambar'] }}" alt="{{ $p['nama'] }}" loading="lazy" onerror="this.style.display='none'">
                            @else
                                {!! $placeholder !!}
                            @endif
                        </div>
                        <h3>{{ $p['nama'] }}</h3>
                        <p class="spec">{{ $p['spesifikasi'] }}</p>
                        <p class="harga">Rp {{ number_format($p['harga'], 0, ',', '.') }}</p>
                        <button type="button" class="btn solid kecil" data-add="{{ $p['id'] }}" data-max="{{ $p['maks'] }}">+ Tambah Keranjang</button>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== FITUR SPESIAL: Custom Buket ===== --}}
    <section class="sec spesial">
        <p class="hero-badge">Fitur Spesial</p>
        <h2>Buat Buket Bunga Sesuai Keinginan Anda!</h2>
        <p class="desc">Pilih sendiri jenis bunga, warna, pembungkus (wrapping), aksesoris plus, hingga isi kartu ucapan.</p>
        <a class="btn white" href="{{ route('custom-buket.index') }}">Mulai Custom Buket {!! $svg('pensil', 16) !!}</a>
    </section>

    {{-- Bagian testimoni ("Apa Kata Pelanggan Kami") sengaja tidak ada: dihapus dari landing page --}}
@endsection

{{--
  Footer lengkap sesuai Figma (deskripsi, navigasi, kontak dari Pengaturan).
  Layout pelanggan yang sekarang belum punya @yield('footer'), jadi blok ini belum tampil dan footer ringkas
  bawaan layout tetap dipakai. Blok ini otomatis aktif kalau suatu saat layout menambahkan @yield('footer').
--}}
@section('footer')
    <footer class="foot lf">
        <div class="lf-in">
            <div>
                <a class="brand" href="{{ url('/') }}">
                    <svg width="30" height="30" viewBox="0 0 32 32" fill="none" aria-hidden="true"><circle cx="16" cy="9" r="6" fill="#f4b6c6"/><circle cx="9" cy="17" r="6" fill="#e9a4b8"/><circle cx="23" cy="17" r="6" fill="#f4b6c6"/><circle cx="16" cy="23" r="6" fill="#e9a4b8"/><circle cx="16" cy="16" r="4" fill="#fff"/></svg>
                    {{ $s('toko_nama') }}
                </a>
                <p>{{ $s('footer_deskripsi') }}</p>
            </div>

            <div>
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="{{ url('/') }}">Beranda</a></li>
                    <li><a href="{{ route('pelanggan.katalog') }}">Katalog Produk</a></li>
                    <li><a href="{{ route('custom-buket.index') }}">Custom Buket</a></li>
                    <li><a href="{{ route('pelanggan.riwayat') }}">Riwayat Pesanan</a></li>
                </ul>
            </div>

            <div>
                <h4>Kontak &amp; Lokasi</h4>
                <ul>
                    <li>{!! $svg('pin', 16) !!}<span>{{ $s('toko_alamat') }}</span></li>
                    <li>{!! $svg('telp', 16) !!}<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">WhatsApp: {{ $s('toko_whatsapp') }}</a></li>
                    <li>{!! $svg('surel', 16) !!}<a href="mailto:{{ $s('toko_email') }}">{{ $s('toko_email') }}</a></li>
                    <li>{!! $svg('jam', 16) !!}<span>{{ $s('toko_jam_senin_sabtu') }}<br>{{ $s('toko_jam_minggu') }}</span></li>
                </ul>
            </div>
        </div>
        <div class="lf-copy">&copy; {{ date('Y') }} {{ $s('toko_nama') }}. Hak Cipta Dilindungi Undang-Undang.</div>
    </footer>
@endsection

@push('scripts')
<script>
    // Tombol "+ Tambah Keranjang": memakai keranjang bawaan layout (window.RF, disimpan di browser)
    document.querySelectorAll('[data-add]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            var kartu = tombol.closest('.pc');
            var maks = tombol.dataset.max ? Number(tombol.dataset.max) : Infinity;   // kosong = tidak dibatasi stok
            if (RF.add(tombol.dataset.add, maks, kartu.querySelector('img, svg'))) {
                RF.toast('Ditambahkan ke keranjang.');
            }
        });
    });
</script>
@endpush
