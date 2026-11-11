@php
    $namaToko = \App\Models\Setting::ambil('toko_nama');
    $wa = preg_replace('/\D/', '', \App\Models\Setting::ambil('toko_whatsapp') ?? '');
    if (str_starts_with($wa, '0')) { $wa = '62' . substr($wa, 1); }
@endphp
@extends('layouts.pelanggan')

@section('title', 'Pembayaran ' . $order->no_invoice . ' - Royal Florist')

@push('head')
<style>
    .langkah { display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-bottom:18px; font-size:12.5px; color:var(--muted); }
    .langkah .on { color:var(--maroon); font-weight:700; }
    .langkah .pemisah { opacity:.5; }
    .judul h1 { margin:0; font-family:'Playfair Display', serif; font-size:30px; color:var(--maroon); }
    .judul p { margin:4px 0 22px; font-size:13.5px; color:var(--muted); }

    .alert { margin-bottom:16px; padding:12px 16px; border-radius:14px; font-size:13px; background:#fde8ec; color:#8a1d36; }
    .alert.warn { background:#fff3cd; color:#7a5a00; }

    .grid-bayar { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:22px; align-items:start; }
    .kartu { background:#fff; border-radius:24px; box-shadow:0 12px 30px rgba(83,26,53,.09); overflow:hidden; }
    .kartu .isi { padding:20px 22px 24px; }

    .waktu { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 18px; background:#fff3cd; color:#7a5a00; font-size:13px; font-weight:600; }
    .waktu b { font-size:17px; letter-spacing:.04em; font-variant-numeric:tabular-nums; }
    .waktu.habis { background:#fde8ec; color:#8a1d36; }

    .qris-top { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 14px; margin-bottom:16px; border-radius:12px; background:var(--blush); font-size:11px; color:var(--muted); }
    .qris-top b { color:var(--maroon); font-size:12.5px; }
    .qris-top strong { color:var(--maroon); font-size:11.5px; text-transform:uppercase; }
    .qr-box { display:grid; place-items:center; width:230px; max-width:100%; aspect-ratio:1 / 1; margin:0 auto; padding:12px; border:2px solid var(--maroon); border-radius:18px; background:#fff; }
    .qr-box img { width:100%; height:100%; object-fit:contain; }
    .qr-kosong { font-size:12px; line-height:1.5; text-align:center; color:var(--muted); }
    .nmid { margin:12px 0 2px; text-align:center; font-size:12px; font-weight:700; color:var(--ink); }
    .scan { margin:0; text-align:center; font-size:11.5px; color:var(--muted); }

    .total { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:18px; padding-top:16px; border-top:1px solid var(--blush-dk); font-size:12.5px; color:var(--muted); }
    .total b { font-size:24px; color:var(--maroon); }
    .catatan { margin:12px 0 0; padding:10px 14px; border-radius:12px; background:var(--blush); font-size:12px; font-weight:600; color:var(--maroon-dk); }
    .cara { margin-top:18px; font-size:12.5px; color:#5b4a52; }
    .cara h3 { margin:0 0 6px; font-size:13px; color:var(--ink); }
    .cara ol { margin:0; padding-left:18px; line-height:1.8; }

    .upload-head { padding:15px 22px; background:linear-gradient(135deg, var(--maroon), var(--maroon-dk)); color:#fff; font-family:'Playfair Display', serif; font-size:17px; font-weight:700; }
    .ketentuan { margin-bottom:18px; padding:11px 14px; border:1.5px solid var(--maroon); border-radius:12px; font-size:12px; line-height:1.55; color:var(--maroon-dk); }
    .ketentuan b { color:var(--maroon); }
    .upload label.lbl { display:block; margin:14px 0 6px; font-size:12.5px; font-weight:700; color:var(--ink); }
    .upload input[type=text] { width:100%; padding:11px 14px; border:1.5px solid var(--blush-dk); border-radius:12px; background:var(--blush); font-family:inherit; font-size:13px; color:var(--ink); }
    .upload input[type=text]:focus { outline:2px solid var(--maroon); outline-offset:0; background:#fff; }
    .err { margin:5px 0 0; font-size:12px; color:#b3263f; }

    .drop { display:grid; place-items:center; gap:4px; padding:26px 14px; border:2px dashed var(--maroon); border-radius:16px; text-align:center; cursor:pointer; transition:.15s; }
    .drop:hover, .drop.over { background:var(--blush); }
    .drop .ik { font-size:26px; }
    .drop b { font-size:13px; color:var(--maroon); }
    .drop small { font-size:11.5px; color:var(--muted); }
    .drop .nama-file { font-size:12.5px; font-weight:700; color:var(--ink); word-break:break-all; }
    .drop input { position:absolute; width:1px; height:1px; opacity:0; }

    .kirim { width:100%; margin-top:20px; padding:14px 20px; border:0; border-radius:99px; background:linear-gradient(135deg, var(--maroon), var(--maroon-dk)); color:#fff; font-family:inherit; font-size:14px; font-weight:700; cursor:pointer; }
    .kirim:disabled { opacity:.5; cursor:not-allowed; }

    @media (max-width:860px) {
        .grid-bayar { grid-template-columns:1fr; }
        .judul h1 { font-size:24px; }
    }
</style>
@endpush

@section('content')
    <div class="langkah">
        <span>1. Keranjang</span><span class="pemisah">&rarr;</span>
        <span>2. Checkout</span><span class="pemisah">&rarr;</span>
        <span class="on">3. Pembayaran &amp; Bukti</span>
    </div>

    <div class="judul">
        <h1>Pembayaran Pesanan #{{ $order->no_invoice }}</h1>
        <p>Selesaikan pembayaran &amp; unggah bukti transfer agar bunga dapat segera diproses</p>
    </div>

    @if (session('error'))
        <div class="alert">{{ session('error') }}</div>
    @endif

    <div class="grid-bayar">
        {{-- ===== KIRI: QRIS ===== --}}
        <div class="kartu">
            <div class="waktu {{ $kadaluarsa ? 'habis' : '' }}">
                <span>&#9200; Batas Waktu Pembayaran:</span>
                <b id="hitung" data-sisa="{{ $sisaDetik }}">{{ $kadaluarsa ? '00:00:00' : '--:--:--' }}</b>
            </div>

            <div class="isi">
                <div class="qris-top">
                    <span><b>QRIS</b> &nbsp;|&nbsp; Quick Response Code Indonesian Standard</span>
                    <strong>{{ $namaToko }}</strong>
                </div>

                <div class="qr-box">
                    @if ($qris)
                        <img src="{{ $qris }}" alt="Kode QRIS {{ $namaToko }}">
                    @else
                        <p class="qr-kosong">Kode QRIS belum diatur oleh toko.<br>Silakan hubungi kami lewat
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" style="color:var(--maroon);font-weight:700;">WhatsApp</a>.</p>
                    @endif
                </div>
                @if ($nmid)
                    <p class="nmid">NMID: {{ $nmid }}</p>
                @endif
                <p class="scan">Scan menggunakan GoPay, OVO, ShopeePay, Dana, BCA, Livin, dll.</p>

                <div class="total">
                    <span>Total Tagihan:</span>
                    <b>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</b>
                </div>
                <p class="catatan">&#128204; Catatan: Pastikan nominal transfer sesuai sampai digit terakhir.</p>

                <div class="cara">
                    <h3>Cara Pembayaran QRIS:</h3>
                    <ol>
                        <li>Buka aplikasi e-wallet / mobile banking pilihan Anda.</li>
                        <li>Pilih menu <b>Scan / Bayar</b> dan arahkan kamera ke kode QR di atas.</li>
                        <li>Periksa nama penerima (<b>{{ $namaToko }}</b>) dan konfirmasi pembayaran.</li>
                        <li>Simpan tangkapan layar (screenshot) bukti pembayaran Anda.</li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- ===== KANAN: UNGGAH BUKTI ===== --}}
        <div class="kartu upload">
            <div class="upload-head">Upload Bukti Pembayaran</div>
            <div class="isi">
                @if ($kadaluarsa)
                    <div class="alert warn">Batas waktu pembayaran sudah habis, jadi bukti pembayaran tidak dapat dikirim lagi. Silakan buat pesanan baru.</div>
                @endif

                <div class="ketentuan">
                    <b>Ketentuan Pemrosesan Bunga:</b><br>
                    Bunga <b>hanya akan mulai dirangkai &amp; dikirim</b> setelah bukti pembayaran diunggah dan diverifikasi oleh kasir toko kami.
                </div>

                <form method="POST" action="{{ route('pelanggan.pembayaran.kirim', $order) }}" enctype="multipart/form-data" id="formBukti">
                    @csrf

                    <label class="lbl" for="nama_pengirim">Nama Pengirim / Pembayar:</label>
                    <input type="text" id="nama_pengirim" name="nama_pengirim" value="{{ old('nama_pengirim', auth()->user()->nama) }}" maxlength="100">
                    @error('nama_pengirim') <p class="err">{{ $message }}</p> @enderror

                    <label class="lbl" for="aplikasi">Metode / Aplikasi yang Digunakan:</label>
                    <input type="text" id="aplikasi" name="aplikasi" value="{{ old('aplikasi') }}" maxlength="50" list="daftar-aplikasi" placeholder="Contoh: GoPay / BCA Mobile">
                    <datalist id="daftar-aplikasi">
                        @foreach (['GoPay', 'OVO', 'DANA', 'ShopeePay', 'LinkAja', 'BCA Mobile', 'Livin\' by Mandiri', 'BRImo', 'BNI Mobile'] as $app)
                            <option value="{{ $app }}">
                        @endforeach
                    </datalist>
                    @error('aplikasi') <p class="err">{{ $message }}</p> @enderror

                    <label class="lbl" for="bukti">Unggah Struk / Screenshot Bukti Transfer:</label>
                    <label class="drop" id="drop" for="bukti">
                        <span class="ik">&#128229;</span>
                        <b>Klik atau Drag &amp; Drop Bukti Transfer Ke Sini</b>
                        <small id="infoFile">Format: JPG, PNG, atau PDF (Maks. 5 MB)</small>
                        <input type="file" id="bukti" name="bukti" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                    </label>
                    @error('bukti') <p class="err">{{ $message }}</p> @enderror

                    <label class="lbl" for="catatan">Catatan Khusus ke Kasir (Opsional):</label>
                    <input type="text" id="catatan" name="catatan" value="{{ old('catatan') }}" maxlength="255" placeholder="Contoh: Mohon dikirim sebelum jam 11 siang ya kak...">
                    @error('catatan') <p class="err">{{ $message }}</p> @enderror

                    <button type="submit" class="kirim" id="btnKirim" @disabled($kadaluarsa)>Kirim Bukti Pembayaran &rarr;</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        // ---- Hitung mundur. Dasarnya detik tersisa dari server, jadi tidak terpengaruh jam di HP/laptop pengguna ----
        var elHitung = document.getElementById('hitung');
        var sisa = Number(elHitung.dataset.sisa);
        var mulai = Date.now();
        var btn = document.getElementById('btnKirim');

        function dua(n) { return String(n).padStart(2, '0'); }
        function tampil() {
            var s = Math.max(0, sisa - Math.floor((Date.now() - mulai) / 1000));
            elHitung.textContent = dua(Math.floor(s / 3600)) + ':' + dua(Math.floor(s % 3600 / 60)) + ':' + dua(s % 60);
            if (s === 0) {
                elHitung.parentElement.classList.add('habis');
                btn.disabled = true;
                clearInterval(pewaktu);
            }
        }
        if (sisa > 0) { var pewaktu = setInterval(tampil, 1000); tampil(); }

        // ---- Area unggah: klik, atau seret file ke dalamnya ----
        var input = document.getElementById('bukti');
        var drop = document.getElementById('drop');
        var info = document.getElementById('infoFile');

        function tampilkanNama() {
            if (input.files.length) {
                var f = input.files[0];
                info.textContent = f.name + ' (' + (f.size / 1048576).toFixed(2) + ' MB)';
                info.className = 'nama-file';
            }
        }
        input.addEventListener('change', tampilkanNama);

        ['dragenter', 'dragover'].forEach(function (e) {
            drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.add('over'); });
        });
        ['dragleave', 'drop'].forEach(function (e) {
            drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.remove('over'); });
        });
        drop.addEventListener('drop', function (ev) {
            if (ev.dataTransfer.files.length) { input.files = ev.dataTransfer.files; tampilkanNama(); }
        });

        // Cegah kirim ganda kalau tombol ditekan berkali-kali
        document.getElementById('formBukti').addEventListener('submit', function () {
            btn.disabled = true; btn.textContent = 'Mengirim...';
        });
    })();
</script>
@endpush
