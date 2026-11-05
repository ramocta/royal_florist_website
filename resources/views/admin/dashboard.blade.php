{{--
  Halaman Dashboard. Kerangka (topbar, sidebar, footer) sekarang ada di layouts/admin.blade.php,
  jadi file ini hanya berisi isi dashboard: kartu statistik dan grafik penjualan.
  Variabel $kartu, $selectedDate, $labelsGrafik, $dataGrafik tetap dikirim dari controller seperti sebelumnya.
--}}
@php
    // Dipakai di sini karena variabel dari layout tidak otomatis terbaca oleh halaman anak
    $ikon = fn (string $n, int $u = 18): string => \App\Support\Ikon::svg($n, $u);
    $namaAdmin = auth()->user()?->nama ?? 'Admin';
@endphp
@extends('layouts.admin')

@section('title', 'Dashboard Admin')

{{-- Filter tanggal sungguhan, menggantikan chip tanggal bawaan di topbar --}}
@section('top-tanggal')
    <form method="GET" action="{{ route('admin.dashboard') }}" id="dateFilterForm" class="date-pill">
        {!! $ikon('calendar', 18) !!}
        <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="document.getElementById('dateFilterForm').submit();">
    </form>
@endsection

{{-- CSS khusus dashboard (dulu bercampur dengan CSS layout) --}}
@push('styles')
<style>
    .welcome-section h1 { font-size: 27px; color: var(--maroon); font-weight: 600; line-height: 1.2; }
    .welcome-section p { font-size: 12.5px; color: #555; margin: 4px 0 26px; }

    /* Kartu statistik */
    .cards-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
    .card {
        position: relative; overflow: hidden; background: var(--pink-kartu); border: 1px solid var(--pink-garis);
        border-radius: 12px; padding: 16px 18px 14px;
    }
    .card-head { display: flex; align-items: center; gap: 12px; }
    .card-icon { width: 40px; height: 40px; background: #A23A55; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .card-title { font-size: 12.5px; font-weight: 600; color: var(--maroon); }
    .card-value { font-size: 30px; font-weight: 600; color: var(--maroon); margin: 14px 0 16px; line-height: 1.1; position: relative; z-index: 1; }
    .card-trend { display: flex; align-items: center; gap: 6px; font-size: 10.5px; color: var(--abu); position: relative; z-index: 1; }
    .card-trend .naik { display: flex; align-items: center; gap: 4px; color: var(--hijau); font-weight: 600; }
    .card-trend .turun { display: flex; align-items: center; gap: 4px; color: var(--merah); font-weight: 600; }
    .card-trend .turun .ikon { transform: rotate(90deg); }
    .watermark { position: absolute; right: -6px; bottom: -10px; width: 100px; height: 100px; color: var(--maroon); opacity: .08; }

    /* Grafik */
    .chart-card { background: #fff; border: 1px solid var(--pink-garis); border-radius: 12px; padding: 20px 22px; }
    .chart-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; gap: 12px; flex-wrap: wrap; }
    .chart-title { display: flex; align-items: center; gap: 12px; color: var(--maroon); }
    .chart-title h3 { font-size: 20px; font-weight: 600; line-height: 1.2; }
    .chart-title p { font-size: 11px; color: var(--abu); margin-top: 1px; }
    .date-chip {
        display: flex; align-items: center; gap: 8px; border: 1px solid var(--pink-garis); border-radius: 9px;
        padding: 8px 14px; font-size: 12px; color: var(--maroon); background: #fff;
    }
    .chart-wrap { position: relative; height: 300px; width: 100%; }

    @media (max-width: 1100px) { .cards-grid { grid-template-columns: repeat(2, 1fr); } }
    @media screen and (max-width: 900px) {
        .welcome-section h1 { font-size: 22px; }
        .welcome-section p { margin-bottom: 18px; }
        .cards-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .card-value { font-size: 24px; }
        .chart-card { padding: 14px; }
        .chart-title h3 { font-size: 17px; }
        .chart-wrap { height: 260px; }
    }
    @media screen and (max-width: 520px) { .cards-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    <div class="welcome-section">
        <h1>Dashboard Admin</h1>
        <p>Selamat datang, {{ $namaAdmin }}! Berikut ringkasan data penjualan Royal Florist.</p>
    </div>

    <!-- CARDS STATISTIK -->
    <section class="cards-grid">
        @foreach ($kartu as $k)
        <div class="card">
            <svg class="watermark"><use href="#bunga"/></svg>
            <div class="card-head">
                <div class="card-icon">{!! $ikon($k['ikon'], 20) !!}</div>
                <div class="card-title">{{ $k['judul'] }}</div>
            </div>
            <div class="card-value">{{ $k['nilai'] }}</div>
            <div class="card-trend">
                @if ($k['info']['teks'] !== '')
                    <span class="{{ $k['info']['kelas'] }}">{!! $ikon('trend', 14) !!}{{ $k['info']['teks'] }}</span>
                @endif
                <span>{{ $k['info']['ket'] }}</span>
            </div>
        </div>
        @endforeach
    </section>

    <!-- GRAFIK PENJUALAN -->
    <section class="chart-card">
        <div class="chart-header">
            <div class="chart-title">
                {!! $ikon('batang', 30) !!}
                <div>
                    <h3>Grafik Penjualan</h3>
                    <p>Data penjualan selama 7 hari terakhir</p>
                </div>
            </div>
            <div class="date-chip">
                {!! $ikon('calendar', 17) !!}
                {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                {!! $ikon('chevron', 15) !!}
            </div>
        </div>
        <div class="chart-wrap">
            <canvas id="salesChart"></canvas>
        </div>
    </section>
@endsection

{{-- Chart.js hanya dimuat di halaman ini. Urutan push penting: library dulu, baru kode yang memakainya --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script>
        Chart.register(ChartDataLabels);

        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($labelsGrafik),
                datasets: [{
                    label: 'Pendapatan',
                    data: @json($dataGrafik),
                    backgroundColor: '#A84860',
                    borderRadius: 3,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 25 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#7B1830',
                        font: { weight: '600', size: 10.5 },
                        formatter: function(value) {
                            if (window.innerWidth < 700) {
                                if (value === 0) return '';
                                return value >= 1000000
                                    ? (value / 1000000).toFixed(1).replace('.0', '').replace('.', ',') + ' jt'
                                    : Math.round(value / 1000) + ' rb';
                            }
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { color: '#e5d6da' },
                        ticks: { color: '#555', font: { size: 10.5 } }
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: 2500000,
                        border: { display: false },
                        grid: { color: '#efe4e7' },
                        ticks: {
                            stepSize: 500000,
                            color: '#555',
                            font: { size: 10.5 },
                            callback: function(value) {
                                if (window.innerWidth < 700) {
                                    return value === 0 ? '0' : (value / 1000000).toString().replace('.', ',') + ' jt';
                                }
                                return value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush