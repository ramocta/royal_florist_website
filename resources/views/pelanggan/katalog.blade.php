@extends('layouts.pelanggan')

@section('title', 'Katalog Produk | Royal Florist')

@push('head')
<style>
    .hero { position:relative; overflow:hidden; border-radius:28px; padding:44px 44px 40px; margin-bottom:8px; color:#fff; background:linear-gradient(135deg, #8a2543 0%, #5b1530 100%); box-shadow:0 22px 50px rgba(83,26,53,.28); }
    .hero::before, .hero::after { content:""; position:absolute; border-radius:50%; pointer-events:none; }
    .hero::before { width:340px; height:340px; right:-90px; top:-140px; background:radial-gradient(circle, rgba(244,182,198,.34), transparent 70%); }
    .hero::after { width:220px; height:220px; left:38%; bottom:-120px; background:radial-gradient(circle, rgba(255,255,255,.12), transparent 70%); }
    .hero > * { position:relative; z-index:1; }
    .hero h1 { font-family:'Playfair Display', serif; font-size:44px; line-height:1.1; margin:0 0 10px; }
    .hero p { margin:0 0 26px; max-width:480px; font-size:15px; line-height:1.6; color:rgba(255,255,255,.82); }
    .tools { max-width:560px; }
    .field { position:relative; }
    .field svg { position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--muted); pointer-events:none; }
    .field input { width:100%; height:48px; border:0; background:#fff; border-radius:99px; font:inherit; font-size:14px; color:var(--ink); box-shadow:0 6px 18px rgba(0,0,0,.12); padding:0 18px 0 46px; }
    .field input:focus-visible { outline:2px solid #fff; outline-offset:2px; }

    .filters { position:sticky; top:var(--nav-h); z-index:30; margin:0 -24px; padding:14px 24px 10px; background:rgba(252,243,245,.9); backdrop-filter:blur(12px); }
    .chips, .subchips { display:flex; gap:10px; }
    .chip { flex:none; display:inline-flex; align-items:center; height:42px; padding:0 20px; border:1px solid var(--line); background:#fff; color:var(--ink); border-radius:99px; font-size:14px; font-weight:600; cursor:pointer; transition:all .15s; white-space:nowrap; }
    .chip:hover { border-color:var(--maroon); color:var(--maroon); }
    .chip.on { background:linear-gradient(135deg, var(--maroon-lt), var(--maroon-dk)); border-color:transparent; color:#fff; box-shadow:0 8px 20px rgba(83,26,53,.26); }
    .subchips { margin-top:10px; }
    .subchips:empty { display:none; }
    .sub { height:34px; padding:0 16px; font-size:13px; font-weight:500; background:transparent; }
    .sub.on { background:var(--maroon); box-shadow:none; }

    .countbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin:14px 0 16px; }
    .count { display:flex; align-items:center; gap:8px; min-width:0; font-size:13px; color:var(--muted); }
    .sort { position:relative; flex:none; }
    .sortbtn { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 14px 0 16px; border:1px solid var(--line); border-radius:99px; background:#fff; color:var(--ink); font-size:13px; cursor:pointer; box-shadow:0 2px 8px rgba(123,26,51,.05); transition:border-color .15s; }
    .sortbtn:hover, .sort.open .sortbtn { border-color:var(--maroon); }
    .sortbtn i { font-style:normal; color:var(--muted); }
    .sortbtn b { font-weight:600; }
    .sortbtn .chev { transition:transform .2s; }
    .sort.open .chev { transform:rotate(180deg); }
    .sortmenu { position:absolute; right:0; top:calc(100% + 8px); z-index:40; min-width:220px; margin:0; padding:6px; list-style:none; background:#fff; border:1px solid var(--line); border-radius:16px; box-shadow:0 18px 40px rgba(83,26,53,.18); opacity:0; visibility:hidden; transform:translateY(-6px); transition:opacity .15s, transform .15s, visibility .15s; }
    .sort.open .sortmenu { opacity:1; visibility:visible; transform:none; }
    .sorthead { display:none; }
    .sortopt { display:flex; align-items:center; justify-content:space-between; gap:12px; width:100%; padding:11px 14px; border:0; border-radius:10px; background:none; color:var(--ink); font-size:14px; text-align:left; cursor:pointer; }
    .sortopt:hover { background:var(--blush); }
    .sortopt svg { opacity:0; color:var(--maroon); }
    .sortopt[aria-selected="true"] { background:var(--blush); color:var(--maroon); font-weight:600; }
    .sortopt[aria-selected="true"] svg { opacity:1; }
    .sortback { display:none; }
    .live { width:8px; height:8px; border-radius:50%; background:#2e9e4d; animation:pulse 2s infinite; }
    @keyframes pulse { 0% { box-shadow:0 0 0 0 rgba(46,158,77,.5); } 70%, 100% { box-shadow:0 0 0 8px rgba(46,158,77,0); } }

    .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:22px; }
    .card { background:#fff; border-radius:22px; overflow:hidden; display:flex; flex-direction:column; text-decoration:none; color:inherit; box-shadow:0 4px 18px rgba(123,26,51,.07); transition:transform .2s, box-shadow .2s; }
    .media { position:relative; margin:10px; border-radius:16px; aspect-ratio:1/1; overflow:hidden; background:radial-gradient(circle at 30% 20%, #fff3f6, #f6dfe4 70%); display:grid; place-items:center; }
    .media img { width:100%; height:100%; object-fit:cover; transition:transform .4s; }
    .tag { position:absolute; top:12px; left:12px; z-index:2; background:var(--maroon); color:#fff; font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; padding:6px 12px; border-radius:99px; }
    .low { position:absolute; left:12px; bottom:12px; z-index:2; background:#fff4d6; color:#8a5a00; font-size:11px; font-weight:600; padding:5px 10px; border-radius:99px; }
    .sold { position:absolute; inset:0; z-index:1; background:rgba(255,255,255,.64); display:grid; place-items:center; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--maroon-dk); font-size:14px; }
    .card:focus-visible { outline:2px solid var(--maroon); outline-offset:3px; }
    .card.off .media img { filter:grayscale(.6); }
    .body { padding:6px 20px 20px; display:flex; flex-direction:column; flex:1; }
    .body h3 { margin:6px 0 4px; font-size:17px; font-weight:700; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .meta { margin:0; font-size:13px; color:var(--maroon-lt); font-weight:500; }
    .buy { margin-top:auto; padding-top:14px; display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .add { flex:none; display:inline-flex; align-items:center; justify-content:center; gap:6px; height:40px; padding:0 14px; border:0; border-radius:99px; background:linear-gradient(135deg, var(--maroon-lt), var(--maroon-dk)); color:#fff; font:inherit; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 6px 14px rgba(83,26,53,.22); transition:transform .15s, box-shadow .15s, opacity .15s; }
    .add:hover { transform:translateY(-1px); box-shadow:0 10px 20px rgba(83,26,53,.3); }
    .add:active { transform:scale(.96); }
    .add:disabled { background:#d9c9ce; box-shadow:none; cursor:not-allowed; transform:none; }
    .add:focus-visible { outline:2px solid var(--maroon); outline-offset:3px; }
    .price { white-space:nowrap; font-size:20px; font-weight:700; color:var(--maroon); letter-spacing:-.01em; }
    @media (hover:hover) {
        .card:hover { transform:translateY(-4px); box-shadow:0 18px 40px rgba(123,26,51,.16); }
        .card:hover .media img { transform:scale(1.05); }
    }

    .skel { aspect-ratio:3/4; border-radius:22px; background:linear-gradient(100deg, #f3e3e7 30%, #fbf2f4 50%, #f3e3e7 70%); background-size:200% 100%; animation:sh 1.3s infinite linear; }
    @keyframes sh { to { background-position:-200% 0; } }
    .empty { grid-column:1/-1; text-align:center; padding:56px 20px; background:#fff; border-radius:22px; color:var(--muted); font-size:14px; }
    .empty strong { display:block; color:var(--ink); font-size:18px; margin:12px 0 4px; }

    .pages { display:flex; justify-content:center; gap:8px; margin-top:34px; }
    .pages button { min-width:42px; height:42px; border:1px solid var(--line); background:#fff; border-radius:12px; font-size:14px; font-weight:600; color:var(--ink); cursor:pointer; }
    .pages button.on { background:var(--maroon-dk); border-color:var(--maroon-dk); color:#fff; }
    .pages button:disabled { opacity:.4; cursor:default; }

    @media (max-width:860px) {
        .hero { padding:32px 28px 28px; }
        .hero h1 { font-size:36px; }
        .filters { margin:0 -16px; padding:12px 16px 8px; }
    }
    @media (max-width:640px) {
        .hero { border-radius:22px; padding:24px 20px 20px; }
        .hero h1 { font-size:28px; margin-bottom:6px; }
        .hero p { font-size:13px; margin-bottom:18px; }
        .field input { height:46px; }
        .chips, .subchips { overflow-x:auto; scrollbar-width:none; margin:0 -16px; padding:0 16px 4px; }
        .chips::-webkit-scrollbar, .subchips::-webkit-scrollbar { display:none; }
        .chip { height:40px; padding:0 18px; font-size:13px; }
        .sub { height:32px; padding:0 14px; font-size:12px; }
        .sortbtn i { display:none; }
        .sortback { position:fixed; inset:0; z-index:85; background:rgba(58,16,32,.45); opacity:0; visibility:hidden; transition:opacity .2s, visibility .2s; display:block; }
        .sort.open .sortback { opacity:1; visibility:visible; }
        .sortmenu { position:fixed; left:0; right:0; top:auto; bottom:0; z-index:90; min-width:0; padding:10px 16px calc(18px + env(safe-area-inset-bottom)); border:0; border-radius:24px 24px 0 0; transform:translateY(100%); opacity:1; transition:transform .25s, visibility .25s; }
        .sort.open .sortmenu { transform:none; }
        .sorthead { display:block; padding:14px 6px 10px; font-size:15px; font-weight:700; }
        .sorthead::before { content:""; display:block; width:40px; height:4px; margin:0 auto 14px; border-radius:99px; background:var(--line); }
        .sortopt { padding:14px; font-size:15px; }

        .grid { grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; }
        .card { border-radius:18px; }
        .media { margin:6px; border-radius:13px; }
        .tag { top:8px; left:8px; font-size:9px; padding:4px 9px; }
        .low { left:8px; bottom:8px; font-size:10px; padding:4px 8px; }
        .body { padding:4px 12px 12px; }
        .body h3 { font-size:14px; margin-top:4px; }
        .meta { font-size:12px; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; }
        .buy { padding-top:10px; }
        .price { font-size:16px; }
        .add { height:36px; padding:0 12px; font-size:12px; }
        .add span { display:none; }
        .pages { gap:6px; margin-top:26px; }
        .pages button { min-width:38px; height:38px; }
    }
</style>
@endpush

@section('content')
    <section class="hero">
        <h1>Katalog Produk</h1>
        <p>Temukan pilihan buket, papan bunga, dan hantaran pernikahan terbaik untuk momen spesial Anda.</p>
        <div class="tools">
            <div class="field">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" id="q" placeholder="Cari nama produk..." aria-label="Cari produk" autocomplete="off">
            </div>
        </div>
    </section>

    <div class="filters">
        <div class="chips" id="chips" aria-label="Kategori"></div>
        <div class="subchips" id="subchips" aria-label="Jenis atau ukuran"></div>
    </div>

    <div class="countbar">
        <div class="count"><span class="live" title="Diperbarui otomatis"></span><span id="count" aria-live="polite">Memuat produk…</span></div>
        <div class="sort" id="sort">
            <button type="button" class="sortbtn" id="sortBtn" aria-haspopup="listbox" aria-expanded="false">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                <span><i>Urutkan:</i> <b id="sortLabel">Terbaru</b></span>
                <svg class="chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="sortback" id="sortBack"></div>
            <ul class="sortmenu" id="sortMenu" role="listbox" aria-label="Urutkan produk">
                <li class="sorthead">Urutkan produk</li>
                @foreach (['terbaru' => 'Terbaru', 'termurah' => 'Harga terendah', 'termahal' => 'Harga tertinggi', 'nama' => 'Nama A–Z'] as $v => $label)
                    <li><button type="button" class="sortopt" role="option" data-sort="{{ $v }}" data-label="{{ $label }}" aria-selected="{{ $v === 'terbaru' ? 'true' : 'false' }}">{{ $label }}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></button></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="grid" id="grid">
        @for ($i = 0; $i < 6; $i++) <div class="skel"></div> @endfor
    </div>

    <div class="pages" id="pages"></div>
@endsection

@push('scripts')
<script>
(() => {
    const URL_DATA = @json(route('pelanggan.katalog.data'));
    const CATS = @json($categories);
    const state = { page: 1, q: '', category: '', sub: '', sort: 'terbaru' };
    const $ = s => document.querySelector(s);
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

    const PH = `<svg width="30%" viewBox="0 0 64 64" fill="none" aria-hidden="true"><circle cx="32" cy="20" r="11" fill="#e9a4b8"/><circle cx="20" cy="32" r="11" fill="#f1b9c9"/><circle cx="44" cy="32" r="11" fill="#f1b9c9"/><circle cx="32" cy="32" r="8" fill="#7b1a33" opacity=".85"/><path d="M32 44v14M32 52c-6 0-10-3-11-7M32 52c6 0 10-3 11-7" stroke="#9b2c4a" stroke-width="3" stroke-linecap="round"/></svg>`;
    window.RF_PH = PH;

    let lastSig = '';

    function renderChips() {
        $('#chips').innerHTML =
            `<button class="chip ${state.category === '' ? 'on' : ''}" data-cat="">Semua</button>` +
            CATS.map(c => `<button class="chip ${String(c.id) === state.category ? 'on' : ''}" data-cat="${c.id}">${esc(c.nama)}</button>`).join('');

        const c = CATS.find(x => String(x.id) === state.category);
        $('#subchips').innerHTML = c && c.subs.length
            ? `<button class="chip sub ${state.sub === '' ? 'on' : ''}" data-sub="">Semua ${esc(c.sub_label.toLowerCase())}</button>` +
              c.subs.map(s => `<button class="chip sub ${String(s.id) === state.sub ? 'on' : ''}" data-sub="${s.id}">${esc(s.nama)}</button>`).join('')
            : '';
    }

    function card(p) {
        const low = p.pakai_stok && p.tersedia && p.stok <= 5;
        return `
        <article class="card ${p.tersedia ? '' : 'off'}" data-id="${p.id}">
            <div class="media">
                <span class="tag">${esc(p.kategori)}</span>
                ${low ? `<span class="low">Sisa ${p.stok} tangkai</span>` : ''}
                ${p.foto_url ? `<img src="${esc(p.foto_url)}" alt="${esc(p.nama)}" loading="lazy" onerror="this.outerHTML=window.RF_PH">` : PH}
                ${p.tersedia ? '' : '<span class="sold">Habis</span>'}
            </div>
            <div class="body">
                <h3>${esc(p.nama)}</h3>
                <p class="meta">${esc(p.sub || p.kategori)}</p>
                <div class="buy">
                    <span class="price">${rupiah(p.harga)}</span>
                    <button type="button" class="add" data-add="${p.id}" ${p.tersedia ? '' : 'disabled'} aria-label="Tambah ${esc(p.nama)} ke keranjang">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                        <span>${p.tersedia ? 'Keranjang' : 'Habis'}</span>
                    </button>
                </div>
            </div>
        </article>`;
    }

    const PRODUK = {};   // id -> data produk yang sedang tampil (untuk cek stok)

    function render(j) {
        const pg = j.pagination;
        j.items.forEach(p => { PRODUK[p.id] = p; });
        const filtered = state.q || state.category;
        $('#grid').innerHTML = j.items.length ? j.items.map(card).join('') : `
            <div class="empty">
                <strong>${filtered ? 'Produk tidak ditemukan' : 'Belum ada produk'}</strong>
                ${filtered ? 'Coba kata kunci atau kategori lain.' : 'Produk akan tampil di sini setelah ditambahkan oleh admin.'}
            </div>`;
        $('#count').textContent = pg.total ? `Menampilkan ${pg.from}–${pg.to} dari ${pg.total} produk` : 'Tidak ada produk';

        const nums = [];
        for (let i = Math.max(1, pg.current_page - 1); i <= Math.min(pg.last_page, pg.current_page + 1); i++) nums.push(i);
        $('#pages').innerHTML = pg.last_page <= 1 ? '' :
            `<button data-page="${pg.current_page - 1}" ${pg.current_page === 1 ? 'disabled' : ''} aria-label="Sebelumnya">‹</button>` +
            nums.map(n => `<button data-page="${n}" class="${n === pg.current_page ? 'on' : ''}">${n}</button>`).join('') +
            `<button data-page="${pg.current_page + 1}" ${pg.current_page === pg.last_page ? 'disabled' : ''} aria-label="Berikutnya">›</button>`;
    }

    async function load(force = true) {
        try {
            const res = await fetch(URL_DATA + '?' + new URLSearchParams(state), { headers: { Accept: 'application/json' } });
            const j = await res.json();
            if (!j.items.length && state.page > 1) { state.page = Math.max(1, j.pagination.last_page); return load(); }
            const sig = JSON.stringify(j);
            if (force || sig !== lastSig) { lastSig = sig; render(j); }
        } catch (e) {
            $('#count').textContent = 'Gagal memuat produk. Periksa koneksi Anda.';
        }
    }
    const reload = () => { lastSig = ''; load(true); };

    // Tombol "Keranjang" di kartu produk
    $('#grid').addEventListener('click', e => {
        const btn = e.target.closest('[data-add]');
        if (!btn || btn.disabled) return;
        const p = PRODUK[btn.dataset.add];
        if (!p || !p.tersedia) return;

        const card = btn.closest('.card');
        const src = card.querySelector('.media img, .media svg');
        const max = p.pakai_stok ? p.stok : Infinity;   // produk per-stok dibatasi jumlah stok

        if (RF.add(p.id, max, src)) RF.toast(p.nama + ' ditambahkan ke keranjang.');
    });

    $('#chips').addEventListener('click', e => {
        const b = e.target.closest('[data-cat]'); if (!b) return;
        state.category = b.dataset.cat; state.sub = ''; state.page = 1;
        renderChips(); reload();
    });
    $('#subchips').addEventListener('click', e => {
        const b = e.target.closest('[data-sub]'); if (!b) return;
        state.sub = b.dataset.sub; state.page = 1;
        renderChips(); reload();
    });
    $('#pages').addEventListener('click', e => {
        const b = e.target.closest('[data-page]');
        if (!b || b.disabled) return;
        state.page = +b.dataset.page; reload();
        window.scrollTo({ top: $('.filters').offsetTop - 80, behavior: 'smooth' });
    });
    const sortEl = $('#sort');
    const setSort = open => { sortEl.classList.toggle('open', open); $('#sortBtn').setAttribute('aria-expanded', open); };
    $('#sortBtn').addEventListener('click', () => setSort(!sortEl.classList.contains('open')));
    $('#sortBack').addEventListener('click', () => setSort(false));
    document.addEventListener('click', e => { if (!sortEl.contains(e.target)) setSort(false); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') setSort(false); });
    $('#sortMenu').addEventListener('click', e => {
        const b = e.target.closest('[data-sort]'); if (!b) return;
        state.sort = b.dataset.sort; state.page = 1;
        $('#sortLabel').textContent = b.dataset.label;
        document.querySelectorAll('.sortopt').forEach(o => o.setAttribute('aria-selected', o === b));
        setSort(false); reload();
    });
    let t;
    $('#q').addEventListener('input', e => { clearTimeout(t); t = setTimeout(() => { state.q = e.target.value.trim(); state.page = 1; reload(); }, 300); });

    // Realtime
    setInterval(() => { if (!document.hidden) load(false); }, 8000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) load(false); });

    renderChips();
    load();
})();
</script>
@endpush