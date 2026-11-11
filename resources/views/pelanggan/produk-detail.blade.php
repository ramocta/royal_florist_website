@php
    $namaKategori = optional($produk->category)->category_name;
    $namaJenis    = optional($produk->subcategory)->subcategory_name;
    $tersedia     = $produk->status === 'Tersedia';
    $wrapping     = \App\Http\Controllers\pelanggan\ProdukDetailController::WRAPPING;

    // Pilihan wrapping hanya untuk buket, seperti di desain
    $punyaWrapping = $namaKategori === 'Buket';

    // Panduan perawatan per kategori. Teks umum, silakan ubah sesuai kebutuhan toko.
    $panduan = [
        'Buket' => [
            'Potong 2-3 cm ujung tangkai secara miring begitu buket tiba.',
            'Taruh di vas berisi air bersih, dan ganti air setiap 1-2 hari.',
            'Hindari sinar matahari langsung dan tempat yang panas.',
        ],
    ][$namaKategori] ?? [];

    $placeholder = '<svg width="120" height="120" viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="9" r="6" fill="#f4b6c6"/><circle cx="9" cy="17" r="6" fill="#e9a4b8"/><circle cx="23" cy="17" r="6" fill="#f4b6c6"/><circle cx="16" cy="23" r="6" fill="#e9a4b8"/><circle cx="16" cy="16" r="4" fill="#fff"/></svg>';
@endphp
@extends('layouts.pelanggan')

@section('title', $produk->nama_produk . ' - Royal Florist')

@push('head')
<style>
    .back { display:inline-block; margin-bottom:16px; font-size:13px; font-weight:600; color:var(--maroon); text-decoration:none; }
    .back:hover { text-decoration:underline; }
    .alert { margin-bottom:16px; padding:12px 16px; border-radius:14px; font-size:13px; background:#fde8ec; color:#8a1d36; }

    .detail { display:grid; grid-template-columns:minmax(0, .9fr) minmax(0, 1.1fr); gap:38px; align-items:start; }
    .foto-card { padding:16px; background:#fff; border-radius:26px; box-shadow:0 14px 34px rgba(83,26,53,.10); }
    .foto { position:relative; display:grid; place-items:center; aspect-ratio:1 / 1; overflow:hidden; border-radius:20px; background:var(--blush-dk); }
    .foto img { width:100%; height:100%; object-fit:cover; }
    .badge-best { position:absolute; top:14px; left:14px; padding:5px 12px; border-radius:99px; background:var(--maroon); color:#fff; font-size:10.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }

    .info h1 { margin:0; font-family:'Playfair Display', serif; font-size:32px; line-height:1.2; color:var(--maroon); }
    .info .sub { margin:6px 0 0; font-size:13px; color:var(--muted); }
    .harga { margin:14px 0 0; font-size:28px; font-weight:700; color:var(--maroon); }
    .info hr { border:0; border-top:1px solid var(--blush-dk); margin:18px 0; }
    .info h2 { margin:0 0 8px; font-family:'Playfair Display', serif; font-size:16px; color:var(--maroon); }
    .info .desk { margin:0 0 20px; font-size:13.5px; line-height:1.7; color:#5b4a52; white-space:pre-line; }

    .pills { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:22px; }
    .pill { position:relative; }
    .pill input { position:absolute; opacity:0; inset:0; cursor:pointer; }
    .pill span { display:inline-block; padding:9px 18px; border-radius:99px; border:1.5px solid var(--blush-dk); background:#fff; font-size:12.5px; font-weight:600; color:var(--maroon); }
    .pill input:checked + span { background:var(--maroon); border-color:var(--maroon); color:#fff; }
    .pill input:focus-visible + span { outline:2px solid var(--maroon); outline-offset:2px; }

    .step { display:inline-flex; align-items:center; gap:10px; margin-bottom:6px; }
    .step button { width:36px; height:36px; border-radius:10px; border:1.5px solid var(--blush-dk); background:#fff; font-size:18px; line-height:1; color:var(--maroon); cursor:pointer; }
    .step button:hover { background:var(--blush); }
    .step input { width:48px; border:0; background:transparent; text-align:center; font-size:16px; font-weight:700; color:var(--maroon); font-family:inherit; -moz-appearance:textfield; }
    .step input::-webkit-outer-spin-button, .step input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
    .stok { margin:4px 0 0; font-size:12px; color:var(--muted); }
    .stok.habis { color:#b3263f; font-weight:600; }

    .aksi { display:flex; flex-wrap:wrap; gap:12px; margin-top:22px; }
    .aksi button { flex:1 1 190px; padding:14px 20px; border-radius:99px; border:1.5px solid var(--maroon); font-family:inherit; font-size:14px; font-weight:700; cursor:pointer; }
    .aksi .beli { background:linear-gradient(135deg, var(--maroon), var(--maroon-dk)); color:#fff; }
    .aksi .keranjang { background:#fff; color:var(--maroon); }
    .aksi .keranjang:hover { background:var(--blush); }
    .aksi button:disabled { opacity:.5; cursor:not-allowed; }

    .panduan { margin-top:30px; padding:22px 26px; background:#fff; border-radius:22px; box-shadow:0 10px 26px rgba(83,26,53,.08); }
    .panduan h2 { margin:0 0 10px; font-family:'Playfair Display', serif; font-size:19px; color:var(--maroon); }
    .panduan ul { margin:0; padding-left:20px; font-size:13.5px; line-height:1.8; color:#5b4a52; }

    @media (max-width:860px) {
        .detail { grid-template-columns:1fr; gap:22px; }
        .info h1 { font-size:26px; }
        .aksi button { flex-basis:100%; }
    }
</style>
@endpush

@section('content')
    <a class="back" href="{{ route('pelanggan.katalog') }}">&larr; Kembali ke Katalog</a>

    @if (session('error'))
        <div class="alert">{{ session('error') }}</div>
    @endif
    @foreach ($errors->all() as $pesan)
        <div class="alert">{{ $pesan }}</div>
    @endforeach

    <div class="detail">
        {{-- FOTO --}}
        <div class="foto-card">
            <div class="foto">
                @if ($bestseller)
                    <span class="badge-best">Bestseller</span>
                @endif
                @if ($produk->foto_url)
                    <img src="{{ $produk->foto_url }}" alt="{{ $produk->nama_produk }}" onerror="this.style.display='none'">
                @else
                    {!! $placeholder !!}
                @endif
            </div>
        </div>

        {{-- INFO. Rating dan ulasan pelanggan sengaja tidak ada. --}}
        <div class="info">
            <h1>{{ $produk->nama_produk }}</h1>
            <p class="sub">{{ $namaKategori }}@if ($namaJenis) &bull; {{ $namaJenis }}@endif</p>
            <p class="harga">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
            <hr>

            <h2>Deskripsi Produk</h2>
            <p class="desk">{{ $produk->deskripsi ?: 'Belum ada deskripsi untuk produk ini.' }}</p>

            {{-- Satu form untuk Beli Sekarang. "Tambah ke Keranjang" memakai pilihan yang sama lewat JavaScript. --}}
            <form method="POST" action="{{ route('pelanggan.produk.beli', $produk) }}" id="formBeli"
                  data-id="{{ $produk->id_product }}" data-max="{{ $maks }}" data-stok="{{ $produk->pakai_stok ? $produk->stok : '' }}">
                @csrf

                @if ($punyaWrapping)
                    <h2>Pilih Warna Wrapping (Pembungkus)</h2>
                    <div class="pills">
                        @foreach ($wrapping as $i => $warna)
                            <label class="pill">
                                <input type="radio" name="wrapping" value="{{ $warna }}" @checked(old('wrapping', $i === 0 ? $warna : null) === $warna)>
                                <span>{{ $warna }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif

                <h2>Jumlah Pesanan</h2>
                <div class="step">
                    <button type="button" id="kurang" aria-label="Kurangi jumlah">&minus;</button>
                    <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', 1) }}" min="1" max="{{ max(1, $maks) }}" aria-label="Jumlah pesanan">
                    <button type="button" id="tambah" aria-label="Tambah jumlah">+</button>
                </div>
                @if (! $tersedia)
                    <p class="stok habis">{{ $produk->status === 'Habis' ? 'Stok habis' : 'Produk tidak tersedia' }}</p>
                @elseif ($produk->pakai_stok)
                    <p class="stok">Stok tersisa {{ $produk->stok }}</p>
                @endif

                <div class="aksi">
                    <button type="submit" class="beli" @disabled(! $tersedia)>Beli Sekarang</button>
                    <button type="button" class="keranjang" id="btnKeranjang" @disabled(! $tersedia)>Tambah ke Keranjang</button>
                </div>
            </form>
        </div>
    </div>

    @if (count($panduan))
        <section class="panduan">
            <h2>Panduan Perawatan</h2>
            <ul>
                @foreach ($panduan as $tips)
                    <li>{{ $tips }}</li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        var form   = document.getElementById('formBeli');
        var qty    = document.getElementById('jumlah');
        var maks   = Number(form.dataset.max) || 1;                              // batas pada stepper
        var stok   = form.dataset.stok === '' ? Infinity : Number(form.dataset.stok); // batas total di keranjang
        var id     = form.dataset.id;

        function atur(n) {
            n = Math.max(1, Math.min(maks, Math.floor(n) || 1));
            qty.value = n;
        }

        document.getElementById('kurang').addEventListener('click', function () { atur(Number(qty.value) - 1); });
        document.getElementById('tambah').addEventListener('click', function () {
            if (Number(qty.value) >= maks) RF.toast('Jumlah melebihi stok yang tersedia.');
            atur(Number(qty.value) + 1);
        });
        qty.addEventListener('change', function () { atur(Number(qty.value)); });

        // Tambah ke Keranjang: memakai keranjang bawaan layout (RF), disimpan di browser
        document.getElementById('btnKeranjang').addEventListener('click', function () {
            var jumlah = Number(qty.value);
            var foto   = document.querySelector('.foto img, .foto svg');
            var ok     = true;

            for (var i = 0; i < jumlah; i++) {
                // Animasi gambar terbang cukup sekali (pada item pertama)
                if (!RF.add(id, stok, i === 0 ? foto : null)) { ok = false; break; }
            }

            // Pilihan wrapping disimpan terpisah (kunci rf_opsi), karena keranjang RF hanya menyimpan jumlah per produk
            var pilih = form.querySelector('input[name=wrapping]:checked');
            if (pilih) {
                try {
                    var opsi = JSON.parse(localStorage.getItem('rf_opsi')) || {};
                    opsi[id] = { wrapping: pilih.value };
                    localStorage.setItem('rf_opsi', JSON.stringify(opsi));
                } catch (e) {}
            }

            if (ok) RF.toast('Ditambahkan ke keranjang.');
        });
    })();
</script>
@endpush
