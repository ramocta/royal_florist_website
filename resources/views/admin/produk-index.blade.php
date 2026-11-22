@extends('layouts.admin')

@section('content')
@include('admin.produk-style')

<style>
    .pr .stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
    .pr .stat { background:#fff; border-radius:12px; padding:16px 18px; }
    .pr .stat span { font-size:12px; font-weight:600; }
    .pr .stat strong { display:block; font-size:28px; margin:4px 0; }
    .pr .stat small { font-size:11px; color:var(--muted); }
    .pr .stat small.ok { color:#1e7a34; font-weight:600; } .pr .stat small.bad { color:#c0261c; font-weight:600; }
    .pr .tools { display:flex; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
    .pr .tools input { width:340px; max-width:100%; } .pr .tools select { width:190px; } .pr .flt { display:flex; gap:10px; flex-wrap:wrap; }
    .pr .wrap { overflow-x:auto; }
    .pr table { width:100%; border-collapse:collapse; font-size:13px; min-width:760px; }
    .pr th { background:var(--blush); text-align:left; padding:12px 14px; font-size:12px; font-weight:600; }
    .pr td { padding:14px; border-bottom:1px solid var(--line); vertical-align:middle; }
    .pr tr:last-child td { border-bottom:0; }
    .pr .nm { display:flex; align-items:center; gap:10px; font-weight:700; }
    .pr .thumb { width:36px; height:36px; border-radius:8px; background:var(--blush-dk); object-fit:cover; flex:none; display:grid; place-items:center; color:var(--maroon); }
    .pr .muted { color:var(--muted); }
    .pr .badge { display:inline-block; padding:4px 14px; border-radius:99px; font-size:11px; font-weight:700; }
    .pr .badge.Tersedia { background:#e4f5e8; color:#1e7a34; } .pr .badge.Habis { background:#fde6e8; color:#c0261c; } .pr .badge.Nonaktif { background:#ececec; color:#555; }
    .pr .act a, .pr .act button { background:none; border:0; padding:0; font:inherit; font-weight:600; color:var(--ink); cursor:pointer; text-decoration:none; }
    .pr .act a:hover, .pr .act button:hover { color:var(--maroon); text-decoration:underline; }
    .pr .act i { color:var(--muted); font-style:normal; margin:0 6px; }
    .pr .empty { text-align:center; padding:48px 0; color:var(--muted); }
    .pr .foot { display:flex; justify-content:space-between; align-items:center; margin-top:16px; font-size:12px; color:var(--muted); flex-wrap:wrap; gap:10px; }
    .pr .pages { display:flex; gap:6px; }
    .pr .pages button { min-width:30px; height:30px; border:1px solid var(--line); background:#fff; border-radius:6px; cursor:pointer; font-size:12px; font-weight:600; color:var(--ink); }
    .pr .pages button.on { background:var(--maroon-dk); border-color:var(--maroon-dk); color:#fff; } .pr .pages button:disabled { opacity:.4; cursor:default; }
    dialog.kat { position:fixed; inset:0; margin:auto; width:min(640px,94vw); max-height:min(90vh,780px); padding:0; border:0; border-radius:22px; background:#fff; color:var(--ink,#3b1020); box-shadow:0 30px 80px rgba(60,10,25,.35); overflow:hidden; }
    dialog.kat[open] { display:flex; flex-direction:column; animation:katMasuk .22s ease-out; }
    dialog.kat::backdrop { background:rgba(48,10,22,.55); backdrop-filter:blur(3px); }
    @keyframes katMasuk { from { opacity:0; transform:translateY(14px) scale(.98); } to { opacity:1; transform:none; } }
    .kat * { box-sizing:border-box; }
    .kat-top { display:flex; align-items:center; gap:14px; padding:20px 24px; background:linear-gradient(135deg,var(--maroon-dk,#5c1229),var(--maroon,#8b1a3a)); color:#fff; }
    .kat-top-ico { flex:none; width:44px; height:44px; border-radius:12px; background:rgba(255,255,255,.16); display:grid; place-items:center; }
    .kat-top h2 { margin:0; font-size:18px; font-weight:700; line-height:1.2; }
    .kat-top p { margin:3px 0 0; font-size:12px; opacity:.85; line-height:1.4; }
    .kat-x { margin-left:auto; flex:none; width:34px; height:34px; border:0; border-radius:50%; background:rgba(255,255,255,.14); color:#fff; cursor:pointer; display:grid; place-items:center; transition:background .15s; }
    .kat-x:hover { background:rgba(255,255,255,.28); }
    .kat-body { overflow:auto; padding:20px 24px 24px; background:var(--blush,#fbeff2); display:grid; gap:16px; }
    .kat-card { background:#fff; border-radius:16px; padding:18px; box-shadow:0 1px 3px rgba(92,18,41,.08); }
    .kat-card h3 { margin:0 0 12px; font-size:14px; font-weight:700; display:flex; align-items:center; gap:8px; }
    .kat-count { background:var(--blush-dk,#f3d3da); color:var(--maroon,#8b1a3a); font-size:11px; font-weight:700; padding:2px 9px; border-radius:99px; }
    .kat-field { display:block; margin:0 0 14px; }
    .kat-field > span { display:block; font-size:12px; font-weight:600; margin-bottom:6px; }
    .kat .kat-field input[type=text] { display:block; width:100%; height:44px; padding:0 14px; border:1.5px solid var(--line,#e6cfd5); border-radius:12px; font:inherit; font-size:14px; background:#fff; color:inherit; outline:0; transition:border-color .15s, box-shadow .15s; }
    .kat .kat-field input[type=text]:focus { border-color:var(--maroon,#8b1a3a); box-shadow:0 0 0 3px rgba(139,26,58,.12); }
    .kat-pilih { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; }
    .kat-opt { position:relative; display:block; cursor:pointer; margin:0; }
    .kat .kat-opt input { position:absolute; opacity:0; pointer-events:none; width:0; height:0; margin:0; padding:0; border:0; }
    .kat-opt-box { display:flex; flex-direction:column; gap:6px; height:100%; padding:14px; border:1.5px solid var(--line,#e6cfd5); border-radius:14px; background:#fff; transition:border-color .15s, background .15s, box-shadow .15s, transform .15s; }
    .kat-opt:hover .kat-opt-box { border-color:var(--maroon,#8b1a3a); transform:translateY(-1px); }
    .kat-opt-ico { width:36px; height:36px; border-radius:10px; display:grid; place-items:center; background:var(--blush,#fbeff2); color:var(--maroon,#8b1a3a); }
    .kat-opt-box b { font-size:13px; font-weight:700; }
    .kat-opt-box small { font-size:11.5px; color:var(--muted,#8a6b73); line-height:1.45; }
    .kat-opt-tick { position:absolute; top:10px; right:10px; width:20px; height:20px; border-radius:50%; background:var(--maroon,#8b1a3a); color:#fff; display:grid; place-items:center; opacity:0; transform:scale(.6); transition:opacity .15s, transform .15s; }
    .kat .kat-opt input:checked + .kat-opt-box { border-color:var(--maroon,#8b1a3a); background:#fff8f9; box-shadow:0 0 0 3px rgba(139,26,58,.10); }
    .kat .kat-opt input:checked + .kat-opt-box .kat-opt-ico { background:var(--maroon,#8b1a3a); color:#fff; }
    .kat .kat-opt input:checked + .kat-opt-box .kat-opt-tick { opacity:1; transform:none; }
    .kat .kat-opt input:focus-visible + .kat-opt-box { outline:2px solid var(--maroon,#8b1a3a); outline-offset:2px; }
    .kat-err { margin:0 0 12px; padding:9px 12px; border-radius:10px; background:#fde6e8; color:#b3261e; font-size:12px; font-weight:600; }
    .kat-simpan { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; height:46px; border:0; border-radius:12px; background:var(--maroon-dk,#5c1229); color:#fff; font:inherit; font-size:14px; font-weight:700; cursor:pointer; transition:background .15s, transform .1s; }
    .kat-simpan:hover { background:var(--maroon,#8b1a3a); }
    .kat-simpan:active { transform:scale(.99); }
    .kat-simpan:disabled { opacity:.6; cursor:default; }
    #katList { list-style:none; margin:0; padding:0; display:grid; gap:8px; }
    .kat-item { display:flex; align-items:center; gap:12px; padding:10px 12px; border:1px solid var(--line,#e6cfd5); border-radius:14px; background:#fff; transition:box-shadow .15s, border-color .15s; }
    .kat-item:hover { border-color:var(--blush-dk,#f3d3da); box-shadow:0 4px 14px rgba(92,18,41,.08); }
    .kat-item-ico { flex:none; width:36px; height:36px; border-radius:10px; background:var(--blush,#fbeff2); color:var(--maroon,#8b1a3a); display:grid; place-items:center; }
    .kat-item-main { min-width:0; flex:1; }
    .kat-item-main strong { display:block; font-size:13.5px; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .kat-item-main small { font-size:11.5px; color:var(--muted,#8a6b73); }
    .kat-badge { flex:none; font-size:10.5px; font-weight:700; padding:3px 10px; border-radius:99px; }
    .kat-b-bawaan { background:#ececec; color:#555; }
    .kat-b-stok { background:#fde6ec; color:#a3173f; }
    .kat-b-ada { background:#e4f5e8; color:#1e7a34; }
    .kat-item-act { flex:none; display:flex; gap:4px; }
    .kat-icon { width:32px; height:32px; border:0; border-radius:9px; background:transparent; color:var(--muted,#8a6b73); cursor:pointer; display:grid; place-items:center; transition:background .15s, color .15s; }
    .kat-icon:hover:not(:disabled) { background:var(--blush,#fbeff2); color:var(--maroon,#8b1a3a); }
    .kat-icon.bahaya:hover:not(:disabled) { background:#fde6e8; color:#b3261e; }
    .kat-icon:disabled { opacity:.35; cursor:not-allowed; }
.kat-hint { display:block; margin-top:6px; font-size:11.5px; color:var(--muted,#8a6b73); }
    .kat-field > span em { font-style:normal; font-weight:400; color:var(--muted,#8a6b73); }
    .kat-jenis-in { display:flex; gap:8px; }
    .kat-jenis-in button { flex:none; width:44px; height:44px; border:0; border-radius:12px; background:var(--maroon,#8b1a3a); color:#fff; font-size:20px; font-weight:700; cursor:pointer; transition:background .15s; }
    .kat-jenis-in button:hover { background:var(--maroon-dk,#5c1229); }
    .kat-chips { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
    .kat-chips:empty { display:none; }
    .kat-chip { display:inline-flex; align-items:center; gap:6px; padding:5px 6px 5px 12px; border-radius:99px; background:var(--blush-dk,#f3d3da); color:var(--maroon-dk,#5c1229); font-size:12px; font-weight:600; }
    .kat-chip button { width:18px; height:18px; border:0; border-radius:50%; background:rgba(92,18,41,.15); color:inherit; cursor:pointer; line-height:1; font-size:13px; }
    .kat-chip button:hover { background:rgba(92,18,41,.3); }
    @media (max-width:560px){ .kat-pilih { grid-template-columns:1fr; } .kat-item { flex-wrap:wrap; } }
    @media (max-width:900px){ .pr .stats { grid-template-columns:repeat(2,1fr); } }
</style>

<div class="pr">
    <div class="head">
        <div>
            <h1>Katalog Produk</h1>
            <p class="sub">Kelola buket, karangan bunga, harga, dan ketersediaan katalog toko.</p>
        </div>
        <div class="btns">
            @if (Route::has('admin.kategori.store'))
                <button type="button" class="btn" id="btnKategori">Kelola Kategori</button>
            @endif
            <a class="btn" href="{{ route('admin.produk.export') }}">Export Data</a>
            <a class="btn primary" href="{{ route('admin.produk.create') }}">+ Produk Baru</a>
        </div>
    </div>

    <div class="stats">
        <div class="stat"><span>Total Produk</span><strong id="stTotal">–</strong><small>Katalog aktif &amp; draft</small></div>
        <div class="stat"><span>Buket Bunga</span><strong id="stBuket">–</strong><small class="ok">Kategori terpopuler</small></div>
        <div class="stat"><span>Bunga Papan / Stand</span><strong id="stPapan">–</strong><small>Acara &amp; duka cita</small></div>
        <div class="stat"><span>Produk Nonaktif/Habis</span><strong id="stNon">–</strong><small class="bad">Perlu pembaruan stok</small></div>
    </div>

    <div class="card">
        <div class="tools">
            <input type="search" id="fSearch" placeholder="Cari nama produk / kode SKU..." aria-label="Cari produk">
            <div class="flt">
                <select id="fCat" aria-label="Filter kategori">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c['id'] }}">{{ $c['nama'] }}</option>
                    @endforeach
                </select>
                <select id="fSub" aria-label="Filter jenis atau ukuran" style="display:none"></select>
            </div>
        </div>

        <div class="wrap">
            <table>
                <thead><tr><th>No</th><th>SKU</th><th>Nama Produk</th><th>Kategori</th><th>Harga Jual</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody id="rows"><tr><td colspan="8" class="empty">Memuat data…</td></tr></tbody>
            </table>
        </div>

        <div class="foot"><span id="info"></span><div class="pages" id="pages"></div></div>
    </div>

    @if (Route::has('admin.kategori.store'))
    <dialog id="dlgKategori" class="kat" aria-labelledby="katJudul">
        <div class="kat-top">
            <span class="kat-top-ico" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.2"/></svg></span>
            <div>
                <h2 id="katJudul">Kelola Kategori</h2>
                <p>Kategori baru langsung tampil di katalog pelanggan dan aplikasi karyawan.</p>
            </div>
            <button type="button" class="kat-x" id="katTutup" aria-label="Tutup"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
        </div>
        <div class="kat-body">
            <section class="kat-card">
                <h3>Tambah kategori baru</h3>
                <form id="formKategori" novalidate>
                    <label class="kat-field"><span>Nama kategori</span><input type="text" id="katNama" maxlength="50" placeholder="Contoh: Parcel, Bunga Potong" autocomplete="off"></label>
                    <div class="kat-pilih" role="radiogroup" aria-label="Cara menghitung produk">
                        <label class="kat-opt">
                            <input type="radio" name="katSistem" value="0" checked>
                            <span class="kat-opt-box">
                                <span class="kat-opt-tick"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                <span class="kat-opt-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"/><path d="m22 4-10 10-3-3"/></svg></span>
                                <b>Keterangan ketersediaan</b>
                                <small>Cukup ditandai tersedia atau tidak tersedia. Cocok untuk produk pesanan khusus.</small>
                            </span>
                        </label>
                        <label class="kat-opt">
                            <input type="radio" name="katSistem" value="1">
                            <span class="kat-opt-box">
                                <span class="kat-opt-tick"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                <span class="kat-opt-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg></span>
                                <b>Pakai stok</b>
                                <small>Dihitung per jumlah. Cocok untuk barang yang jumlahnya perlu dipantau.</small>
                            </span>
                        </label>
                    </div>
                    <div class="kat-field">
                        <span>Jenis <em>(opsional)</em></span>
                        <div class="kat-jenis-in">
                            <input type="text" id="katJenis" maxlength="50" placeholder="Contoh: Mawar, Lily" autocomplete="off">
                            <button type="button" id="katJenisTambah" aria-label="Tambah jenis">+</button>
                        </div>
                        <small class="kat-hint">Jika diisi, kategori ini tampil sebagai dropdown jenis saat menambah produk.</small>
                        <div class="kat-chips" id="katChips"></div>
                    </div>
                    <p class="kat-err" id="katErr" hidden></p>
                    <button type="submit" class="kat-simpan" id="katSimpan"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>Tambah Kategori</button>
                </form>
            </section>
            <section class="kat-card">
                <h3>Kategori saat ini <span class="kat-count" id="katJumlah">0</span></h3>
                <ul id="katList"></ul>
            </section>
        </div>
    </dialog>
    @endif

    <div class="toast" id="toast" role="status" aria-live="polite"></div>
</div>

<script>
(() => {
    const URL_DATA = @json(route('admin.produk.data'));
    const URL_BASE = @json(url('admin/produk'));
    const CSRF = @json(csrf_token());
    const FLASH = @json(session('success'));

    const $ = s => document.querySelector(s);
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const CATS = @json($categories);
    const state = { page: 1, q: '', category: '', sub: '' };

    function toast(msg, err = false) {
        const t = $('#toast');
        t.textContent = msg; t.classList.toggle('error', err); t.classList.add('show');
        clearTimeout(toast.t); toast.t = setTimeout(() => t.classList.remove('show'), 3000);
    }

    async function load() {
        const params = new URLSearchParams(state);
        try {
            const res = await fetch(URL_DATA + '?' + params, { headers: { Accept: 'application/json' } });
            const j = await res.json();
            if (!j.items.length && state.page > 1) { state.page = Math.max(1, j.pagination.last_page); return load(); }
            render(j);
        } catch (e) {
            $('#rows').innerHTML = '<tr><td colspan="8" class="empty">Gagal memuat data. Muat ulang halaman.</td></tr>';
        }
    }

    function render({ items, pagination: pg, stats }) {
        $('#stTotal').textContent = stats.total;  $('#stBuket').textContent = stats.buket;
        $('#stPapan').textContent = stats.papan;  $('#stNon').textContent = stats.nonaktif;

        $('#rows').innerHTML = items.length ? items.map((p, i) => `
            <tr>
                <td>${pg.from + i}</td>
                <td>${esc(p.sku)}</td>
                <td><div class="nm">${p.foto_url ? `<img class="thumb" src="${esc(p.foto_url)}" alt="">` : '<span class="thumb" aria-hidden="true">✿</span>'}<span>${esc(p.nama)}</span></div></td>
                <td class="muted">${esc(p.kategori)}${p.sub ? `<br><small>${esc(p.sub)}</small>` : ''}</td>
                <td><strong>${rupiah(p.harga)}</strong></td>
                <td>${p.pakai_stok ? p.stok + ' ' + esc(p.satuan) : '<span class="muted">–</span>'}</td>
                <td><span class="badge ${esc(p.status)}">${esc(p.status)}</span></td>
                <td class="act"><a href="${URL_BASE}/${p.id}/edit">Edit</a><i>|</i><button type="button" data-del="${p.id}" data-name="${esc(p.nama)}">Hapus</button></td>
            </tr>`).join('')
            : `<tr><td colspan="8" class="empty">${state.q || state.category || state.sub ? 'Tidak ada produk yang cocok.' : 'Belum ada produk. Klik “+ Produk Baru” untuk menambahkan.'}</td></tr>`;

        $('#info').textContent = pg.total ? `Menampilkan ${pg.from}–${pg.to} dari ${pg.total} produk` : 'Tidak ada produk';

        const nums = [];
        for (let i = Math.max(1, pg.current_page - 2); i <= Math.min(pg.last_page, pg.current_page + 2); i++) nums.push(i);
        $('#pages').innerHTML = pg.last_page <= 1 ? '' :
            `<button data-page="${pg.current_page - 1}" ${pg.current_page === 1 ? 'disabled' : ''} aria-label="Sebelumnya">‹</button>` +
            nums.map(n => `<button data-page="${n}" class="${n === pg.current_page ? 'on' : ''}">${n}</button>`).join('') +
            `<button data-page="${pg.current_page + 1}" ${pg.current_page === pg.last_page ? 'disabled' : ''} aria-label="Berikutnya">›</button>`;
    }

    $('#rows').addEventListener('click', async e => {
        const b = e.target.closest('[data-del]');
        if (!b || !confirm(`Hapus produk "${b.dataset.name}"? Tindakan ini tidak bisa dibatalkan.`)) return;
        try {
            const res = await fetch(`${URL_BASE}/${b.dataset.del}`, { method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF } });
            const j = await res.json();
            toast(j.message, !res.ok);
            load();
        } catch (err) { toast('Gagal menghapus produk.', true); }
    });

    $('#pages').addEventListener('click', e => {
        const b = e.target.closest('[data-page]');
        if (b && !b.disabled) { state.page = +b.dataset.page; load(); }
    });

    let t;
    $('#fSearch').addEventListener('input', e => { clearTimeout(t); t = setTimeout(() => { state.q = e.target.value.trim(); state.page = 1; load(); }, 300); });
    $('#fCat').addEventListener('change', e => {
        state.category = e.target.value; state.sub = ''; state.page = 1;
        const c = CATS.find(x => String(x.id) === state.category), sel = $('#fSub');
        if (c && c.subs.length) {
            sel.innerHTML = `<option value="">Semua ${c.sub_label.toLowerCase()}</option>` + c.subs.map(s => `<option value="${s.id}">${esc(s.nama)}</option>`).join('');
            sel.style.display = '';
        } else { sel.style.display = 'none'; sel.innerHTML = ''; }
        load();
    });
    $('#fSub').addEventListener('change', e => { state.sub = e.target.value; state.page = 1; load(); });

    // Realtime produk: polling 10 detik & saat tab kembali aktif
    setInterval(() => { if (!document.hidden) load(); }, 10000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) load(); });

    @if (Route::has('admin.kategori.store'))
    const KAT_URL = @json(route('admin.kategori.store'));
    const KAT_BASE = @json(url('admin/kategori'));
    const dlg = $('#dlgKategori');
    const katErr = $('#katErr');
    const IKON = {
        tag: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.2"/></svg>',
        ubah: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>',
        hapus: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg>',
        kunci: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
    };

    async function kirimKategori(url, method, body) {
        const res = await fetch(url, {
            method,
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: body ? JSON.stringify(body) : undefined,
        });
        const j = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(j.errors ? Object.values(j.errors)[0][0] : (j.message || 'Gagal menyimpan.'));
        return j;
    }

    const KAT_DATA = @json(route('admin.kategori.data'));
    const MAKS_JENIS = 20;
    let jenis = [];

    function tampilChips() {
        $('#katChips').innerHTML = jenis.map((j, i) =>
            `<span class="kat-chip">${esc(j)}<button type="button" data-lepas="${i}" aria-label="Hapus ${esc(j)}">×</button></span>`).join('');
    }

    function tambahJenis() {
        const input = $('#katJenis'), nilai = input.value.replace(/\s+/g, ' ').trim();
        if (!nilai) return;
        if (jenis.length >= MAKS_JENIS) { katErr.textContent = `Jenis maksimal ${MAKS_JENIS} per kategori.`; katErr.hidden = false; return; }
        if (!jenis.some(j => j.toLowerCase() === nilai.toLowerCase())) jenis.push(nilai);
        input.value = ''; katErr.hidden = true; tampilChips(); input.focus();
    }

    async function segarkanKategori() {
        try {
            const res = await fetch(KAT_DATA, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const baru = await res.json();
            if (JSON.stringify(baru) === JSON.stringify(CATS)) return;

            CATS.splice(0, CATS.length, ...baru);
            const sel = $('#fCat'), dipilih = sel.value;
            sel.innerHTML = '<option value="">Semua Kategori</option>' + CATS.map(c => `<option value="${c.id}">${esc(c.nama)}</option>`).join('');
            if (CATS.some(c => String(c.id) === dipilih)) sel.value = dipilih;
            else if (dipilih) { state.category = ''; state.sub = ''; $('#fSub').style.display = 'none'; load(); }
            if (dlg.open) tampilKategori();
        } catch (e) {}
    }

    async function selesai(pesan) {
        toast(pesan);
        await segarkanKategori();
    }

    setInterval(() => { if (!document.hidden) segarkanKategori(); }, 10000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) segarkanKategori(); });

    function tampilKategori() {
        $('#katJumlah').textContent = CATS.length;
        $('#katList').innerHTML = CATS.map(c => {
            const [label, kelas] = c.bawaan ? ['Bawaan', 'kat-b-bawaan'] : (c.pakai_stok ? ['Pakai stok', 'kat-b-stok'] : ['Ketersediaan', 'kat-b-ada']);
            const aksi = c.bawaan
                ? `<span class="kat-icon" title="Kategori bawaan tidak bisa diubah" style="cursor:default">${IKON.kunci}</span>`
                : `<button type="button" class="kat-icon" data-ubah="${c.id}" title="Ubah nama" aria-label="Ubah nama ${esc(c.nama)}">${IKON.ubah}</button>
                   <button type="button" class="kat-icon bahaya" data-hapus="${c.id}" ${c.jumlah_produk ? 'disabled' : ''} title="${c.jumlah_produk ? 'Masih punya produk' : 'Hapus kategori'}" aria-label="Hapus ${esc(c.nama)}">${IKON.hapus}</button>`;
            return `<li class="kat-item">
                <span class="kat-item-ico">${IKON.tag}</span>
                <div class="kat-item-main"><strong>${esc(c.nama)}</strong><small>${c.jumlah_produk} produk</small></div>
                <span class="kat-badge ${kelas}">${label}</span>
                <div class="kat-item-act">${aksi}</div>
            </li>`;
        }).join('');
    }

    $('#btnKategori').addEventListener('click', () => { tampilKategori(); katErr.hidden = true; dlg.showModal(); $('#katNama').focus(); });
    $('#katJenisTambah').addEventListener('click', tambahJenis);
    $('#katJenis').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); tambahJenis(); } });
    $('#katChips').addEventListener('click', e => {
        const b = e.target.closest('[data-lepas]');
        if (b) { jenis.splice(+b.dataset.lepas, 1); tampilChips(); }
    });
    $('#katTutup').addEventListener('click', () => dlg.close());
    dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close(); });

    $('#formKategori').addEventListener('submit', async e => {
        e.preventDefault();
        katErr.hidden = true;
        const tombol = $('#katSimpan');
        tombol.disabled = true;
        try {
            tambahJenis();
            const pakaiStok = document.querySelector('input[name=katSistem]:checked').value;
            const j = await kirimKategori(KAT_URL, 'POST', { nama: $('#katNama').value, pakai_stok: pakaiStok, jenis });
            e.target.reset(); jenis = []; tampilChips();
            await selesai(j.message);
            $('#katNama').focus();
        } catch (x) { katErr.textContent = x.message; katErr.hidden = false; }
        tombol.disabled = false;
    });

    $('#katList').addEventListener('click', async e => {
        const ubah = e.target.closest('[data-ubah]'), hapus = e.target.closest('[data-hapus]');
        const id = ubah ? ubah.dataset.ubah : (hapus ? hapus.dataset.hapus : null);
        if (!id) return;
        const c = CATS.find(x => String(x.id) === id);
        katErr.hidden = true;
        try {
            if (ubah) {
                const nama = prompt('Nama kategori baru:', c.nama);
                if (nama === null) return;
                await selesai((await kirimKategori(`${KAT_BASE}/${id}`, 'PUT', { nama })).message);
            } else if (confirm(`Hapus kategori "${c.nama}"?`)) {
                await selesai((await kirimKategori(`${KAT_BASE}/${id}`, 'DELETE')).message);
            }
        } catch (x) { katErr.textContent = x.message; katErr.hidden = false; $('#katNama').scrollIntoView({ block: 'nearest' }); }
    });
    @endif

    if (FLASH) toast(FLASH);
    load();
})();
</script>
@endsection
