@extends('layouts.admin')

@section('title', 'Manajemen Stok - Royal Florist')

@section('styles')
<style>
    /* -------------------------------------------------------------
     * PALET WARNA & VARIABEL TEMA (ROYAL FLORIST)
     * ------------------------------------------------------------- */
    :root {
        --rf-maroon: #7B1830;         /* Warna Utama Sidebar & Aksesori */
        --rf-pink-bg: #FBE9ED;        /* Background Soft Pink */
        --rf-pink-kartu: #FFF7F8;     /* Background Kartu Ringkasan */
        --rf-pink-garis: #F3CCD6;     /* Border Pink Halus */
        --rf-teks: #2B2B2B;           /* Warna Teks Utama */
        --rf-abu: #6F6F6F;            /* Warna Teks Keterangan */
        --rf-hijau: #2E8B57;          /* Warna Status Aman / Naik */
        --rf-kuning: #D97706;         /* Warna Status Menipis */
        --rf-merah: #C0392B;          /* Warna Status Kritis / Turun */
    }

    /* -------------------------------------------------------------
     * 1. KARTU RINGKASAN STATISTIK (4 Card Atas)
     * ------------------------------------------------------------- */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .card-stat,
    .card {
        position: relative;
        background: var(--rf-pink-kartu);
        border: 1px solid var(--rf-pink-garis);
        border-radius: 14px;
        padding: 18px 20px 16px;
        box-shadow: 0 2px 6px rgba(123, 24, 48, 0.03);
    }

    .card-stat-head,
    .card-head {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-stat-icon,
    .card-icon {
        width: 36px;
        height: 36px;
        background: #F3D0D8;
        color: var(--rf-maroon);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-stat-title,
    .card-title {
        font-size: 13px;
        font-weight: 500;
        color: #7A6269;
    }

    .card-stat-value,
    .card-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--rf-maroon);
        margin: 12px 0 10px;
        line-height: 1;
    }

    .card-stat-trend,
    .card-trend {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
    }

    .card-stat-trend.naik { color: var(--rf-hijau); font-weight: 500; }
    .card-stat-trend.turun { color: var(--rf-merah); font-weight: 500; }

    /* -------------------------------------------------------------
     * 2. WIDGET TENGAH (Stok Kategori & Mutasi Stok)
     * ------------------------------------------------------------- */
    .middle-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .widget-box {
        background: #FFFFFF;
        border: 1px solid var(--rf-pink-garis);
        border-radius: 14px;
        padding: 22px 24px;
        box-shadow: 0 2px 6px rgba(123, 24, 48, 0.03);
    }

    .widget-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 4px;
    }

    .widget-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--rf-teks);
    }

    .widget-subtitle {
        font-size: 11.5px;
        color: var(--rf-abu);
        margin-bottom: 20px;
    }

    /* Progress Bar Kategori */
    .category-item {
        margin-bottom: 16px;
    }

    .category-item:last-child {
        margin-bottom: 0;
    }

    .category-info {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .category-info .name { font-weight: 600; color: var(--rf-teks); }
    .category-info .count { color: var(--rf-abu); font-size: 12px; }

    .progress-bar-bg {
        width: 100%;
        height: 10px;
        background: #F4E8EC;
        border-radius: 50px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: var(--rf-maroon);
        border-radius: 50px;
        transition: width 0.4s ease;
    }

    /* Variasi warna tipis untuk membedakan baris jika diinginkan */
    .category-item:nth-child(2) .progress-bar-fill { background: #B83B5E; }
    .category-item:nth-child(3) .progress-bar-fill { background: #8E4453; }
    .category-item:nth-child(4) .progress-bar-fill { background: #682232; }

    /* List Mutasi Stok */
    .mutation-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 20px;
    }

    .mutation-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    .mutation-item .label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--rf-teks);
    }

    .mutation-item .val-masuk { color: var(--rf-hijau); font-weight: 700; }
    .mutation-item .val-keluar { color: var(--rf-merah); font-weight: 700; }
    .mutation-item .val-penyesuaian { color: var(--rf-kuning); font-weight: 700; }

    /* Highlight Produk Paling Banyak Keluar */
    .top-product-card {
        background: var(--rf-pink-bg);
        border-radius: 10px;
        padding: 12px 14px;
    }

    .top-product-card .caption {
        font-size: 10px;
        color: var(--rf-abu);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .top-product-card .prod-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--rf-maroon);
        margin-top: 2px;
    }

    .top-product-card .prod-stat {
        font-size: 11px;
        color: var(--rf-abu);
    }

    /* -------------------------------------------------------------
     * 3. TABEL DAFTAR STOK PRODUK (Bawah)
     * ------------------------------------------------------------- */
    .table-container {
        background: #FFFFFF;
        border: 1px solid var(--rf-pink-garis);
        border-radius: 14px;
        padding: 22px 24px;
        box-shadow: 0 2px 6px rgba(123, 24, 48, 0.03);
    }

    .table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .table-toolbar .title-area {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-toolbar h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--rf-teks);
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-input {
        background: #FAFAFA;
        border: 1px solid #E2D2D6;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 12.5px;
        outline: none;
        min-width: 200px;
        color: var(--rf-teks);
    }

    .search-input:focus {
        border-color: var(--rf-maroon);
        background: #FFF;
    }

    .select-filter {
        background: #FAFAFA;
        border: 1px solid #E2D2D6;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 12.5px;
        outline: none;
        color: var(--rf-teks);
        cursor: pointer;
    }

    /* Element Tabel HTML */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }

    .custom-table th {
        background: #FFF5F7;
        color: #7A6269;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 12px 14px;
        border-bottom: 1.5px solid var(--rf-pink-garis);
        text-align: left;
    }

    .custom-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #F6E6EA;
        color: var(--rf-teks);
        vertical-align: middle;
    }

    .custom-table tbody tr:hover {
        background: #FFFBFB;
    }

    .sku-code {
        font-family: monospace;
        color: #666;
        font-size: 12px;
    }

    .stok-count {
        font-weight: 700;
        color: var(--rf-teks);
    }

    .val-plus { color: var(--rf-hijau); font-weight: 600; }
    .val-minus { color: var(--rf-merah); font-weight: 600; }

    /* Badge Status (Aman, Menipis, Kritis) */
    .badge-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-status.aman {
        background: #E8F8F0;
        color: var(--rf-hijau);
    }

    .badge-status.menipis {
        background: #FEF3C7;
        color: #B45309;
    }

    .badge-status.kritis {
        background: #FEE2E2;
        color: var(--rf-merah);
    }

    /* Tombol Aksi */
    .action-btns {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-action {
        background: none;
        border: none;
        color: #8A757B;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: 0.2s;
    }

    .btn-action:hover {
        color: var(--rf-maroon);
        background: var(--rf-pink-bg);
    }

    .btn-action.delete:hover {
        color: var(--rf-merah);
        background: #FEE2E2;
    }

    /* -------------------------------------------------------------
     * 4. MEDIA QUERIES (Responsif HP & Tablet)
     * ------------------------------------------------------------- */
    @media (max-width: 1100px) {
        .cards-grid { grid-template-columns: repeat(2, 1fr); }
        .middle-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 600px) {
        .cards-grid { grid-template-columns: 1fr; }
        .filter-group { width: 100%; }
        .search-input, .select-filter { flex: 1; min-width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="stok-container">

    {{-- 1. HEADER HALAMAN --}}
    <div class="page-header mb-4">
        <h1 class="text-2xl font-bold text-maroon">Stok Produk</h1>
        <p class="text-xs text-gray-500">Ringkasan ketersediaan dan status stok produk Royal Florist</p>
    </div>

    {{-- 2. RINGKASAN STATISTIK (4 KARTU ATAS) --}}
    <section class="cards-grid mb-6">
        {{-- Card Total Stok --}}
        <div class="card">
            <div class="card-head">
                <div class="card-icon">{!! \App\Support\Ikon::svg('box', 20) !!}</div>
                <div class="card-title">Total Stok Produk</div>
            </div>
            <div class="card-value">{{ $summaryCards['total_stok']['nilai'] }}</div>
            <div class="card-trend text-green-600">
                <span>{{ $summaryCards['total_stok']['trend'] }}</span>
            </div>
        </div>

        {{-- Card Stok Aman --}}
        <div class="card">
            <div class="card-head">
                <div class="card-icon">{!! \App\Support\Ikon::svg('shield', 20) !!}</div>
                <div class="card-title">Stok Aman</div>
            </div>
            <div class="card-value">{{ $summaryCards['stok_aman']['nilai'] }}</div>
            <div class="card-trend text-green-600">
                <span>{{ $summaryCards['stok_aman']['trend'] }}</span>
            </div>
        </div>

        {{-- Card Stok Menipis --}}
        <div class="card">
            <div class="card-head">
                <div class="card-icon">{!! \App\Support\Ikon::svg('alert', 20) !!}</div>
                <div class="card-title">Stok Menipis</div>
            </div>
            <div class="card-value">{{ $summaryCards['stok_menipis']['nilai'] }}</div>
            <div class="card-trend text-amber-600">
                <span>{{ $summaryCards['stok_menipis']['trend'] }}</span>
            </div>
        </div>

        {{-- Card Stok Kritis --}}
        <div class="card">
            <div class="card-head">
                <div class="card-icon">{!! \App\Support\Ikon::svg('info', 20) !!}</div>
                <div class="card-title">Stok Kritis</div>
            </div>
            <div class="card-value">{{ $summaryCards['stok_kritis']['nilai'] }}</div>
            <div class="card-trend text-rose-600">
                <span>{{ $summaryCards['stok_kritis']['trend'] }}</span>
            </div>
        </div>
    </section>


    {{-- 3. BARIS MIDDLE WIDGET (STOK PER KATEGORI & MUTASI STOK) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        {{-- Left: Stok per Kategori (2/3 Lebar) --}}
        <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-pink-garis shadow-sm">
            <h3 class="font-semibold text-maroon text-base">Stok per Kategori</h3>
            <p class="text-xs text-gray-500 mb-4">Ringkasan ketersediaan stok produk</p>

            <div class="space-y-4">
                @foreach ($stokKategori as $kat)
                    <div>
                        <div class="flex justify-between text-xs font-medium mb-1">
                            <span>{{ $kat['nama'] }}</span>
                            <span class="text-gray-500">{{ $kat['total'] }} unit</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                            <div class="bg-maroon h-2.5 rounded-full" style="width: {{ min(100, $kat['total']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right: Mutasi Stok Hari Ini (1/3 Lebar) --}}
        <div class="bg-white p-5 rounded-xl border border-pink-garis shadow-sm">
            <h3 class="font-semibold text-maroon text-base">Mutasi Stok Hari Ini</h3>
            <p class="text-xs text-gray-500 mb-4">Ringkasan pergerakan stok</p>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Total Stok Masuk</span>
                    <span class="font-semibold text-green-600">+{{ $mutasiHariIni['masuk'] }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Total Stok Keluar</span>
                    <span class="font-semibold text-rose-600">-{{ $mutasiHariIni['keluar'] }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Penyesuaian</span>
                    <span class="font-semibold text-amber-600">{{ $mutasiHariIni['penyesuaian'] }}</span>
                </div>
            </div>

            {{-- Highlight Produk Paling Banyak Keluar --}}
            <div class="mt-4 p-3 bg-pink-bg rounded-lg">
                <p class="text-[10px] text-gray-500 uppercase font-semibold">Produk Paling Banyak Keluar</p>
                @if ($mutasiHariIni['top_keluar'])
                    <p class="font-bold text-maroon text-sm mt-0.5">{{ $mutasiHariIni['top_keluar']['nama'] }}</p>
                    <p class="text-xs text-gray-600">{{ $mutasiHariIni['top_keluar']['jumlah'] }} {{ $mutasiHariIni['top_keluar']['satuan'] }} hari ini</p>
                @else
                    <p class="text-xs text-gray-500 italic mt-0.5">Belum ada transaksi hari ini</p>
                @endif
            </div>
        </div>

    </div>


    {{-- 4. TABEL DAFTAR STOK PRODUK (Bawah) --}}
    <section class="bg-white p-5 rounded-xl border border-pink-garis shadow-sm">
        
        {{-- Header Tabel & Form Filter --}}
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-5">
            <h3 class="font-semibold text-maroon text-lg">Daftar Stok Produk</h3>

            <form method="GET" action="{{ route('admin.stok.index') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                {{-- Input Cari SKU / Nama --}}
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk atau SKU..." class="border rounded-lg px-3 py-1.5 text-xs focus:outline-none">

                {{-- Filter Kategori --}}
                <select name="kategori" onchange="this.form.submit()" class="border rounded-lg px-3 py-1.5 text-xs focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $kat)
                        <option value="{{ $kat->id_ctg }}" {{ request('kategori') == $kat->id_ctg ? 'selected' : '' }}>
                            {{ $kat->category_name }}
                        </option>
                    @endforeach
                </select>

                {{-- Filter Status --}}
                <select name="status" onchange="this.form.submit()" class="border rounded-lg px-3 py-1.5 text-xs focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="aman" {{ request('status') == 'aman' ? 'selected' : '' }}>Aman</option>
                    <option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="kritis" {{ request('status') == 'kritis' ? 'selected' : '' }}>Kritis</option>
                </select>
            </form>
        </div>

        {{-- Tabel Data --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700">
                <thead class="bg-pink-bg text-maroon font-semibold uppercase text-[11px]">
                    <tr>
                        <th class="p-3">NO</th>
                        <th class="p-3">SKU</th>
                        <th class="p-3">NAMA PRODUK</th>
                        <th class="p-3">KATEGORI</th>
                        <th class="p-3">SATUAN</th>
                        <th class="p-3">STOK SAAT INI</th>
                        <th class="p-3">STATUS</th>
                        <th class="p-3 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($productsList as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3">{{ $productsList->firstItem() + $index }}</td>
                            <td class="p-3 font-mono font-medium">{{ $item->sku }}</td>
                            <td class="p-3 font-semibold text-gray-800">{{ $item->nama_produk }}</td>
                            <td class="p-3">{{ $item->category?->category_name ?? '-' }}</td>
                            <td class="p-3">{{ $item->subcategory?->nama_subctg ?? 'Pcs' }}</td>
                            <td class="p-3 font-bold text-maroon">{{ $item->stok }}</td>
                            <td class="p-3">
                                @if ($item->stok > 10)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">Aman</span>
                                @elseif ($item->stok > 0)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">Menipis</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700">Kritis</span>
                                @endif
                            </td>
                            <td class="p-3 text-center space-x-1">
                                {{-- Tombol Quick Edit Stok --}}
                                <button type="button" onclick="openAdjustModal({{ $item->id_product }}, @js($item->nama_produk), {{ $item->stok }})" class="p-1 text-gray-500 hover:text-maroon" title="Adjust Stok">
                                    {!! \App\Support\Ikon::svg('edit', 16) !!}
                                </button>
                                
                                {{-- Tombol Hapus --}}
                                <form action="{{ route('admin.stok.destroy', $item->id_product) }}" method="POST" class="inline" onsubmit="return confirm('Hapus produk dari inventaris stok?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-500 hover:text-rose-600" title="Hapus">
                                        {!! \App\Support\Ikon::svg('trash', 16) !!}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-gray-400">Data stok produk tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Links Pagination --}}
        <div class="mt-4">
            {{ $productsList->links() }}
        </div>

    </section>

</div>

{{-- MODAL QUICK EDIT / ADJUSTMENT STOK --}}
<div id="adjustModal" class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <h4 class="text-base font-bold text-maroon mb-1">Penyesuaian Stok</h4>
        <p id="modalProductName" class="text-xs text-gray-500 mb-4"></p>

        <form id="adjustForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-700 mb-1">Jumlah Stok Baru</label>
                <input type="number" name="stok_baru" id="modalInputStok" min="0" required class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-maroon">
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeAdjustModal()" class="px-4 py-2 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-maroon rounded-lg">Simpan Stok</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAdjustModal(id, nama, stokLama) {
        document.getElementById('adjustModal').classList.remove('hidden');
        document.getElementById('modalProductName').innerText = nama;
        document.getElementById('modalInputStok').value = stokLama;
        document.getElementById('adjustForm').action = '/admin/stok/' + id + '/adjust';
    }

    function closeAdjustModal() {
        document.getElementById('adjustModal').classList.add('hidden');
    }
</script>
@endpush