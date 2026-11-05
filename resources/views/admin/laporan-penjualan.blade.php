{{--
  Halaman Laporan Penjualan. Kerangka (topbar, sidebar, footer, menu geser di HP) sekarang ada di
  layouts/admin.blade.php, jadi file ini hanya berisi isi laporan: ringkasan, grafik, dan tabel detail.
  Variabel dari controller tidak berubah: $startDate, $endDate, $search, $kartu, $labelsGrafik, $dataGrafik,
  $kategoriLabels, $kategoriData, $totalKategori, $listDetail.
--}}
@php
    // Variabel dari layout tidak otomatis terbaca oleh halaman anak, jadi helper ikon didefinisikan lagi di sini
    $ikon = fn (string $n, int $u = 18): string => \App\Support\Ikon::svg($n, $u);

    $warnaKategori = ['#7B1830', '#A23A55', '#BF607A', '#D68DA1', '#E8B3C1', '#F5D9E0'];
@endphp
@extends('layouts.admin')

@section('title', 'Laporan Penjualan')

{{-- Filter periode sungguhan, menggantikan chip tanggal bawaan di topbar --}}
@section('top-tanggal')
    <form method="GET" action="{{ route('admin.laporan.index') }}" class="date-pill" title="Pilih periode laporan">
        {!! $ikon('calendar', 18) !!}
        <input type="date" name="start_date" value="{{ $startDate }}" aria-label="Dari tanggal">
        <span class="sd">s/d</span>
        <input type="date" name="end_date" value="{{ $endDate }}" aria-label="Sampai tanggal">
        @if ($search !== '')
            <input type="hidden" name="search" value="{{ $search }}">
        @endif
        <button type="submit">Terapkan</button>
    </form>
@endsection

