@extends('layouts.pelanggan')

@section('title', 'Riwayat Pesanan')

@push('head')
<style>
    .rw { --m:var(--maroon,#7b1a33); --md:var(--maroon-dk,#531a35); --ln:var(--line,#ecd9de);
          --soft:#fbeef1; --gray:#e4d3d9;
          max-width:1100px; margin:0 auto; padding:16px 16px 104px; color:#3a1020; }
    .rw *, .rw *::before, .rw *::after { box-sizing:border-box; }

    .rw-hero { position:relative; overflow:hidden; padding:20px 18px; border-radius:24px; color:#fff;
               background:radial-gradient(circle at 80% 0%, rgba(255,255,255,.18), transparent 55%), linear-gradient(135deg, #8b2342, #531a35);
               box-shadow:0 10px 24px rgba(83,26,53,.25); }
    .rw-title { margin:0; font-family:"Playfair Display", Georgia, "Times New Roman", serif; font-size:28px; font-weight:700; line-height:1.15; }
    .rw-sub { margin:6px 0 0; max-width:520px; font-size:11.5px; line-height:1.5; color:rgba(255,255,255,.85); }

    .rw-tabs { display:flex; gap:8px; margin:16px -16px; padding:4px 16px 8px; overflow-x:auto; scrollbar-width:none; }
    .rw-tabs::-webkit-scrollbar { display:none; }
    .rw-tab { flex:none; padding:9px 18px; border-radius:999px; background:#fff; font-size:11.5px; font-weight:700; color:#3a1020; text-decoration:none; box-shadow:0 2px 8px rgba(122,31,61,.08); }
    .rw-tab.on { color:#fff; background:linear-gradient(135deg, #8b2342, #531a35); box-shadow:0 6px 14px rgba(83,26,53,.3); }

    .rw-card { margin-bottom:14px; overflow:hidden; border-radius:22px; background:#fff; box-shadow:0 6px 18px rgba(122,31,61,.10); }
    .rw-head { display:grid; grid-template-columns:1fr auto; align-items:center; gap:6px 10px; padding:12px 14px; background:var(--soft); }
    .rw-jenis { font-size:10.5px; font-weight:800; letter-spacing:.3px; color:#3a1020; }
    .rw-meta { grid-column:1 / -1; display:flex; flex-wrap:wrap; gap:2px 14px; font-size:10.5px; color:#8a7078; }
    .rw-meta strong { color:var(--m); }
    .rw-badge { padding:5px 12px; border-radius:999px; font-size:10px; font-weight:800; white-space:nowrap; }
    .rw-b-warn, .rw-b-ship { background:#fdeaa8; color:#8a5a00; }
    .rw-b-info { background:#d9ebfb; color:#1d5f93; }
    .rw-b-ok { background:#d6f0dc; color:#1f6b36; }
    .rw-b-bad { background:#fde1e5; color:#b3263f; }

    .rw-body { display:grid; gap:14px; padding:14px; }
    .rw-row { display:grid; gap:12px; }
    .rw-prod { display:flex; align-items:center; gap:12px; }
    .rw-thumb { flex:none; width:64px; height:64px; overflow:hidden; border-radius:14px; background:linear-gradient(135deg, #f7d3de, #f2c4d2); }
    .rw-thumb img { display:block; width:100%; height:100%; object-fit:cover; }
    .rw-name { margin:0; font-size:13px; font-weight:800; line-height:1.3; }
    .rw-line { margin:3px 0 0; font-size:10.5px; line-height:1.4; color:#8a7078; }
    .rw-pay { margin:3px 0 0; font-size:10.5px; font-weight:700; color:var(--m); }
    .rw-pay.ok { color:#1f6b36; }
    .rw-total { display:flex; align-items:baseline; justify-content:space-between; gap:8px; padding-top:10px; border-top:1px dashed var(--ln); }
    .rw-total span { font-size:11px; color:#8a7078; }
    .rw-total strong { font-size:18px; font-weight:800; color:var(--m); }
    .rw-total strong.kasir { color:#3a1020; }

    .rw-timeline { padding:14px 10px; border-radius:16px; background:var(--soft); }
    .rw-tl-title { margin:0 0 12px 6px; font-size:10.5px; font-weight:800; }
    .rw-steps { display:flex; }
    .rw-step { position:relative; flex:1; display:flex; flex-direction:column; align-items:center; text-align:center; }
    .rw-step + .rw-step::before { content:""; position:absolute; top:10px; left:-50%; width:100%; height:3px; background:var(--gray); }
    .rw-step.reach + .rw-step.reach::before { background:var(--m); }
    .rw-dot { position:relative; z-index:1; display:grid; place-items:center; width:22px; height:22px; border-radius:50%; font-size:10px; font-weight:800; background:var(--gray); color:#7a6b70; }
    .rw-step.done .rw-dot { background:var(--m); color:#fff; }
    .rw-step.now .rw-dot { background:#fff; border:4px solid var(--m); }
    .rw-sn { margin-top:6px; padding:0 2px; font-size:9.5px; font-weight:700; line-height:1.25; }
    .rw-st { margin-top:1px; font-size:8.5px; color:#8a7078; }

    .rw-info { display:grid; gap:4px; padding:10px 12px; border:1px solid var(--ln); border-radius:12px; font-size:10.5px; font-weight:600; line-height:1.4; }
    .rw-info em { font-style:normal; font-weight:800; color:var(--m); }
    .rw-cancel { padding:12px 14px; border-radius:14px; background:#fdecef; color:#b3263f; font-size:11.5px; }

    .rw-actions { display:flex; gap:10px; }
    .rw-btn { flex:1; padding:11px 12px; border:2px solid var(--m); border-radius:999px; background:#fff; font-size:11.5px; font-weight:800; text-align:center; text-decoration:none; color:var(--m); }
    .rw-btn.fill { background:linear-gradient(135deg, #8b2342, #531a35); border-color:transparent; color:#fff; }

    .rw-empty { padding:40px 20px; border-radius:22px; background:#fff; text-align:center; box-shadow:0 6px 18px rgba(122,31,61,.08); }
    .rw-empty b { display:block; font-size:14px; }
    .rw-empty p { margin:6px 0 16px; font-size:11.5px; color:#8a7078; }
    .rw-empty .rw-btn { display:inline-block; flex:none; padding:11px 26px; }
    .rw-pager { display:flex; justify-content:center; gap:8px; margin-top:20px; font-size:11.5px; }
    .rw-pager a { padding:9px 16px; border-radius:999px; background:#fff; color:#3a1020; text-decoration:none; box-shadow:0 2px 8px rgba(122,31,61,.08); }

    @media (min-width:768px) {
        .rw { padding:32px 24px 56px; }
        .rw-hero { padding:28px 32px; }
        .rw-title { font-size:34px; }
        .rw-sub { font-size:13px; }
        .rw-tabs { margin:20px 0; padding:4px 2px 8px; }
        .rw-head { grid-template-columns:auto 1fr auto; gap:0 28px; padding:14px 24px; }
        .rw-meta { grid-column:auto; grid-row:1; gap:2px 28px; font-size:12px; }
        .rw-jenis { font-size:12px; }
        .rw-badge { grid-row:1; font-size:11px; padding:6px 14px; }
        .rw-body { padding:22px 24px 24px; }
        .rw-row { grid-template-columns:1fr auto; align-items:center; gap:16px; }
        .rw-thumb { width:80px; height:72px; }
        .rw-name { font-size:15px; }
        .rw-line, .rw-pay { font-size:12px; }
        .rw-total { display:block; padding-top:0; border-top:0; text-align:right; }
        .rw-total span { display:block; font-size:12px; }
        .rw-total strong { font-size:24px; }
        .rw-timeline { padding:16px 20px; }
        .rw-dot { width:24px; height:24px; }
        .rw-step + .rw-step::before { top:11px; }
        .rw-sn { font-size:11px; }
        .rw-st { font-size:10px; }
        .rw-info { display:flex; align-items:center; justify-content:space-between; font-size:11px; }
        .rw-btn { flex:none; padding:10px 26px; font-size:12px; }
    }
</style>
@endpush

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

    $statusUi = [
        'menunggu_pembayaran'   => ['Menunggu Pembayaran', 'warn'],
        'menunggu_dikonfirmasi' => ['Menunggu Konfirmasi', 'warn'],
        'dikonfirmasi'          => ['Dikonfirmasi', 'warn'],
        'diproses'              => ['Diproses', 'warn'],
        'dalam_pengiriman'      => ['Dalam Pengiriman', 'ship'],
        'siap_diambil'          => ['Siap Diambil', 'info'],
        'selesai'               => ['Selesai', 'ok'],
        'dibatalkan'            => ['Dibatalkan', 'bad'],
    ];
    $metode = ['bayar_di_toko' => 'Kasir Toko', 'QRIS' => 'QRIS', 'transfer_bank' => 'Transfer Bank'];
    $bayarUi = ['belum_dibayar' => 'Belum Dibayar', 'proses' => 'Menunggu Verifikasi', 'lunas' => 'Lunas'];
    $tabs = ['semua' => 'Semua', 'diproses' => 'Diproses', 'siap_diambil' => 'Siap Diambil', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai'];
@endphp

<div class="rw">
    <section class="rw-hero">
        <h1 class="rw-title">Riwayat Pesanan</h1>
        <p class="rw-sub">Pantau status pesanan bunga Anda: diproses, dikirim, atau siap diambil di toko.</p>
    </section>

    <div class="rw-tabs">
        @foreach ($tabs as $kunci => $label)
            <a href="{{ route('pelanggan.riwayat', ['status' => $kunci]) }}" class="rw-tab {{ $filter === $kunci ? 'on' : '' }}">{{ $label }} ({{ $jumlah[$kunci] }})</a>
        @endforeach
    </div>

    @forelse ($daftar as $o)
        @php
            $dikirim = $o->jenis_pengambilan === 'dikirim';
            $toko    = $o->metode_pembayaran === 'bayar_di_toko';
            $item    = $o->items->first();
            $produk  = $item?->product;
            $lainnya = $o->items->count() - 1;
            $batal   = $o->status_pesanan === 'dibatalkan';
            [$statusLabel, $statusKelas] = $statusUi[$o->status_pesanan];
            if ($o->status_pesanan === 'selesai' && $o->status_pembayaran === 'lunas') {
                $statusLabel = 'Selesai & Lunas';
            }

            $foto = $o->foto_buket ? \Illuminate\Support\Facades\Storage::disk('public')->url($o->foto_buket) : $produk?->foto_url;

            $langkah = [
                'dikonfirmasi' => 'Dikonfirmasi',
                'diproses'     => 'Diproses',
                ($dikirim ? 'dalam_pengiriman' : 'siap_diambil') => $dikirim ? 'Dalam Pengiriman' : 'Siap Diambil',
                'selesai'      => 'Selesai',
            ];
            $posisi = array_search($o->status_pesanan, array_keys($langkah), true);
            $posisi = $posisi === false ? -1 : $posisi;
            $tuntas = $o->status_pesanan === 'selesai';

            $riwayat = $o->statusHistory->sortBy('diubah_pada')->groupBy('status_pesanan');
            $jam = function ($kunci) use ($riwayat) {
                $baris = $riwayat->get($kunci)?->last();
                return $baris ? \Illuminate\Support\Carbon::parse($baris->diubah_pada)->format('H:i') . ' WIB' : null;
            };
            $estimasi = $o->estimasi_tiba ? \Illuminate\Support\Carbon::parse($o->estimasi_tiba)->format('H:i') . ' WIB' : null;
        @endphp

        <article class="rw-card">
            <div class="rw-head">
                <b class="rw-jenis">{{ $dikirim ? 'DIKIRIM KURIR' : 'AMBIL MANDIRI DI TOKO' }}</b>
                <span class="rw-badge rw-b-{{ $statusKelas }}">{{ $statusLabel }}</span>
                <div class="rw-meta">
                    <span>No. Pesanan: <strong>#{{ $o->no_invoice }}</strong></span>
                    <span>Tanggal: {{ $o->dibuat_pada->locale('id')->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>
            </div>

            <div class="rw-body">
                <div class="rw-row">
                    <div class="rw-prod">
                        <div class="rw-thumb">@if ($foto)<img src="{{ $foto }}" alt="Foto pesanan">@endif</div>
                        <div>
                            <p class="rw-name">{{ $produk?->nama_produk ?? 'Produk tidak tersedia' }}@if ($lainnya > 0) (+{{ $lainnya }} Item lainnya)@endif</p>
                            <p class="rw-line">@if ($dikirim) Tujuan: {{ $o->alamat_tujuan ?: '-' }} @else Lokasi Ambil: Outlets Royal Florist @endif</p>
                            <p class="rw-pay {{ $toko ? 'ok' : '' }}">Pembayaran {{ $metode[$o->metode_pembayaran] }}: {{ $bayarUi[$o->status_pembayaran] }}</p>
                        </div>
                    </div>
                    <div class="rw-total">
                        <span>{{ $toko ? 'Total Tagihan Kasir:' : 'Total Pembayaran:' }}</span>
                        <strong class="{{ $toko ? 'kasir' : '' }}">{{ $rp($o->total_harga) }}</strong>
                    </div>
                </div>

                @if ($batal)
                    <div class="rw-cancel"><b>Pesanan dibatalkan.</b> {{ $o->catatan_toko }}</div>
                @else
                    <div class="rw-timeline">
                        <p class="rw-tl-title">Status Pesanan:</p>
                        <div class="rw-steps">
                            @foreach ($langkah as $kunci => $nama)
                                @php
                                    $i = $loop->index;
                                    $keadaan = ($tuntas || $i < $posisi) ? 'done' : ($i === $posisi ? 'now' : 'todo');
                                    $waktu = $jam($kunci);
                                    if (! $waktu && $kunci === 'dalam_pengiriman' && $estimasi) $waktu = 'Estimasi ' . $estimasi;
                                @endphp
                                <div class="rw-step {{ $keadaan }} {{ ($tuntas || $i <= $posisi) ? 'reach' : '' }}">
                                    <span class="rw-dot">{{ $keadaan === 'done' ? '✓' : ($keadaan === 'now' ? '' : $i + 1) }}</span>
                                    <span class="rw-sn">{{ $nama }}</span>
                                    <span class="rw-st">{{ $waktu ?? '-' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if ($o->catatan_toko || ($dikirim && $o->nama_kurir) || ($dikirim && $estimasi))
                        <div class="rw-info">
                            <span>
                                @if ($dikirim && $o->nama_kurir) Kurir: {{ $o->nama_kurir }} &middot; {{ $o->no_hp_kurir }} @endif
                                @if ($dikirim && $o->nama_kurir && $o->catatan_toko) | @endif
                                @if ($o->catatan_toko) Catatan toko: {{ $o->catatan_toko }} @endif
                            </span>
                            @if ($dikirim && $estimasi) <em>Estimasi tiba {{ $estimasi }}</em> @endif
                        </div>
                    @endif
                @endif

                @if (($dikirim && $o->status_pesanan === 'dalam_pengiriman' && $o->no_hp_kurir) || $o->bukti_pembayaran || in_array($o->status_pesanan, ['selesai', 'dibatalkan']))
                    <div class="rw-actions">
                        @if ($dikirim && $o->status_pesanan === 'dalam_pengiriman' && $o->no_hp_kurir)
                            <a class="rw-btn fill" href="tel:{{ preg_replace('/[^0-9+]/', '', $o->no_hp_kurir) }}">Hubungi Kurir</a>
                        @endif
                        @if ($o->bukti_pembayaran)
                            <a class="rw-btn" target="_blank" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($o->bukti_pembayaran) }}">Lihat Bukti Bayar</a>
                        @endif
                        @if (in_array($o->status_pesanan, ['selesai', 'dibatalkan']))
                            <a class="rw-btn fill" href="{{ route('pelanggan.katalog') }}">Pesan Lagi &rarr;</a>
                        @endif
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="rw-empty">
            <b>Belum ada pesanan</b>
            <p>Pesanan Anda akan muncul di sini setelah Anda berbelanja.</p>
            <a class="rw-btn fill" href="{{ route('pelanggan.katalog') }}">Lihat Katalog</a>
        </div>
    @endforelse

    @if ($daftar->hasPages())
        <div class="rw-pager">
            <a href="{{ $daftar->previousPageUrl() ?? '#' }}">&lsaquo; Sebelumnya</a>
            <a href="{{ $daftar->nextPageUrl() ?? '#' }}">Berikutnya &rsaquo;</a>
        </div>
    @endif
</div>
@endsection
