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

    /* Banner slider: isinya murni gambar desain UMKM (tanpa teks tambahan), diatur admin di Pengaturan > Manajemen Landing Page */
    .slider { position:relative; border-radius:26px; overflow:hidden; background:var(--blush-dk); box-shadow:0 18px 40px rgba(83,26,53,.22); }
    .slides { display:flex; transition:transform .6s ease; touch-action:pan-y; }
    .slide { flex:0 0 100%; aspect-ratio:8 / 3; }
    .slide img { width:100%; height:100%; object-fit:cover; display:block; user-select:none; -webkit-user-drag:none; }
    .slide.kosong { display:grid; place-items:center; background:linear-gradient(135deg, #6a1730, #8a2543); }
    .s-btn { position:absolute; top:50%; transform:translateY(-50%); width:42px; height:42px; border-radius:50%; border:0; background:rgba(255,255,255,.85); color:var(--maroon); font-size:22px; line-height:1; cursor:pointer; display:grid; place-items:center; box-shadow:0 4px 14px rgba(0,0,0,.18); }
    .s-btn:hover { background:#fff; }
    .s-btn.prev { left:14px; } .s-btn.next { right:14px; }
    .dots { position:absolute; left:0; right:0; bottom:12px; display:flex; justify-content:center; gap:8px; }
    .dot { width:9px; height:9px; padding:0; border:0; border-radius:99px; background:rgba(255,255,255,.6); cursor:pointer; transition:.25s; }
    .dot.on { width:26px; background:#fff; }

    .hero-badge { margin:0; font-size:11px; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:#f4b6c6; }

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
    .pc .harga { flex:1; margin:8px 4px 4px; font-size:14.5px; font-weight:700; color:var(--maroon); }
    /* Kartu produk adalah link ke halaman detail produk */
    a.pc { color:inherit; text-decoration:none; transition:transform .2s ease, box-shadow .2s ease; }
    a.pc:hover { transform:translateY(-4px); box-shadow:0 16px 32px rgba(83,26,53,.14); }
    a.pc:focus-visible { outline:2px solid var(--maroon); outline-offset:3px; }

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
        .slider { border-radius:18px; }
        .slide { aspect-ratio:16 / 9; }
        .s-btn { width:34px; height:34px; font-size:18px; }
        .s-btn.prev { left:8px; } .s-btn.next { right:8px; }
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

    {{-- ===== BANNER SLIDER: gambar-gambar yang diunggah admin (lebih dari satu = bergeser otomatis) ===== --}}
    <section class="slider" id="slider" aria-roledescription="carousel" aria-label="Banner {{ $s('toko_nama') }}">
        <div class="slides" id="slides">
            @forelse ($banners as $i => $url)
                <div class="slide" role="group" aria-label="Banner {{ $i + 1 }} dari {{ count($banners) }}">
                    <img src="{{ $url }}" alt="Banner {{ $s('toko_nama') }} {{ $i + 1 }}" draggable="false"
                         @if ($i > 0) loading="lazy" @endif>
                </div>
            @empty
                {{-- Belum ada banner diunggah: tampil latar polos supaya halaman tidak kosong --}}
                <div class="slide kosong">{!! $placeholder !!}</div>
            @endforelse
        </div>

        @if (count($banners) > 1)
            <button type="button" class="s-btn prev" id="sPrev" aria-label="Banner sebelumnya">&lsaquo;</button>
            <button type="button" class="s-btn next" id="sNext" aria-label="Banner berikutnya">&rsaquo;</button>
            <div class="dots" id="dots">
                @foreach ($banners as $i => $url)
                    <button type="button" class="dot {{ $i === 0 ? 'on' : '' }}" data-i="{{ $i }}" aria-label="Ke banner {{ $i + 1 }}"></button>
                @endforeach
            </div>
        @endif
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
                    <a class="pc" href="{{ route('pelanggan.produk.show', $p['id']) }}" aria-label="Lihat detail {{ $p['nama'] }}">
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
                    </a>
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
    // Banner slider: otomatis geser tiap 3 detik, bisa klik panah/titik, dan digeser jari di HP
    (function () {
        var wadah = document.getElementById('slides');
        var jumlah = wadah ? wadah.children.length : 0;
        if (jumlah < 2) return;

        var i = 0, timer = null, mulaiX = null;
        var titik = document.querySelectorAll('#dots .dot');

        function ke(n) {
            i = (n + jumlah) % jumlah;
            wadah.style.transform = 'translateX(' + (-i * 100) + '%)';
            titik.forEach(function (t, k) { t.classList.toggle('on', k === i); });
        }
        function mulai() { stop(); timer = setInterval(function () { ke(i + 1); }, 3000); }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }

        document.getElementById('sPrev').addEventListener('click', function () { ke(i - 1); mulai(); });
        document.getElementById('sNext').addEventListener('click', function () { ke(i + 1); mulai(); });
        titik.forEach(function (t) { t.addEventListener('click', function () { ke(Number(t.dataset.i)); mulai(); }); });

        var slider = document.getElementById('slider');
        slider.addEventListener('touchstart', function (e) { mulaiX = e.touches[0].clientX; stop(); }, { passive: true });
        slider.addEventListener('touchend', function (e) {
            if (mulaiX !== null) {
                var selisih = e.changedTouches[0].clientX - mulaiX;
                if (Math.abs(selisih) > 40) { ke(selisih < 0 ? i + 1 : i - 1); }
            }
            mulaiX = null; mulai();
        });
        slider.addEventListener('touchcancel', function () { mulaiX = null; mulai(); });
        // Tab tidak terlihat -> jeda supaya tidak "loncat" saat kembali
        document.addEventListener('visibilitychange', function () { document.hidden ? stop() : mulai(); });

        mulai();
    })();
</script>
@endpush