{{-- CSS khusus halaman laporan (dulu bercampur dengan CSS layout) --}}
@push('styles')
<style>
    /* Warna tambahan yang hanya dipakai halaman ini */
    :root { --maroon-gelap: #6A1228; --pink-tabel: #FBE3E9; }

    /* Filter periode di topbar */
    .date-pill {
        display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid var(--pink-garis);
        border-radius: 10px; padding: 6px 10px; color: var(--maroon);
    }
    .date-pill input[type=date] {
        border: none; background: transparent; font-size: 12.5px; color: var(--teks); width: 118px; outline: none;
    }
    .date-pill .sd { font-size: 11px; color: var(--abu); }
    .date-pill button {
        border: none; background: var(--maroon); color: #fff; font-size: 11px; font-weight: 600;
        padding: 5px 10px; border-radius: 7px; cursor: pointer;
    }
    .date-pill button:hover { background: var(--maroon-gelap); }

    /* ---------- Isi halaman ---------- */
    .page-title-area { display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; flex-wrap: wrap; margin-bottom: 16px; }
    .page-title-area h1 { font-size: 26px; color: var(--maroon); font-weight: 600; line-height: 1.2; }
    .page-title-area p { font-size: 12px; color: var(--abu); margin-top: 2px; }
    .btn-group { display: flex; gap: 10px; }
    .btn-action {
        display: inline-flex; align-items: center; gap: 8px; background: var(--pink-tabel); color: var(--maroon);
        border: 1px solid var(--pink-garis); padding: 9px 18px; border-radius: 9px; font-size: 12px; font-weight: 500; cursor: pointer;
    }
    .btn-action:hover { background: #f6d3dc; }
    button.btn-action:disabled { opacity: .6; cursor: wait; }

    /* Kartu ringkasan */
    .cards-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 16px; }
    .card-stat {
        position: relative; overflow: hidden; background: var(--pink-kartu); border: 1px solid var(--pink-garis);
        border-radius: 12px; padding: 14px 16px 12px;
    }
    .card-head { display: flex; align-items: center; gap: 10px; }
    .card-icon { width: 36px; height: 36px; background: var(--maroon); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .card-head .title { font-size: 12.5px; font-weight: 600; color: var(--maroon); }
    .card-stat .value { font-size: 28px; font-weight: 600; color: var(--maroon); margin: 8px 0 4px; line-height: 1.1; position: relative; z-index: 1; }
    .tren { display: flex; align-items: center; gap: 5px; font-size: 10.5px; position: relative; z-index: 1; }
    .tren span { color: var(--abu); }
    .tren.naik { color: var(--hijau); }
    .tren.turun { color: var(--merah); }
    .tren.turun .ikon { transform: rotate(90deg); }
    .watermark { position: absolute; right: -6px; bottom: -10px; width: 90px; height: 90px; color: var(--maroon); opacity: .08; }

    /* Grafik */
    .charts-row { display: grid; grid-template-columns: 1.75fr 1fr; gap: 16px; margin-bottom: 16px; }
    .chart-box { background: #fff; border: 1px solid var(--pink-garis); border-radius: 12px; padding: 16px 18px; }
    .chart-title { display: flex; align-items: center; gap: 8px; color: var(--maroon); font-weight: 600; font-size: 17px; }
    .chart-sub { font-size: 11px; color: var(--abu); margin: 1px 0 8px 30px; }
    .chart-wrap { height: 230px; position: relative; }

    .donut-area { display: flex; align-items: center; gap: 14px; margin-top: 12px; }
    .donut-area .donut { position: relative; width: 190px; height: 190px; flex-shrink: 0; }
    .legend { list-style: none; flex: 1; min-width: 0; }
    .legend li { display: flex; align-items: center; gap: 8px; font-size: 11.5px; padding: 6px 0; color: var(--teks); }
    .legend .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .legend .nm { flex: 1; }
    .legend .jml { color: var(--abu); font-size: 11px; white-space: nowrap; }

    /* Tabel */
    .table-box { background: #fff; border: 1px solid var(--pink-garis); border-radius: 12px; padding: 16px 18px; overflow-x: auto; }
    .table-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
    .search-box {
        display: flex; align-items: center; gap: 8px; border: 1px solid #ddd; border-radius: 9px;
        padding: 7px 12px; color: #555; min-width: 220px;
    }
    .search-box input { border: none; outline: none; font-size: 12px; width: 100%; background: transparent; }
    table { width: 100%; border-collapse: collapse; }
    th { background: var(--pink-tabel); color: var(--maroon); font-size: 11.5px; font-weight: 600; padding: 10px 12px; text-align: left; }
    th:first-child { border-radius: 8px 0 0 8px; }
    th:last-child { border-radius: 0 8px 8px 0; }
    td { font-size: 11.5px; padding: 10px 12px; border-bottom: 1px solid #eee; color: #444; }
    .kosong { text-align: center; color: #999; padding: 25px; }
    .table-foot { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 11px; color: var(--abu); flex-wrap: wrap; gap: 10px; }
    .paging { display: flex; gap: 6px; }
    .paging span { min-width: 26px; height: 26px; padding: 0 6px; border: 1px solid #e3e3e3; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11.5px; color: #555; }
    .paging { flex-wrap: wrap; }
    .paging a { min-width: 26px; height: 26px; padding: 0 6px; border: 1px solid #e3e3e3; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11.5px; color: #555; text-decoration: none; }
    .paging a:hover { background: var(--pink-tabel); border-color: var(--pink-garis); color: var(--maroon); }
    .paging span.aktif { background: var(--maroon); border-color: var(--maroon); color: #fff; }
    .paging span.nonaktif { opacity: .4; }
    .paging span.titik { border: none; }

    @media (max-width: 1100px) {
        .cards-grid { grid-template-columns: repeat(2, 1fr); }
        .charts-row { grid-template-columns: 1fr; }
    }

    @media screen and (max-width: 900px) {
        .date-pill input[type=date] { flex: 1 1 110px; min-width: 0; width: auto; font-size: 14px; }
        .date-pill button { flex: 1 1 100%; padding: 8px; }
        .page-title-area h1 { font-size: 22px; }
        .cards-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .card-stat .value { font-size: 24px; }
        .chart-box, .table-box { padding: 14px; }
        .chart-wrap { height: 260px; }
        .btn-group { width: 100%; }
        .btn-action { flex: 1; justify-content: center; }
        .search-box { min-width: 0; width: 100%; }
        table { min-width: 640px; }
        .table-foot { justify-content: center; }
    }

    @media screen and (max-width: 520px) {
        .cards-grid { grid-template-columns: 1fr; }
        .donut-area { flex-direction: column; align-items: stretch; }
        .donut-area .donut { margin: 0 auto; }
        .chart-sub { margin-left: 0; }
    }

    /* Tombol Cetak: sembunyikan kerangka admin supaya yang tercetak hanya isi laporan */
    @media print {
        .sidebar, .sidebar-backdrop, .menu-toggle, .footer-admin, .btn-group, .topbar .top-kanan, .search-box, .paging { display: none !important; }
        .topbar { position: static; border: none; }
        .main-content { margin: 0; }
        .area-konten { padding: 10px 0; }
    }
</style>
@endpush

@section('content')
    <div class="page-title-area">
        <div>
            <h1>Laporan Penjualan</h1>
            <p>Berikut adalah ringkasan dan detail penjualan Royal Florist periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}.</p>
        </div>
        <div class="btn-group">
            <button type="button" id="btnExportPdf" class="btn-action" data-url="{{ route('admin.laporan.index', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search, 'format' => 'json'])) }}" title="Unduh laporan lengkap periode ini sebagai file PDF">{!! $ikon('download', 17) !!} Export PDF</button>
            <button type="button" onclick="window.print()" class="btn-action">{!! $ikon('printer', 17) !!} Cetak</button>
        </div>
    </div>

    <section class="cards-grid">
        @foreach ($kartu as $k)
        <div class="card-stat">
            <svg class="watermark"><use href="#bunga"/></svg>
            <div class="card-head">
                <div class="card-icon">{!! $ikon($k['ikon'], 19) !!}</div>
                <div class="title">{{ $k['judul'] }}</div>
            </div>
            <div class="value">{{ $k['nilai'] }}</div>
            @if ($k['tren'] === null)
                <div class="tren netral"><span>Belum ada pembanding</span></div>
            @else
                <div class="tren {{ $k['tren'] >= 0 ? 'naik' : 'turun' }}">
                    {!! $ikon('trend', 14) !!}<b>{{ $k['tren'] >= 0 ? '+' : '' }}{{ $k['tren'] }}%</b><span>dari periode sebelumnya</span>
                </div>
            @endif
        </div>
        @endforeach
    </section>

    <section class="charts-row">
        <div class="chart-box">
            <div class="chart-title">{!! $ikon('aktivitas', 22) !!} Grafik Penjualan</div>
            <div class="chart-sub">Total penjualan per hari</div>
            <div class="chart-wrap"><canvas id="lineChartPenjualan"></canvas></div>
        </div>
        <div class="chart-box">
            <div class="chart-title">{!! $ikon('jam', 22) !!} Kategori Produk Terlaris</div>
            <div class="donut-area">
                <div class="donut"><canvas id="doughnutChartKategori"></canvas></div>
                <ul class="legend">
                    @foreach ($kategoriLabels as $i => $nama)
                        @php
                            $jml = $kategoriData[$i];
                            $persen = $totalKategori > 0 ? round($jml / $totalKategori * 100) : 0;
                        @endphp
                        <li>
                            <span class="dot" style="background: {{ $warnaKategori[$i % count($warnaKategori)] }}"></span>
                            <span class="nm">{{ $nama }}</span>
                            <span class="jml">{{ $jml }} ({{ $persen }}%)</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="table-box">
        <div class="table-head">
            <div class="chart-title">{!! $ikon('tas', 22) !!} Detail Penjualan</div>
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="search-box">
                {!! $ikon('cari', 16) !!}
                <input type="text" name="search" placeholder="Cari produk..." value="{{ $search }}" aria-label="Cari produk">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Jumlah Terjual</th>
                    <th>Harga Satuan</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($listDetail as $row)
                    <tr>
                        <td>{{ $listDetail->firstItem() + $loop->index }}</td>
                        <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $row->nama_produk }}</td>
                        <td>{{ $row->kategori }}</td>
                        <td>{{ (int) $row->jumlah }}</td>
                        <td>Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format((float) $row->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="kosong">Data penjualan tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="table-foot">
            <div>
                @if ($listDetail->total() > 0)
                    Menampilkan {{ $listDetail->firstItem() }}&ndash;{{ $listDetail->lastItem() }} dari {{ $listDetail->total() }} data
                @else
                    Tidak ada data
                @endif
            </div>
            @include('admin.partials.pagination', ['paginator' => $listDetail])
        </div>
    </section>
@endsection

{{-- Library dimuat dulu, baru kode yang memakainya. Urutan push ini tidak boleh dibalik --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@4.2.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@5.0.8/dist/jspdf.plugin.autotable.min.js"></script>
    <script>
        const rupiah = v => 'Rp ' + Number(v).toLocaleString('id-ID');
        const dataPenjualan = @json($dataGrafik);

        // Angka di atas titik grafik (hanya kalau titiknya tidak terlalu banyak)
        const labelTitik = {
            id: 'labelTitik',
            afterDatasetsDraw(chart) {
                if (dataPenjualan.length > 10 || window.innerWidth < 700) return;
                const { ctx } = chart;
                ctx.save();
                ctx.font = '600 10px Inter, sans-serif';
                ctx.fillStyle = '#7B1830';
                ctx.textAlign = 'center';
                chart.getDatasetMeta(0).data.forEach((titik, i) => {
                    ctx.fillText(rupiah(dataPenjualan[i]), titik.x, titik.y - 10);
                });
                ctx.restore();
            }
        };

        new Chart(document.getElementById('lineChartPenjualan').getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($labelsGrafik),
                datasets: [{
                    label: 'Penjualan',
                    data: dataPenjualan,
                    borderColor: '#7B1830',
                    borderWidth: 1.8,
                    backgroundColor: 'rgba(245, 205, 216, 0.55)',
                    fill: true,
                    tension: 0,
                    pointRadius: 3.5,
                    pointBackgroundColor: '#7B1830',
                    pointBorderColor: '#7B1830'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 22, right: 28 } },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => rupiah(c.parsed.y) } }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: '#666', font: { size: 10 } } },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: '#f0e3e7' },
                        ticks: { color: '#666', font: { size: 10 }, maxTicksLimit: 5, callback: v => window.innerWidth < 700 ? (v >= 1000000 ? (v / 1000000).toString().replace('.', ',') + ' jt' : (v / 1000) + ' rb') : rupiah(v) }
                    }
                }
            },
            plugins: [labelTitik]
        });

        // Teks di tengah donat
        const teksTengah = {
            id: 'teksTengah',
            afterDraw(chart) {
                const { ctx, chartArea: a } = chart;
                const x = (a.left + a.right) / 2, y = (a.top + a.bottom) / 2;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.fillStyle = '#7B1830';
                ctx.font = '600 11px Inter, sans-serif';
                ctx.fillText('Total Terjual', x, y - 14);
                ctx.font = '700 24px Inter, sans-serif';
                ctx.fillText(@json((string) $totalKategori), x, y + 10);
                ctx.font = '400 11px Inter, sans-serif';
                ctx.fillText('produk', x, y + 26);
                ctx.restore();
            }
        };

        new Chart(document.getElementById('doughnutChartKategori').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json($kategoriLabels),
                datasets: [{
                    data: @json($kategoriData),
                    backgroundColor: @json($warnaKategori),
                    borderColor: '#fff',
                    borderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: { legend: { display: false } }
            },
            plugins: [teksTengah]
        });
    </script>
    <script>
        /* ---------- Export PDF (dibuat di browser, tanpa paket Composer) ---------- */
        function buatLaporanPdf(jsPDF, d) {
            var doc = new jsPDF({ unit: 'pt', format: 'a4' });
            var lebar = doc.internal.pageSize.getWidth();
            var tinggi = doc.internal.pageSize.getHeight();
            var m = 36;
            var maroon = [123, 24, 48];
            var pink = [251, 227, 233];

            var rp = function (v) { return 'Rp ' + Math.round(Number(v) || 0).toLocaleString('id-ID'); };
            var tgl = function (s) {
                var p = String(s).slice(0, 10).split('-');
                return p[2] + '/' + p[1] + '/' + p[0];
            };
            var kanan = function (t) { return { content: t, styles: { halign: 'right' } }; };
            var dua = function (n) { return (n < 10 ? '0' : '') + n; };
            var sekarang = new Date();
            var dicetak = dua(sekarang.getDate()) + '/' + dua(sekarang.getMonth() + 1) + '/' + sekarang.getFullYear()
                + ' ' + dua(sekarang.getHours()) + ':' + dua(sekarang.getMinutes());
            var periode = tgl(d.startDate) + ' s/d ' + tgl(d.endDate);

            /* Kop laporan */
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(20);
            doc.setTextColor(maroon[0], maroon[1], maroon[2]);
            doc.text('Royal Florist', m, 50);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);
            doc.setTextColor(111, 111, 111);
            doc.text('Fresh Flower - Special Moment', m, 64);

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(14);
            doc.setTextColor(maroon[0], maroon[1], maroon[2]);
            doc.text('Laporan Penjualan', lebar - m, 46, { align: 'right' });
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);
            doc.setTextColor(43, 43, 43);
            doc.text('Periode: ' + periode, lebar - m, 60, { align: 'right' });
            doc.setTextColor(111, 111, 111);
            doc.text('Dicetak ' + dicetak + ' oleh ' + d.namaAdmin, lebar - m, 72, { align: 'right' });

            doc.setDrawColor(maroon[0], maroon[1], maroon[2]);
            doc.setLineWidth(2);
            doc.line(m, 82, lebar - m, 82);

            var y = 104;

            function judul(teks) {
                if (y > tinggi - 140) { doc.addPage(); y = 50; }
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(12);
                doc.setTextColor(maroon[0], maroon[1], maroon[2]);
                doc.text(teks, m, y);
                doc.setDrawColor(243, 204, 214);
                doc.setLineWidth(1);
                doc.line(m, y + 5, lebar - m, y + 5);
                y += 14;
            }

            function tabel(opsi) {
                doc.autoTable(Object.assign({
                    startY: y,
                    margin: { left: m, right: m, bottom: 46 },
                    theme: 'striped',
                    styles: { font: 'helvetica', fontSize: 9, cellPadding: 5, textColor: [43, 43, 43] },
                    headStyles: { fillColor: pink, textColor: maroon, fontStyle: 'bold' },
                    footStyles: { fillColor: pink, textColor: maroon, fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [252, 246, 248] },
                    showFoot: 'lastPage'
                }, opsi));
                y = doc.lastAutoTable.finalY + 24;
            }

            /* 1. Ringkasan */
            judul('Ringkasan');
            tabel({
                head: [['Keterangan', kanan('Nilai'), 'Perbandingan']],
                body: d.kartu.map(function (k) {
                    var tren = k.tren === null || k.tren === undefined
                        ? 'Belum ada pembanding'
                        : (k.tren >= 0 ? '+' : '') + k.tren + '% dari periode sebelumnya';
                    return [k.judul, k.nilai, tren];
                }),
                columnStyles: { 1: { halign: 'right' } }
            });

            /* 2. Penjualan per hari */
            judul('Penjualan per Hari');
            tabel({
                head: [['Tanggal', 'Hari', kanan('Total Penjualan')]],
                body: d.rincianHarian.map(function (h) { return [h.tanggal, h.hari, rp(h.total)]; }),
                foot: [[{ content: 'Total', colSpan: 2 }, kanan(rp(d.totalPendapatan))]],
                columnStyles: { 2: { halign: 'right' } }
            });

            /* 3. Kategori terlaris */
            judul('Kategori Produk Terlaris');
            var totalKategori = d.kategori.reduce(function (a, k) { return a + Number(k.jumlah); }, 0);
            tabel({
                head: [['Kategori', kanan('Jumlah Terjual'), kanan('Persentase')]],
                body: d.kategori.length
                    ? d.kategori.map(function (k) {
                        var persen = totalKategori > 0 ? Math.round(Number(k.jumlah) / totalKategori * 100) : 0;
                        return [k.nama, String(k.jumlah), persen + '%'];
                    })
                    : [[{ content: 'Belum ada data penjualan pada periode ini.', colSpan: 3, styles: { halign: 'center', textColor: [111, 111, 111] } }]],
                columnStyles: { 1: { halign: 'right' }, 2: { halign: 'right' } }
            });

            /* 4. Detail penjualan (semua baris, bukan hanya satu halaman) */
            judul('Detail Penjualan');
            if (d.search) {
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(9);
                doc.setTextColor(111, 111, 111);
                doc.text('Filter produk: "' + d.search + '"', m, y);
                y += 8;
            }
            var jumlahTotal = 0;
            var hargaTotal = 0;
            d.detail.forEach(function (r) {
                jumlahTotal += Number(r.jumlah) || 0;
                hargaTotal += Number(r.total) || 0;
            });
            tabel({
                head: [['No', 'Tanggal', 'Nama Produk', 'Kategori', kanan('Jml'), kanan('Harga Satuan'), kanan('Total Harga')]],
                body: d.detail.length
                    ? d.detail.map(function (r, i) {
                        return [String(i + 1), r.tanggal ? tgl(r.tanggal) : '-', r.nama_produk, r.kategori,
                            String(Number(r.jumlah) || 0), rp(r.harga_satuan), rp(r.total)];
                    })
                    : [[{ content: 'Data penjualan tidak ditemukan.', colSpan: 7, styles: { halign: 'center', textColor: [111, 111, 111] } }]],
                foot: d.detail.length
                    ? [[{ content: 'Total (' + d.detail.length + ' baris)', colSpan: 4 }, kanan(String(jumlahTotal)), '', kanan(rp(hargaTotal))]]
                    : undefined,
                columnStyles: {
                    0: { cellWidth: 28 },
                    4: { halign: 'right', cellWidth: 34 },
                    5: { halign: 'right' },
                    6: { halign: 'right' }
                }
            });

            /* Footer + nomor halaman di setiap halaman */
            var total = doc.getNumberOfPages();
            for (var i = 1; i <= total; i++) {
                doc.setPage(i);
                doc.setDrawColor(243, 204, 214);
                doc.setLineWidth(1);
                doc.line(m, tinggi - 34, lebar - m, tinggi - 34);
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(8);
                doc.setTextColor(111, 111, 111);
                doc.text('Royal Florist - Laporan Penjualan ' + periode, m, tinggi - 22);
                doc.text('Halaman ' + i + ' dari ' + total, lebar - m, tinggi - 22, { align: 'right' });
            }

            return doc;
        }

        document.getElementById('btnExportPdf').addEventListener('click', function () {
            var tombol = this;
            var labelAsal = tombol.innerHTML;

            if (!window.jspdf) {
                alert('Library PDF belum termuat. Periksa koneksi internet lalu muat ulang halaman.');
                return;
            }

            tombol.disabled = true;
            tombol.textContent = 'Menyiapkan PDF...';

            fetch(tombol.dataset.url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (res) {
                    if (!res.ok) { throw new Error('Gagal mengambil data (kode ' + res.status + ').'); }
                    return res.json();
                })
                .then(function (d) {
                    var doc = buatLaporanPdf(window.jspdf.jsPDF, d);
                    doc.save('laporan-penjualan_' + d.startDate + '_sd_' + d.endDate + '.pdf');
                })
                .catch(function (err) {
                    var pesan = (err instanceof SyntaxError)
                        ? 'Sesi login mungkin sudah habis. Muat ulang halaman lalu coba lagi.'
                        : err.message;
                    alert('Export PDF gagal: ' + pesan);
                })
                .finally(function () {
                    tombol.disabled = false;
                    tombol.innerHTML = labelAsal;
                });
        });
    </script>
@endpush