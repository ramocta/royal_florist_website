@extends('layouts.pelanggan')

@section('title', 'Keranjang Belanja | Royal Florist')

@push('head')
<style>
    .k-head h1 { margin:0; font-family:'Playfair Display', serif; font-size:32px; line-height:1.15; color:var(--maroon); }
    .k-head p  { margin:4px 0 0; font-size:13px; color:var(--muted); }

    .k-grid { display:grid; grid-template-columns:minmax(0, 1fr) 385px; gap:35px; margin-top:22px; align-items:start; }

    /* Judul kolom + baris produk memakai grid yang sama supaya sejajar */
    .k-cols, .k-row { display:grid; grid-template-columns:minmax(0, 1fr) 120px 140px 120px 36px; align-items:center; gap:12px; }
    .k-cols { padding:0 20px 0 18px; font-size:11px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:var(--maroon); text-align:center; }
    .k-cols span:first-child { text-align:left; padding-left:6px; }

    .k-row { position:relative; margin-top:14px; padding:12px 20px 12px 18px; background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(83,26,53,.05); }
    .k-prod { display:flex; align-items:center; gap:16px; min-width:0; }
    .k-img { flex:none; width:94px; height:88px; border-radius:10px; background:var(--blush); display:grid; place-items:center; overflow:hidden; }
    .k-img img { width:100%; height:100%; object-fit:cover; }
    .k-img svg { width:48px; height:48px; }
    .k-info { min-width:0; }
    .k-name { margin:0; font-size:15px; font-weight:700; color:var(--ink); }
    .k-cat  { margin:3px 0 0; font-size:12px; color:var(--muted); }
    .k-warn { margin:3px 0 0; font-size:11px; font-weight:600; color:#c2410c; }
    .k-unit-m { display:none; }
    .k-unit, .k-sub { text-align:center; font-size:14px; font-weight:700; color:var(--ink); }
    .k-sub { color:var(--maroon-lt); }

    .k-qty { justify-self:center; display:inline-flex; align-items:center; background:var(--blush); border:1px solid var(--line); border-radius:99px; padding:2px; }
    .k-qty button { width:30px; height:30px; border:0; border-radius:50%; background:transparent; color:var(--maroon); font-size:16px; font-weight:700; cursor:pointer; }
    .k-qty button:hover:not(:disabled) { background:#fff; }
    .k-qty button:disabled { opacity:.35; cursor:not-allowed; }
    .k-qty span { min-width:32px; text-align:center; font-size:13px; font-weight:700; }

    .k-del { justify-self:center; width:34px; height:34px; border:0; border-radius:50%; background:transparent; color:#a8949b; cursor:pointer; display:grid; place-items:center; }
    .k-del:hover { background:var(--blush); color:var(--maroon); }

    .k-actions { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:20px; flex-wrap:wrap; }
    .btn-line { display:inline-flex; align-items:center; gap:8px; padding:11px 30px; border:2px solid var(--maroon); border-radius:99px; background:transparent; color:var(--maroon); font-size:13px; font-weight:700; text-decoration:none; }
    .btn-line:hover { background:var(--maroon); color:#fff; }
    .btn-soft { padding:12px 26px; border:0; border-radius:99px; background:var(--blush-dk); color:var(--maroon); font-size:13px; font-weight:700; cursor:pointer; }
    .btn-soft:hover { background:var(--rose); }

    /* Ringkasan */
    .k-sum { position:sticky; top:calc(var(--nav-h) + 16px); background:#fff; border-radius:18px; overflow:hidden; box-shadow:0 6px 24px rgba(83,26,53,.08); }
    .k-sum-h { padding:18px 22px; background:linear-gradient(90deg, #6a1730, #8a2543); color:#fff; font-family:'Playfair Display', serif; font-size:19px; font-weight:700; }
    .k-sum-b { padding:22px; }
    .k-line { display:flex; justify-content:space-between; gap:12px; margin-top:12px; font-size:13px; color:var(--muted); }
    .k-line b { color:var(--ink); }
    .k-div { height:1px; margin:18px 0 6px; background:var(--line); }
    .k-total-l { margin:14px 0 0; font-family:'Playfair Display', serif; font-size:16px; font-weight:700; }
    .k-total { margin:6px 0 18px; font-size:26px; font-weight:700; color:var(--maroon-lt); }
    .k-checkout { display:flex; justify-content:center; align-items:center; gap:8px; width:100%; padding:15px; border:0; border-radius:99px; background:linear-gradient(90deg, #6a1730, #8a2543); color:#fff; font-size:14px; font-weight:700; text-decoration:none; cursor:pointer; box-shadow:0 8px 20px rgba(106,23,48,.25); }
    .k-checkout[aria-disabled="true"] { opacity:.45; pointer-events:none; box-shadow:none; }

    /* Keadaan kosong / memuat */
    .k-empty { margin-top:14px; padding:56px 20px; text-align:center; background:#fff; border-radius:14px; color:var(--muted); font-size:14px; }
    .k-empty strong { display:block; margin-bottom:6px; font-size:17px; color:var(--maroon); font-family:'Playfair Display', serif; }
    .k-empty .btn-line { margin-top:18px; }

    @media (max-width:980px) {
        .k-grid { grid-template-columns:1fr; gap:20px; }
        .k-sum { position:static; }
    }
    @media (max-width:860px) {
        .k-head h1 { font-size:26px; }
        .k-cols { display:none; }
        .k-row { grid-template-columns:1fr auto; gap:12px 10px; padding:12px; }
        .k-prod { grid-column:1 / -1; padding-right:30px; gap:12px; }
        .k-img { width:76px; height:72px; }
        .k-unit { display:none; }
        .k-unit-m { display:block; margin:6px 0 0; font-size:13px; font-weight:700; color:var(--ink); }
        .k-qty { justify-self:start; }
        .k-sub { text-align:right; font-size:15px; }
        .k-del { position:absolute; top:6px; right:6px; width:30px; height:30px; }
        .k-actions { flex-direction:column-reverse; align-items:stretch; }
        .k-actions .btn-line, .k-actions .btn-soft { width:100%; justify-content:center; text-align:center; }
    }
</style>
@endpush

@section('content')
<div class="k-head">
    <h1>Keranjang Belanja</h1>
    <p>Periksa kembali item pilihan Anda sebelum melanjutkan ke pembayaran</p>
</div>

<div class="k-grid">
    {{-- DAFTAR ITEM --}}
    <section aria-label="Item keranjang">
        <div class="k-cols" aria-hidden="true">
            <span>Produk</span><span>Harga Satuan</span><span>Jumlah</span><span>Total</span><span></span>
        </div>

        <div id="kList"><div class="k-empty">Memuat keranjang...</div></div>

        <div class="k-actions" id="kActions" hidden>
            <a href="{{ route('pelanggan.katalog') }}" class="btn-line">&#9664; Lanjut Belanja</a>
            <button type="button" class="btn-soft" id="kClear">Kosongkan Keranjang</button>
        </div>
    </section>

    {{-- RINGKASAN --}}
    <aside class="k-sum" aria-label="Ringkasan pesanan">
        <div class="k-sum-h">Ringkasan Pesanan</div>
        <div class="k-sum-b">
            <div class="k-line"><span id="kSubLbl">Subtotal (0 Item)</span><b id="kSubtotal">Rp 0</b></div>
            <div class="k-line"><span>Estimasi Ongkir</span><b id="kOngkir">Rp 0</b></div>

            <div class="k-div" style="margin-bottom:0"></div>

            <p class="k-total-l">Total Pembayaran</p>
            <p class="k-total" id="kTotal">Rp 0</p>

            <a id="kCheckout" class="k-checkout" aria-disabled="true"
               href="{{ route('pelanggan.checkout') }}">
                Lanjut ke Checkout &#9654;
            </a>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const KEY = 'rf_cart';
    const ONGKIR = {{ (int) $ongkir }};
    const DATA_URL = @json(route('pelanggan.keranjang.data'));

    const $ = id => document.getElementById(id);
    const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const read = () => { try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; } };
    const write = c => { try { localStorage.setItem(KEY, JSON.stringify(c)); } catch (e) {} };

    let produk = {};   // id -> data produk dari server

    // Angka merah di ikon keranjang (navbar)
    function badge() {
        const n = Object.values(read()).reduce((a, b) => a + b, 0);
        const b = $('cartCount');
        if (!b) return;
        b.textContent = n > 99 ? '99+' : n;
        b.style.display = n ? 'grid' : 'none';
    }

    function el(tag, cls, text) {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        if (text !== undefined) e.textContent = text;
        return e;
    }

    const IKON_BUNGA = '<svg viewBox="0 0 32 32" fill="none" aria-hidden="true"><circle cx="16" cy="9" r="6" fill="#f4b6c6"/><circle cx="9" cy="17" r="6" fill="#e9a4b8"/><circle cx="23" cy="17" r="6" fill="#f4b6c6"/><circle cx="16" cy="23" r="6" fill="#e9a4b8"/><circle cx="16" cy="16" r="4" fill="#fff"/></svg>';
    const IKON_SAMPAH = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14M10 11v5M14 11v5"/></svg>';

    function barisItem(p, qty) {
        const row = el('div', 'k-row');

        const prod = el('div', 'k-prod');
        const img = el('div', 'k-img');
        if (p.foto_url) {
            const im = document.createElement('img');
            im.src = p.foto_url; im.alt = p.nama; im.loading = 'lazy';
            im.onerror = () => { img.innerHTML = IKON_BUNGA; };
            img.appendChild(im);
        } else {
            img.innerHTML = IKON_BUNGA;
        }
        const info = el('div', 'k-info');
        info.appendChild(el('p', 'k-name', p.nama));
        const ket = [p.kategori, p.sub].filter(Boolean).join(' \u2022 ');
        if (ket) info.appendChild(el('p', 'k-cat', ket));
        if (p.pakai_stok && p.stok <= 5) info.appendChild(el('p', 'k-warn', 'Stok tinggal ' + p.stok));
        info.appendChild(el('p', 'k-unit-m', rp(p.harga)));
        prod.append(img, info);

        const unit = el('div', 'k-unit', rp(p.harga));

        const qtyBox = el('div', 'k-qty');
        const min = el('button', '', '-'); min.type = 'button'; min.setAttribute('aria-label', 'Kurangi jumlah');
        min.disabled = qty <= 1;
        min.onclick = () => ubah(p.id, -1);
        const num = el('span', '', qty);
        const plus = el('button', '', '+'); plus.type = 'button'; plus.setAttribute('aria-label', 'Tambah jumlah');
        plus.onclick = () => ubah(p.id, +1);
        qtyBox.append(min, num, plus);

        const sub = el('div', 'k-sub', rp(p.harga * qty));

        const del = el('button', 'k-del'); del.type = 'button'; del.setAttribute('aria-label', 'Hapus ' + p.nama);
        del.innerHTML = IKON_SAMPAH;
        del.onclick = () => hapus(p.id);

        row.append(prod, unit, qtyBox, sub, del);
        return row;
    }

    function render() {
        const cart = read();
        const ids = Object.keys(cart).filter(id => produk[id]);
        const list = $('kList');
        list.replaceChildren();

        let totalItem = 0, subtotal = 0;

        if (!ids.length) {
            const kosong = el('div', 'k-empty');
            kosong.append(el('strong', '', 'Keranjang masih kosong'),
                          document.createTextNode('Yuk, pilih bunga favoritmu di katalog.'));
            const a = el('a', 'btn-line', 'Lihat Katalog');
            a.href = @json(route('pelanggan.katalog'));
            kosong.append(document.createElement('br'), a);
            list.appendChild(kosong);
            $('kActions').hidden = true;
        } else {
            ids.forEach(id => {
                const p = produk[id], q = cart[id];
                totalItem += q; subtotal += p.harga * q;
                list.appendChild(barisItem(p, q));
            });
            $('kActions').hidden = false;
        }

        const ongkir = totalItem ? ONGKIR : 0;
        $('kSubLbl').textContent = 'Subtotal (' + totalItem + ' Item)';
        $('kSubtotal').textContent = rp(subtotal);
        $('kOngkir').textContent = rp(ongkir);
        $('kTotal').textContent = rp(subtotal + ongkir);
        $('kCheckout').setAttribute('aria-disabled', totalItem ? 'false' : 'true');
        badge();
    }

    function ubah(id, d) {
        const cart = read(), p = produk[id];
        const n = (cart[id] || 0) + d;
        if (n < 1) return;
        // Batas jumlah hanya berlaku untuk produk yang dihitung per stok
        if (p.pakai_stok && n > p.stok) { RF.toast('Jumlah melebihi stok yang tersedia.'); return; }
        cart[id] = n; write(cart); render();
    }

    function hapus(id) {
        const cart = read();
        delete cart[id]; write(cart); render();
        RF.toast('Item dihapus dari keranjang.');
    }

    // Ambil data produk terbaru dari server, lalu sesuaikan isi keranjang
    async function muat() {
        const cart = read(), ids = Object.keys(cart);
        if (!ids.length) { produk = {}; render(); return; }

        try {
            const res = await fetch(DATA_URL + '?' + ids.map(i => 'ids[]=' + encodeURIComponent(i)).join('&'),
                                    { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(res.status);
            produk = Object.fromEntries((await res.json()).map(p => [p.id, p]));
        } catch (e) {
            const err = el('div', 'k-empty');
            err.append(el('strong', '', 'Gagal memuat keranjang'), document.createTextNode('Periksa koneksi lalu muat ulang halaman.'));
            $('kList').replaceChildren(err);
            return;
        }

        // Buang produk yang sudah dihapus/nonaktif/habis, dan batasi jumlah sesuai stok
        let berubah = false;
        ids.forEach(id => {
            const p = produk[id];
            if (!p || !p.tersedia) { delete cart[id]; berubah = true; }
            else if (p.pakai_stok && cart[id] > p.stok) { cart[id] = p.stok; berubah = true; }
        });
        if (berubah) { write(cart); RF.toast('Sebagian item disesuaikan dengan ketersediaan produk.'); }
        render();
    }

    $('kClear').addEventListener('click', () => {
        if (!confirm('Kosongkan semua item di keranjang?')) return;
        write({}); render();
    });

    $('kCheckout').addEventListener('click', e => {
        const href = e.currentTarget.getAttribute('href');
        if (!href || href === '#') { e.preventDefault(); RF.toast('Halaman checkout segera hadir.'); }
    });

    muat();
})();
</script>
@endpush
