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
    @media (max-width:900px){ .pr .stats { grid-template-columns:repeat(2,1fr); } }
</style>

<div class="pr">
    <div class="head">
        <div>
            <h1>Katalog Produk</h1>
            <p class="sub">Kelola buket, karangan bunga, harga, dan ketersediaan katalog toko.</p>
        </div>
        <div class="btns">
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
                <td>${p.pakai_stok ? p.stok + ' tangkai' : '<span class="muted">–</span>'}</td>
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

    // realtime: muat ulang otomatis tiap 10 detik & saat tab kembali aktif
    setInterval(() => { if (!document.hidden) load(); }, 10000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) load(); });

    if (FLASH) toast(FLASH);
    load();
})();
</script>
@endsection
