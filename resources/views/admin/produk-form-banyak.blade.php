<style>
    .pb { display:grid; gap:14px; padding-bottom:8px; }
    .pb *, .pb *::before, .pb *::after { box-sizing:border-box; }
    .pb [hidden] { display:none !important; }

    .pb-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 16px; background:#fff; border-radius:14px; box-shadow:0 1px 3px rgba(92,18,41,.08); }
    .pb-bar strong { display:block; font-size:15px; }
    .pb-bar span { display:block; font-size:12px; color:var(--muted,#8a6b73); line-height:1.4; }
    .pb-tambah { display:none; align-items:center; gap:8px; height:38px; padding:0 16px; border:1.5px solid var(--maroon,#8b1a3a); border-radius:11px; background:#fff; color:var(--maroon,#8b1a3a); font:inherit; font-size:13px; font-weight:700; cursor:pointer; white-space:nowrap; transition:background .15s, color .15s; }
    .pb-tambah:hover:not(:disabled) { background:var(--maroon,#8b1a3a); color:#fff; }
    .pb-tambah:disabled { opacity:.5; cursor:default; }

    .pb-cols { display:none; }
    .pb-list { display:grid; gap:12px; }

    .pb-item { display:grid; align-items:start; gap:12px 10px; padding:14px; background:#fff; border-radius:16px; box-shadow:0 1px 3px rgba(92,18,41,.1); transition:background .2s, box-shadow .2s; animation:pbMasuk .25s ease-out;
        grid-template-columns:30px 42px minmax(0,1fr) minmax(0,1fr) 40px 40px;
        grid-template-areas:"no judul judul judul salin hapus" "foto foto kat kat kat kat" "nama nama nama nama nama nama" "harga harga harga qty qty qty" "aktif aktif aktif desk desk desk"; }
    .pb-item.salah { box-shadow:0 0 0 2px #e5736f; }
    @keyframes pbMasuk { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
    .c-no { grid-area:no; } .c-judul { grid-area:judul; } .c-foto { grid-area:foto; } .c-kat { grid-area:kat; } .c-nama { grid-area:nama; }
    .c-harga { grid-area:harga; } .c-qty { grid-area:qty; } .c-aktif { grid-area:aktif; } .c-desk { grid-area:desk; } .c-salin { grid-area:salin; } .c-hapus { grid-area:hapus; }
    .pb-desc { grid-column:1 / -1; }

    .pb-no { align-self:center; width:28px; height:28px; border-radius:50%; background:var(--maroon,#8b1a3a); color:#fff; display:grid; place-items:center; font-size:13px; font-weight:700; }
    .pb-judul { align-self:center; min-width:0; }
    .pb-judul strong { display:block; font-size:14px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .pb-judul small { display:block; font-size:11.5px; color:var(--muted,#8a6b73); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .pb-kat { display:flex; flex-direction:column; gap:10px; min-width:0; }
    .pb-f { min-width:0; }
    .pb-f > label, .pb-desc > label { display:block; font-size:12px; font-weight:600; margin:0 0 5px; }
    .pb-desc > label em { font-style:normal; font-weight:400; color:var(--muted,#8a6b73); }

    .pb .pb-f input[type=text], .pb .pb-f input[type=number], .pb .pb-f select, .pb .pb-desc textarea { display:block; width:100%; height:44px; padding:0 12px; border:1.5px solid var(--line,#e6cfd5); border-radius:11px; background:#fff; font:inherit; font-size:16px; color:inherit; outline:0; transition:border-color .15s, box-shadow .15s; }
    .pb .pb-desc textarea { height:auto; min-height:72px; padding:10px 12px; resize:vertical; }
    .pb .pb-f input:focus, .pb .pb-f select:focus, .pb .pb-desc textarea:focus, .pb-rp:focus-within { border-color:var(--maroon,#8b1a3a); box-shadow:0 0 0 3px rgba(139,26,58,.12); }
    .pb .pb-f input[readonly] { background:var(--blush,#fbeff2); color:var(--muted,#8a6b73); }
    .pb-f.salah input, .pb-f.salah select, .pb-f.salah .pb-rp { border-color:#e5736f; }

    .pb-rp { display:flex; align-items:stretch; border:1.5px solid var(--line,#e6cfd5); border-radius:11px; background:#fff; overflow:hidden; transition:border-color .15s, box-shadow .15s; }
    .pb-rp > span { display:grid; place-items:center; padding:0 11px; background:var(--blush,#fbeff2); color:var(--muted,#8a6b73); font-size:12.5px; font-weight:700; }
    .pb .pb-rp input[type=number] { border:0; border-radius:0; height:41px; box-shadow:none; min-width:0; }

    .pb-seg { display:flex; gap:3px; height:44px; padding:3px; background:var(--blush,#fbeff2); border-radius:12px; }
    .pb-seg button { flex:1; border:0; border-radius:9px; background:transparent; color:var(--muted,#8a6b73); font:inherit; font-size:13px; font-weight:600; cursor:pointer; transition:background .15s, color .15s; }
    .pb-seg button.on.ya { background:#1e7a34; color:#fff; }
    .pb-seg button.on.tidak { background:#b3261e; color:#fff; }

    .pb-fotoWrap { position:relative; }
    .pb label.pb-foto { display:block; position:relative; width:82px; height:82px; border:2px dashed var(--maroon,#8b1a3a); border-radius:14px; background:var(--blush,#fbeff2); color:var(--maroon,#8b1a3a); cursor:pointer; overflow:hidden; transition:background .15s; }
    .pb-foto:hover { background:var(--blush-dk,#f3d3da); }
    .pb-fotoWrap.salah .pb-foto { border-color:#e5736f; }
    .pb .pb-foto input { position:absolute; opacity:0; width:0; height:0; pointer-events:none; }
    .pb-foto-kosong { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px; padding:4px; text-align:center; font-size:11px; font-weight:700; line-height:1.2; }
    .pb-foto-img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
    .pb-foto.ada .pb-foto-kosong { inset:auto 0 0 0; height:22px; padding:0; flex-direction:row; background:rgba(59,16,32,.7); color:#fff; font-size:11px; }
    .pb-foto.ada .pb-foto-kosong svg { display:none; }
    .pb-foto-x { position:absolute; top:-7px; left:68px; width:22px; height:22px; border:0; border-radius:50%; background:#3b1020; color:#fff; cursor:pointer; display:grid; place-items:center; z-index:1; }
    .pb-fotoWrap .pb-err { display:none; }

    .pb label.pb-switch { display:flex; align-items:center; gap:10px; align-self:center; min-height:44px; font-size:13px; font-weight:600; cursor:pointer; margin:0; }
    .pb .pb-switch input { position:absolute; opacity:0; width:0; height:0; }
    .pb-sw { display:block; flex:none; position:relative; width:42px; height:24px; border-radius:99px; background:#d9c5ca; transition:background .2s; }
    .pb-sw::after { content:''; position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:#fff; transition:transform .2s; }
    .pb .pb-switch input:checked + .pb-sw { background:var(--maroon,#8b1a3a); }
    .pb .pb-switch input:checked + .pb-sw::after { transform:translateX(18px); }
    .pb .pb-switch input:focus-visible + .pb-sw { outline:2px solid var(--maroon,#8b1a3a); outline-offset:2px; }

    .pb-ib { position:relative; display:inline-flex; align-items:center; justify-content:center; gap:6px; height:40px; min-width:40px; padding:0; border:0; border-radius:10px; background:transparent; color:var(--muted,#8a6b73); font:inherit; font-size:13px; font-weight:600; cursor:pointer; transition:background .15s, color .15s; }
    .pb-ib:hover { background:var(--blush,#fbeff2); color:var(--maroon,#8b1a3a); }
    .pb-ib.bahaya:hover { background:#fde6e8; color:#b3261e; }
    .c-desk { justify-self:end; padding:0 14px; border:1.5px solid var(--line,#e6cfd5); }
    .pb-ib.isi::after { content:''; position:absolute; top:7px; right:7px; width:7px; height:7px; border-radius:50%; background:var(--maroon,#8b1a3a); }

    .pb-err { display:block; margin-top:4px; font-size:11.5px; font-weight:600; color:#b3261e; }
    .pb-err:empty { display:none; }

    .pb-baris { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; height:50px; border:1.5px dashed var(--blush-dk,#f3d3da); border-radius:14px; background:#fff; color:var(--maroon,#8b1a3a); font:inherit; font-size:14px; font-weight:700; cursor:pointer; transition:background .15s; }
    .pb-baris:hover:not(:disabled) { background:var(--blush,#fbeff2); }
    .pb-baris:disabled { opacity:.5; cursor:default; }

    .pb-foot { position:sticky; bottom:0; z-index:5; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 16px calc(12px + env(safe-area-inset-bottom,0px)); background:rgba(255,255,255,.96); backdrop-filter:blur(8px); border-radius:16px; box-shadow:0 -4px 22px rgba(92,18,41,.14); }
    .pb-foot-info { display:none; font-size:12.5px; color:var(--muted,#8a6b73); }
    .pb-foot-act { display:flex; gap:10px; flex:1; }
    .pb-batal { display:inline-flex; align-items:center; justify-content:center; height:46px; padding:0 20px; border:1.5px solid var(--line,#e6cfd5); border-radius:12px; background:#fff; color:var(--ink,#3b1020); font-size:14px; font-weight:700; text-decoration:none; }
    .pb-batal:hover { background:var(--blush,#fbeff2); }
    .pb-simpan { flex:1; display:inline-flex; align-items:center; justify-content:center; height:46px; padding:0 24px; border:0; border-radius:12px; background:var(--maroon-dk,#5c1229); color:#fff; font:inherit; font-size:14px; font-weight:700; cursor:pointer; transition:background .15s, transform .1s; }
    .pb-simpan:hover { background:var(--maroon,#8b1a3a); }
    .pb-simpan:active { transform:scale(.98); }
    .pb-simpan:disabled { opacity:.65; cursor:default; }
    .pb-toast { position:fixed; left:50%; bottom:96px; transform:translate(-50%,20px); z-index:50; width:max-content; max-width:92vw; padding:11px 18px; border-radius:12px; background:#3b1020; color:#fff; font-size:13px; font-weight:600; opacity:0; pointer-events:none; transition:opacity .2s, transform .2s; }
    .pb-toast.show { opacity:1; transform:translate(-50%,0); }



    @media (max-width: 420px) {
        .pb .pb-item { grid-template-areas:"no judul judul judul salin hapus" "foto foto kat kat kat kat" "nama nama nama nama nama nama" "harga harga harga harga harga harga" "qty qty qty qty qty qty" "aktif aktif aktif desk desk desk"; }
        .pb .pb-bar span { display:none; }
    }

    @media (min-width: 1000px) {
        .pb .pb-list { grid-template-columns:repeat(2,minmax(0,1fr)); align-items:start; }
        .pb .pb-tambah { display:inline-flex; }
        .pb .pb-foot-info { display:block; }
        .pb .pb-foot-act { flex:none; }
        .pb .pb-simpan { flex:none; }
    }

    @media (min-width: 1500px) {
        .pb .pb-table { background:#fff; border-radius:16px; box-shadow:0 1px 3px rgba(92,18,41,.08); }
        .pb .pb-list { display:block; }
        .pb .pb-cols, .pb .pb-item { grid-template-columns:28px 104px minmax(210px,1.6fr) minmax(140px,1.1fr) 140px 150px 50px 34px 34px 34px; gap:10px; }
        .pb .pb-cols { display:grid; position:sticky; top:0; z-index:4; padding:10px 14px; background:var(--blush,#fbeff2); border-bottom:1px solid var(--line,#e6cfd5); border-radius:16px 16px 0 0; font-size:11.5px; font-weight:700; color:var(--muted,#8a6b73); }
        .pb .pb-cols > :last-child { grid-column:span 3; }
        .pb .pb-item { grid-template-areas:"no foto kat nama harga qty aktif desk salin hapus"; padding:10px 14px; border-radius:0; box-shadow:none; border-bottom:1px solid var(--line,#e6cfd5); }
        .pb .pb-item.salah { box-shadow:none; background:#fff7f7; }
        .pb .pb-judul { display:none; }
        .pb .pb-desc { grid-column:3 / -1; }
        .pb .c-no { margin-top:6px; align-self:start; }
        .pb .pb-kat { flex-direction:row; gap:8px; }
        .pb .pb-kat > .pb-f { flex:1; }
        .pb .pb-f input[type=text], .pb .pb-f input[type=number], .pb .pb-f select { height:38px; font-size:13px; border-radius:10px; }
        .pb .pb-rp input[type=number] { height:35px; font-size:13px; }
        .pb .pb-rp { border-radius:10px; } .pb-seg { height:38px; border-radius:11px; }
        .pb .pb-seg button { font-size:12px; }
        .pb label.pb-foto { display:flex; align-items:center; gap:6px; width:100%; height:38px; padding:0 8px; border-radius:10px; }
        .pb .pb-foto-kosong, .pb .pb-foto.ada .pb-foto-kosong { position:static; flex-direction:row; gap:6px; height:auto; padding:0; background:none; color:inherit; font-size:11.5px; white-space:nowrap; }
        .pb .pb-foto.ada .pb-foto-kosong svg { display:none; }
        .pb .pb-foto-kosong svg { width:16px; height:16px; }
        .pb .pb-foto-img { position:static; width:26px; height:26px; flex:none; border-radius:6px; }
        .pb .pb-foto-x { top:-6px; left:auto; right:-6px; width:18px; height:18px; }
        .pb .pb-item .pb-f > label, .pb .pb-txt { position:absolute; width:1px; height:1px; margin:-1px; overflow:hidden; clip-path:inset(50%); white-space:nowrap; }
        .pb label.pb-switch { align-self:start; min-height:0; margin-top:8px; }
        .pb .pb-ib { height:34px; min-width:34px; }
        .pb .c-desk { border:0; padding:0; justify-self:start; }
        .pb .pb-ib.isi::after { top:5px; right:5px; }
        .pb .pb-baris { height:46px; border:0; border-top:1.5px dashed var(--blush-dk,#f3d3da); border-radius:0 0 16px 16px; }
    }
    @media (prefers-reduced-motion:reduce) { .pb-item { animation:none; } }
</style>

<div class="pb" id="pb">
    <div class="pb-bar">
        <div>
            <strong id="pbJumlah">1 produk</strong>
            <span>Kategori dan jenis otomatis disalin dari produk sebelumnya. Pakai ikon duplikat untuk menyalin seluruh isian.</span>
        </div>
        <button type="button" class="pb-tambah" data-tambah>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Tambah produk lain
        </button>
    </div>

    <div class="pb-table">
        <div class="pb-cols" aria-hidden="true">
            <div>#</div><div>Foto</div><div>Kategori / Jenis</div><div>Nama produk</div><div>Harga jual</div><div>Stok / Ketersediaan</div><div>Tampil</div><div>Aksi</div>
        </div>
        <div class="pb-list" id="pbList"></div>
        <button type="button" class="pb-baris" data-tambah>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Tambah baris
        </button>
    </div>

    <div class="pb-foot">
        <span class="pb-foot-info" id="pbInfo"></span>
        <div class="pb-foot-act">
            <a class="pb-batal" href="{{ route('admin.produk.index') }}">Batal</a>
            <button type="button" class="pb-simpan" id="pbSimpan">Simpan 1 Produk</button>
        </div>
    </div>
</div>
<div class="pb-toast" id="pbToast" role="status" aria-live="polite"></div>

<template id="pbTpl">
    <article class="pb-item">
        <div class="pb-no c-no">1</div>
        <div class="pb-judul c-judul"><strong data-judul>Produk baru</strong><small data-sub>Pilih kategori untuk mulai</small></div>

        <div class="pb-fotoWrap c-foto">
            <label class="pb-foto" title="Klik untuk memilih foto (JPG, PNG, WEBP, maks. 2 MB)">
                <input type="file" accept="image/png,image/jpeg,image/webp" data-in="foto" aria-label="Foto produk">
                <img class="pb-foto-img" alt="" hidden>
                <span class="pb-foto-kosong"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L8 6H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-3z"/><circle cx="12" cy="13" r="3.2"/></svg><span data-teks>Pilih foto</span></span>
            </label>
            <button type="button" class="pb-foto-x" data-aksi="foto-hapus" aria-label="Hapus foto" hidden><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
            <small class="pb-err" data-err="foto"></small>
        </div>

        <div class="pb-kat c-kat">
            <div class="pb-f" data-w="kategori"><label>Kategori</label><select data-in="kategori"></select><small class="pb-err" data-err="kategori"></small></div>
            <div class="pb-f" data-w="sub" hidden><label data-l="sub">Jenis</label><select data-in="sub"></select><small class="pb-err" data-err="sub"></small></div>
        </div>

        <div class="pb-f c-nama" data-w="nama"><label data-l="nama">Nama produk</label><input type="text" maxlength="100" data-in="nama" autocomplete="off" placeholder="Nama produk"><small class="pb-err" data-err="nama"></small></div>

        <div class="pb-f c-harga" data-w="harga"><label>Harga jual</label><div class="pb-rp"><span>Rp</span><input type="number" min="0" step="500" inputmode="numeric" data-in="harga" placeholder="0"></div><small class="pb-err" data-err="harga"></small></div>

        <div class="c-qty">
            <div class="pb-f" data-w="stok" hidden><label>Stok</label><div class="pb-rp"><input type="number" min="0" step="1" inputmode="numeric" data-in="stok" value="0"><span data-unit>pcs</span></div><small class="pb-err" data-err="stok"></small></div>
            <div class="pb-f" data-w="ada"><label>Ketersediaan</label>
                <div class="pb-seg" data-in="ada"><button type="button" class="ya on" data-v="1">Tersedia</button><button type="button" class="tidak" data-v="0">Habis</button></div>
                <small class="pb-err" data-err="ada"></small>
            </div>
        </div>

        <label class="pb-switch c-aktif" title="Tampilkan di katalog pelanggan"><input type="checkbox" data-in="aktif" checked><span class="pb-sw"></span><span class="pb-txt">Tampil di katalog</span></label>

        <button type="button" class="pb-ib c-desk" data-aksi="desk" title="Deskripsi (opsional)" aria-expanded="false"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h10"/></svg><span class="pb-txt">Deskripsi</span></button>
        <button type="button" class="pb-ib c-salin" data-aksi="salin" title="Duplikat produk ini" aria-label="Duplikat"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg></button>
        <button type="button" class="pb-ib bahaya c-hapus" data-aksi="hapus" title="Hapus produk ini" aria-label="Hapus"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg></button>

        <div class="pb-desc" hidden><label>Deskripsi <em>(opsional)</em></label><textarea rows="2" data-in="deskripsi" placeholder="Ceritakan singkat tentang produk ini"></textarea><small class="pb-err" data-err="deskripsi"></small></div>
    </article>
</template>

<script>
(() => {
    const CATS = @json($categories);
    const URL_SIMPAN = @json(route('admin.produk.store'));
    const CSRF = @json(csrf_token());
    const MAKS = 20;
    const PETA = { id_ctg: 'kategori', id_subctg: 'sub', nama_produk: 'nama', harga: 'harga', stok: 'stok', tersedia: 'ada', foto_produk: 'foto', deskripsi: 'deskripsi' };

    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => [...r.querySelectorAll(s)];
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const list = $('#pbList'), tpl = $('#pbTpl');
    const kartu = () => $$('.pb-item', list);
    const f = (card, n) => $(`[data-in="${n}"]`, card);
    const cat = id => CATS.find(c => String(c.id) === String(id));

    let timer;
    function toast(pesan) {
        const t = $('#pbToast');
        t.textContent = pesan; t.classList.add('show');
        clearTimeout(timer); timer = setTimeout(() => t.classList.remove('show'), 3200);
    }

    function info(card) {
        const c = cat(f(card, 'kategori').value);
        const sub = c ? c.subs.find(s => String(s.id) === f(card, 'sub').value) : null;
        const pakai = sub ? !!sub.pakai_stok : !!(c && !c.subs.length && c.pakai_stok);
        const bebas = !c || !c.subs.length || !!(sub && sub.pakai_stok);
        const auto = c && sub && !sub.pakai_stok
            ? (sub.nama.toLowerCase().startsWith(c.nama.toLowerCase()) ? sub.nama : `${c.nama} ${sub.nama}`) : '';
        return { c, sub, pakai, bebas, auto };
    }

    function setAda(card, v) {
        card.dataset.ada = v;
        $$('.pb-seg button', card).forEach(b => b.classList.toggle('on', b.dataset.v === v));
    }

    function sinkron(card, ubahKategori) {
        if (ubahKategori) {
            const c = cat(f(card, 'kategori').value), sel = f(card, 'sub'), wrap = $('[data-w="sub"]', card);
            if (c && c.subs.length) {
                $('[data-l="sub"]', card).textContent = c.sub_label;
                sel.innerHTML = `<option value="">Pilih ${esc(c.sub_label.toLowerCase())}</option>` + c.subs.map(s => `<option value="${s.id}">${esc(s.nama)}</option>`).join('');
                wrap.hidden = false;
            } else { sel.innerHTML = ''; wrap.hidden = true; }
        }
        const { sub, pakai, bebas, auto } = info(card);
        $('[data-w="stok"]', card).hidden = !pakai;
        $('[data-w="ada"]', card).hidden = pakai;
        $('[data-unit]', card).textContent = sub ? 'tangkai' : 'pcs';

        const nama = f(card, 'nama'), lbl = $('[data-l="nama"]', card);
        if (bebas) {
            nama.readOnly = false;
            lbl.textContent = sub ? 'Nama bunga' : 'Nama produk';
            nama.placeholder = sub ? 'Mis. Aster, Mawar' : 'Nama produk';
            if (nama.dataset.auto === '1') { nama.value = ''; nama.dataset.auto = ''; }
        } else {
            nama.readOnly = true; nama.value = auto; nama.dataset.auto = '1';
            lbl.textContent = 'Nama produk (otomatis)'; nama.placeholder = '';
        }
        judul(card);
    }

    function judul(card) {
        const { c, sub } = info(card);
        $('[data-judul]', card).textContent = f(card, 'nama').value.trim() || 'Produk baru';
        $('[data-sub]', card).textContent = c ? c.nama + (sub ? ' · ' + sub.nama : '') : 'Pilih kategori untuk mulai';
    }

    function nomori() {
        const semua = kartu(), n = semua.length;
        semua.forEach((k, i) => { $('.pb-no', k).textContent = i + 1; $('[data-aksi="hapus"]', k).hidden = n === 1; });
        $('#pbJumlah').textContent = `${n} produk`;
        $('#pbSimpan').textContent = `Simpan ${n} Produk`;
        $('#pbInfo').textContent = n >= MAKS ? `Batas ${MAKS} produk sekali simpan tercapai.` : `Maksimal ${MAKS} produk sekali simpan.`;
        $$('[data-tambah]').forEach(b => { b.disabled = n >= MAKS; });
    }

    function tambah(dari = null, salin = false) {
        if (kartu().length >= MAKS) return toast(`Maksimal ${MAKS} produk sekali simpan.`);
        const card = tpl.content.firstElementChild.cloneNode(true);
        f(card, 'kategori').innerHTML = '<option value="">Pilih kategori</option>' + CATS.map(c => `<option value="${c.id}">${esc(c.nama)}</option>`).join('');
        if (salin && dari && dari.nextElementSibling) list.insertBefore(card, dari.nextElementSibling); else list.appendChild(card);

        if (dari) {
            f(card, 'kategori').value = f(dari, 'kategori').value;
            sinkron(card, true);
            f(card, 'sub').value = f(dari, 'sub').value;
            sinkron(card, false);
            f(card, 'aktif').checked = f(dari, 'aktif').checked;
            setAda(card, dari.dataset.ada || '1');
            if (salin) {
                ['harga', 'stok', 'deskripsi'].forEach(n => { f(card, n).value = f(dari, n).value; });
                if (!f(card, 'nama').readOnly) f(card, 'nama').value = f(dari, 'nama').value;
                judul(card);
                if (f(card, 'deskripsi').value) bukaDeskripsi(card, true);
            }
        } else { setAda(card, '1'); sinkron(card, true); }

        nomori();
        if (kartu().length > 1) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const fokus = [f(card, 'kategori'), f(card, 'sub'), f(card, 'nama'), f(card, 'harga')].find(el => el && !el.value && !el.readOnly && !el.closest('[hidden]'));
        if (fokus) setTimeout(() => fokus.focus({ preventScroll: true }), 60);
    }

    function bukaDeskripsi(card, buka) {
        const box = $('.pb-desc', card), btn = $('[data-aksi="desk"]', card);
        box.hidden = !buka; btn.setAttribute('aria-expanded', buka);
        if (buka) f(card, 'deskripsi').focus({ preventScroll: true });
    }

    function galat(card, kolom, pesan) {
        const el = $(`[data-err="${kolom}"]`, card);
        if (el) {
            el.textContent = pesan;
            const w = el.closest('.pb-f, .pb-fotoWrap');
            if (w) { w.classList.add('salah'); if (kolom === 'foto') w.title = pesan; }
        }
        if (kolom === 'deskripsi') bukaDeskripsi(card, true);
        card.classList.add('salah');
    }

    function bersih(card) {
        card.classList.remove('salah');
        $$('.pb-err', card).forEach(e => { e.textContent = ''; });
        $$('.salah', card).forEach(w => w.classList.remove('salah'));
    }

    function periksa(card) {
        bersih(card);
        const { c, sub, pakai, bebas } = info(card);
        let ok = true;
        const salah = (k, p) => { galat(card, k, p); ok = false; };
        if (!c) salah('kategori', 'Pilih kategori.');
        else if (c.subs.length && !sub) salah('sub', `Pilih ${c.sub_label.toLowerCase()}.`);
        if (bebas && !f(card, 'nama').value.trim()) salah('nama', 'Nama wajib diisi.');
        if (f(card, 'harga').value === '' || Number(f(card, 'harga').value) < 0) salah('harga', 'Harga wajib diisi.');
        if (pakai && (f(card, 'stok').value === '' || Number(f(card, 'stok').value) < 0)) salah('stok', 'Stok wajib diisi.');
        const file = f(card, 'foto').files[0];
        if (file && file.size > 2 * 1024 * 1024) salah('foto', 'Ukuran foto maksimal 2 MB.');
        return ok;
    }

    function susun() {
        const fd = new FormData();
        fd.append('_token', CSRF);
        kartu().forEach((card, i) => {
            const { sub, pakai } = info(card), p = k => `items[${i}][${k}]`;
            fd.append(p('id_ctg'), f(card, 'kategori').value);
            if (sub) fd.append(p('id_subctg'), sub.id);
            fd.append(p('nama_produk'), f(card, 'nama').value.trim());
            fd.append(p('harga'), f(card, 'harga').value);
            if (pakai) fd.append(p('stok'), f(card, 'stok').value); else fd.append(p('tersedia'), card.dataset.ada || '1');
            fd.append(p('deskripsi'), f(card, 'deskripsi').value.trim());
            fd.append(p('is_active'), f(card, 'aktif').checked ? '1' : '0');
            const file = f(card, 'foto').files[0];
            if (file) fd.append(p('foto_produk'), file);
        });
        return fd;
    }

    async function simpan() {
        const semua = kartu(), hasil = semua.map(periksa);
        if (hasil.includes(false)) {
            semua[hasil.indexOf(false)].scrollIntoView({ behavior: 'smooth', block: 'center' });
            return toast('Lengkapi isian yang ditandai merah.');
        }
        const tombol = $('#pbSimpan'), teks = tombol.textContent;
        tombol.disabled = true; tombol.textContent = 'Menyimpan…';
        try {
            const res = await fetch(URL_SIMPAN, { method: 'POST', headers: { Accept: 'application/json' }, body: susun() });
            const j = await res.json().catch(() => ({}));
            if (res.ok) { location.href = j.redirect; return; }
            if (res.status === 422 && j.errors) {
                let pertama = null;
                Object.entries(j.errors).forEach(([kunci, pesan]) => {
                    const m = kunci.match(/^items\.(\d+)\.(\w+)$/);
                    if (!m || !semua[+m[1]]) return;
                    galat(semua[+m[1]], PETA[m[2]] || m[2], pesan[0]);
                    pertama = pertama || semua[+m[1]];
                });
                if (pertama) pertama.scrollIntoView({ behavior: 'smooth', block: 'center' });
                toast(j.errors.items ? j.errors.items[0] : 'Ada isian yang perlu diperbaiki. Pilih ulang foto bila perlu.');
            } else toast(j.message || 'Gagal menyimpan produk.');
        } catch (e) { toast('Tidak bisa terhubung ke server.'); }
        tombol.disabled = false; tombol.textContent = teks;
    }

    list.addEventListener('change', e => {
        const card = e.target.closest('.pb-item');
        if (!card) return;
        const n = e.target.dataset.in;
        if (n === 'kategori') sinkron(card, true);
        else if (n === 'sub') sinkron(card, false);
        else if (n === 'foto') {
            const file = e.target.files[0], img = $('.pb-foto-img', card);
            if (!file) return;
            img.src = URL.createObjectURL(file);
            img.hidden = false; $('.pb-foto', card).classList.add('ada'); $('[data-teks]', card).textContent = 'Ganti';
            $('[data-aksi="foto-hapus"]', card).hidden = false;
        }
    });

    list.addEventListener('input', e => {
        const card = e.target.closest('.pb-item'), n = e.target.dataset.in;
        if (!card) return;
        if (n === 'nama') judul(card);
        if (n === 'deskripsi') $('[data-aksi="desk"]', card).classList.toggle('isi', !!e.target.value.trim());
    });

    list.addEventListener('click', e => {
        const card = e.target.closest('.pb-item');
        const seg = e.target.closest('.pb-seg button');
        if (card && seg) return setAda(card, seg.dataset.v);
        const b = e.target.closest('[data-aksi]');
        if (!b || !card) return;
        const aksi = b.dataset.aksi;
        if (aksi === 'salin') tambah(card, true);
        else if (aksi === 'hapus' && kartu().length > 1) {
            const terisi = f(card, 'nama').value.trim() || f(card, 'harga').value;
            if (terisi && !confirm('Hapus produk ini dari daftar?')) return;
            card.remove(); nomori();
        }
        else if (aksi === 'desk') bukaDeskripsi(card, $('.pb-desc', card).hidden);
        else if (aksi === 'foto-hapus') {
            e.preventDefault();
            f(card, 'foto').value = ''; $('.pb-foto-img', card).hidden = true; $('.pb-foto', card).classList.remove('ada'); $('[data-teks]', card).textContent = 'Pilih foto'; b.hidden = true;
        }
    });

    $$('[data-tambah]').forEach(b => b.addEventListener('click', () => tambah(kartu().slice(-1)[0] || null)));
    $('#pbSimpan').addEventListener('click', simpan);
    tambah();
})();
</script>
