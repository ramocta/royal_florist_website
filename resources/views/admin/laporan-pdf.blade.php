<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Royal Florist</title>
    <style>
        @page { margin: 28px 30px 48px 30px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #2B2B2B; }
        h1 { font-size: 20px; color: #7B1830; margin: 0; }
        h2 { font-size: 12px; color: #7B1830; margin: 18px 0 6px; border-bottom: 1.5px solid #F3CCD6; padding-bottom: 4px; }
        .muted { color: #6F6F6F; }
        .kanan { text-align: right; }
        .tengah { text-align: center; }

        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .header { border-bottom: 3px solid #7B1830; }
        .header td { padding-bottom: 8px; }

        .ringkasan td { width: 25%; padding: 4px; }
        .kotak { background: #FAE6EB; border: 1px solid #F3CCD6; padding: 8px 10px; }
        .kotak .judul { font-size: 8.5px; font-weight: bold; color: #7B1830; }
        .kotak .nilai { font-size: 14px; font-weight: bold; color: #7B1830; margin: 5px 0 3px; }
        .kotak .tren { font-size: 7.5px; color: #6F6F6F; }

        .data th { background: #FBE3E9; color: #7B1830; font-size: 9px; text-align: left; padding: 6px 7px; border-bottom: 1px solid #F3CCD6; }
        .data td { padding: 5px 7px; border-bottom: 1px solid #eeeeee; }
        .data tr { page-break-inside: avoid; }
        .data thead { display: table-header-group; }
        .data .total td { font-weight: bold; background: #FBE3E9; color: #7B1830; border-bottom: none; }

        .footer { position: fixed; bottom: -30px; left: 0; right: 0; font-size: 8px; color: #6F6F6F; border-top: 1px solid #F3CCD6; padding-top: 5px; }
        .footer .halaman:after { content: counter(page); }
    </style>
</head>
<body>

    <div class="footer">
        <table>
            <tr>
                <td>Royal Florist &bull; Laporan Penjualan {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
                <td class="kanan">Halaman <span class="halaman"></span></td>
            </tr>
        </table>
    </div>

    <table class="header">
        <tr>
            <td>
                <h1>Royal Florist</h1>
                <div class="muted">Fresh Flower &bull; Special Moment</div>
            </td>
            <td class="kanan">
                <div style="font-size: 14px; font-weight: bold; color: #7B1830;">Laporan Penjualan</div>
                <div>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
                <div class="muted">Dicetak {{ $dicetakPada }} oleh {{ $dicetakOleh }}</div>
            </td>
        </tr>
    </table>

    {{-- Ringkasan --}}
    <h2>Ringkasan</h2>
    <table class="ringkasan">
        <tr>
            @foreach ($kartu as $k)
                <td>
                    <div class="kotak">
                        <div class="judul">{{ $k['judul'] }}</div>
                        <div class="nilai">{{ $k['nilai'] }}</div>
                        <div class="tren">
                            @if ($k['tren'] === null)
                                Belum ada pembanding
                            @else
                                {{ $k['tren'] >= 0 ? '+' : '' }}{{ $k['tren'] }}% dari periode sebelumnya
                            @endif
                        </div>
                    </div>
                </td>
            @endforeach
        </tr>
    </table>

    {{-- Penjualan per hari --}}
    <h2>Penjualan per Hari</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th class="kanan">Total Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rincianHarian as $h)
                <tr>
                    <td>{{ $h['tanggal'] }}</td>
                    <td>{{ $h['hari'] }}</td>
                    <td class="kanan">Rp {{ number_format($h['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="2">Total</td>
                <td class="kanan">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Kategori terlaris --}}
    <h2>Kategori Produk Terlaris</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="kanan">Jumlah Terjual</th>
                <th class="kanan">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kategoriLabels as $i => $nama)
                <tr>
                    <td>{{ $nama }}</td>
                    <td class="kanan">{{ $kategoriData[$i] }}</td>
                    <td class="kanan">{{ $totalKategori > 0 ? round($kategoriData[$i] / $totalKategori * 100) : 0 }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" class="tengah muted">Belum ada data penjualan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Detail penjualan (semua baris) --}}
    <h2>Detail Penjualan</h2>
    @if ($search !== '')
        <div class="muted" style="margin-bottom: 4px;">Filter produk: "{{ $search }}"</div>
    @endif
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th class="kanan">Jumlah</th>
                <th class="kanan">Harga Satuan</th>
                <th class="kanan">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($listDetail as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $row->nama_produk }}</td>
                    <td>{{ $row->kategori }}</td>
                    <td class="kanan">{{ (int) $row->jumlah }}</td>
                    <td class="kanan">Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}</td>
                    <td class="kanan">Rp {{ number_format((float) $row->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="tengah muted">Data penjualan tidak ditemukan.</td></tr>
            @endforelse
            @if ($listDetail->count() > 0)
                <tr class="total">
                    <td colspan="4">Total ({{ $listDetail->count() }} baris)</td>
                    <td class="kanan">{{ (int) $listDetail->sum('jumlah') }}</td>
                    <td></td>
                    <td class="kanan">Rp {{ number_format((float) $listDetail->sum('total'), 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>