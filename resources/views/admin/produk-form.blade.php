@php $p = $produk ?? null; @endphp

<style>
    .pr .form { max-width:640px; }
    .pr .form label { display:block; font-size:12px; font-weight:600; margin:14px 0 6px; }
    .pr .form textarea { min-height:90px; resize:vertical; }
    .pr .form .row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .pr .form .err { color:#c0261c; font-size:11px; margin-top:4px; display:block; }
    .pr .form .chk { display:flex; align-items:center; gap:8px; margin-top:16px; font-size:13px; }
    .pr .form .chk input { width:auto; }
    .pr .form .preview { width:84px; height:84px; border-radius:10px; object-fit:cover; margin-top:8px; background:var(--blush-dk); }
    .pr .form .seg { display:inline-flex; gap:4px; padding:4px; background:var(--blush); border:1px solid var(--line); border-radius:10px; max-width:100%; }
    .pr .form .opt { position:relative; display:block; margin:0; cursor:pointer; }
    .pr .form .opt input { position:absolute; opacity:0; width:0; height:0; }
    .pr .form .opt span { display:flex; align-items:center; gap:7px; padding:7px 18px; border-radius:7px; font-size:13px; font-weight:600; color:var(--muted); transition:background .15s, color .15s; }
    .pr .form .opt:hover span { color:var(--ink); }
    .pr .form .opt.ya input:checked + span { background:#1e7a34; color:#fff; }
    .pr .form .opt.tidak input:checked + span { background:#b3261e; color:#fff; }
    .pr .form .opt.ya input:checked + span::before { content:"✓"; }
    .pr .form .opt.tidak input:checked + span::before { content:"✕"; }
    .pr .form .opt input:focus-visible + span { outline:2px solid var(--maroon); outline-offset:2px; }
    @media (prefers-reduced-motion:reduce){ .pr .form .opt span { transition:none; } }
    .pr .form input[readonly] { background:var(--blush); color:var(--muted); }
    .pr .form .foot { display:flex; gap:10px; margin-top:24px; }
    @media (max-width:600px){ .pr .form .row { grid-template-columns:1fr; } }
</style>

<form class="form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($p) @method('PUT') @endif

    <div class="row">
        <div>
            <label for="id_ctg">Kategori</label>
            <select id="id_ctg" name="id_ctg" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c['id'] }}" @selected(old('id_ctg', $p->id_ctg ?? '') == $c['id'])>{{ $c['nama'] }}</option>
                @endforeach
            </select>
            @error('id_ctg') <small class="err">{{ $message }}</small> @enderror
        </div>
        <div id="subWrap" style="display:none">
            <label for="id_subctg" id="subLabel">Jenis</label>
            <select id="id_subctg" name="id_subctg"></select>
            @error('id_subctg') <small class="err">{{ $message }}</small> @enderror
        </div>
    </div>

    <label for="nama_produk" id="namaLabel">Nama produk</label>
    <input id="nama_produk" name="nama_produk" maxlength="100" value="{{ old('nama_produk', $p->nama_produk ?? '') }}">
    @error('nama_produk') <small class="err">{{ $message }}</small> @enderror

    <div class="row">
        <div>
            <label for="harga">Harga jual (Rp)</label>
            <input id="harga" name="harga" type="number" min="0" step="1000" value="{{ old('harga', isset($p) ? (int) $p->harga : '') }}" required>
            @error('harga') <small class="err">{{ $message }}</small> @enderror
        </div>
        <div id="stokWrap">
            <label for="stok">Stok (tangkai)</label>
            <input id="stok" name="stok" type="number" min="0" value="{{ old('stok', $p->stok ?? 0) }}">
            @error('stok') <small class="err">{{ $message }}</small> @enderror
        </div>
        <div id="tersediaWrap">
            @php $ters = (string) old('tersedia', isset($p) ? (int) $p->tersedia : 1); @endphp
            <label>Ketersediaan</label>
            <div class="seg">
                <label class="opt ya"><input type="radio" name="tersedia" value="1" @checked($ters === '1')><span>Tersedia</span></label>
                <label class="opt tidak"><input type="radio" name="tersedia" value="0" @checked($ters === '0')><span>Tidak tersedia</span></label>
            </div>
            @error('tersedia') <small class="err">{{ $message }}</small> @enderror
        </div>
    </div>

    <label for="foto_produk">Foto produk</label>
    <input id="foto_produk" name="foto_produk" type="file" accept="image/png,image/jpeg,image/webp">
    @error('foto_produk') <small class="err">{{ $message }}</small> @enderror
    <img id="preview" class="preview" alt="Pratinjau foto" @if (! optional($p)->foto_url) style="display:none" @else src="{{ $p->foto_url }}" @endif>

    <label for="deskripsi">Deskripsi</label>
    <textarea id="deskripsi" name="deskripsi">{{ old('deskripsi', $p->deskripsi ?? '') }}</textarea>
    @error('deskripsi') <small class="err">{{ $message }}</small> @enderror

    <label class="chk">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $p->is_active ?? true))>
        Tampilkan di katalog (aktif)
    </label>

    <div class="foot">
        <button type="submit" class="btn primary">{{ $p ? 'Simpan perubahan' : 'Simpan produk' }}</button>
        <a href="{{ route('admin.produk.index') }}" class="btn">Batal</a>
    </div>
</form>

<script>
    // Pilihan subkategori (jenis / ukuran) menyesuaikan kategori yang dipilih
    const CATS = @json($categories);
    const SELECTED_SUB = @json((string) old('id_subctg', $p->id_subctg ?? ''));
    const catSel = document.getElementById('id_ctg'), subSel = document.getElementById('id_subctg');

    function syncSub(keep) {
        const c = CATS.find(x => String(x.id) === catSel.value);
        const wrap = document.getElementById('subWrap');
        if (!c || !c.subs.length) { wrap.style.display = 'none'; subSel.innerHTML = ''; return; }
        document.getElementById('subLabel').textContent = c.sub_label;
        subSel.innerHTML = `<option value="">Pilih ${c.sub_label.toLowerCase()}</option>` +
            c.subs.map(s => `<option value="${s.id}" ${String(s.id) === keep ? 'selected' : ''}>${s.nama}</option>`).join('');
        wrap.style.display = 'block';
    }
    // Stok (tangkai) hanya untuk jenis yang pakai_stok; selain itu tersedia / tidak
    function applyMode() {
        const c = CATS.find(x => String(x.id) === catSel.value);
        const s = c && c.subs.find(x => String(x.id) === subSel.value);
        const pakai = !!(s && s.pakai_stok);
        const stokInp = document.getElementById('stok');
        document.getElementById('stokWrap').style.display = pakai ? '' : 'none';
        stokInp.disabled = !pakai; stokInp.required = pakai;
        document.getElementById('tersediaWrap').style.display = pakai ? 'none' : '';
        document.querySelectorAll('#tersediaWrap input').forEach(i => i.disabled = pakai);
        applyName(c, s);
    }

    // Nama produk: bebas untuk Buket Fresh Flower, selain itu otomatis dari jenis
    const namaInp = document.getElementById('nama_produk'), namaLbl = document.getElementById('namaLabel');
    function applyName(c, s) {
        const bebas = (c && !c.subs.length) || (s && s.pakai_stok);
        if (bebas) {
            namaInp.readOnly = false; namaInp.required = true;
            namaLbl.textContent = s ? 'Nama bunga (mis. Aster, Mawar)' : 'Nama produk';
            namaInp.placeholder = s ? 'Mis. Aster, Mawar' : '';
            if (namaInp.dataset.auto === '1') { namaInp.value = ''; namaInp.dataset.auto = ''; }
        } else {
            namaInp.readOnly = true; namaInp.required = false;
            namaLbl.textContent = 'Nama produk (otomatis dari jenis)';
            if (c && s) {
                const k = c.nama, j = s.nama;
                namaInp.value = j.toLowerCase().startsWith(k.toLowerCase()) ? j : `${k} ${j}`;
                namaInp.dataset.auto = '1';
                namaInp.placeholder = '';
            } else {
                namaInp.value = ''; namaInp.dataset.auto = '';
                namaInp.placeholder = 'Pilih kategori dan jenis terlebih dahulu';
            }
        }
    }
    catSel.addEventListener('change', () => { syncSub(''); applyMode(); });
    subSel.addEventListener('change', applyMode);
    syncSub(SELECTED_SUB);
    applyMode();

    document.getElementById('foto_produk').addEventListener('change', e => {
        const f = e.target.files[0], img = document.getElementById('preview');
        if (f) { img.src = URL.createObjectURL(f); img.style.display = 'block'; }
    });
</script>
