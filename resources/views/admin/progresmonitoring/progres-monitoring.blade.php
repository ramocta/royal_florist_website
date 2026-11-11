@extends('layouts.admin')

@section('title', 'Order Progress Monitoring')

@section('styles')
    <style>
        /* ==========================================
   ROYAL FLORIST - PURE CSS MONITORING PROGRESS
   ========================================== */

:root {
    --rf-maroon-primary: #70132A;
    --rf-maroon-hover: #540D1E;
    --rf-maroon-light: #FFF0F3;
    --rf-maroon-accent: #A02344;
    --rf-border-radius: 12px;
}

/* Container Utama */
.rf-container {
    padding: 24px;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    color: #333;
}

/* Header Section */
.rf-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.rf-header h3 {
    margin: 0 0 4px 0;
    font-size: 22px;
    font-weight: 700;
}

.rf-header p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.rf-header-actions {
    display: flex;
    gap: 8px;
}

/* Grid Layout 4 Card Atas */
.rf-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

@media (max-width: 992px) {
    .rf-summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .rf-summary-grid {
        grid-template-columns: 1fr;
    }
}

/* Card Styling */
.rf-card {
    background: #ffffff;
    border-radius: var(--rf-border-radius);
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.rf-card-summary {
    background-color: var(--rf-maroon-light);
}

.rf-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.rf-card-title {
    font-size: 14px;
    color: #6c757d;
    font-weight: 600;
}

.rf-icon-box {
    background-color: var(--rf-maroon-accent);
    color: #ffffff;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 18px;
}

.rf-card-number {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: #1a1a1a;
}

/* Buttons */
.rf-btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    border: 1px solid transparent;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.rf-btn-maroon {
    background-color: var(--rf-maroon-primary);
    color: #ffffff;
}

.rf-btn-maroon:hover {
    background-color: var(--rf-maroon-hover);
}

.rf-btn-outline {
    border-color: #cccccc;
    background: #ffffff;
    color: #333333;
}

.rf-btn-action {
    padding: 4px 10px;
    border: 1px solid var(--rf-maroon-primary);
    color: var(--rf-maroon-primary);
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
}

.rf-btn-action:hover {
    background-color: var(--rf-maroon-primary);
    color: #ffffff;
}

/* Grid Middle Section (Progress Bar & Donut Chart) */
.rf-middle-grid {
    display: grid;
    grid-template-columns: 7fr 5fr;
    gap: 16px;
    margin-bottom: 24px;
}

@media (max-width: 992px) {
    .rf-middle-grid {
        grid-template-columns: 1fr;
    }
}

/* Progress Bar Custom */
.rf-progress-item {
    margin-bottom: 16px;
}

.rf-progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
}

.rf-progress-track {
    background-color: #e9ecef;
    height: 10px;
    border-radius: 10px;
    overflow: hidden;
}

.rf-progress-bar {
    height: 100%;
    border-radius: 10px;
}

.bg-warning { background-color: #ffc107; }
.bg-success { background-color: #198754; }
.bg-maroon { background-color: var(--rf-maroon-primary); }

/* Donut Chart Box Layout */
.rf-chart-flex {
    display: flex;
    align-items: center;
    gap: 16px;
}

.rf-chart-box {
    position: relative;
    width: 140px;
    height: 140px;
    flex-shrink: 0;
}

.rf-chart-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
}

.rf-chart-overlay .num {
    font-size: 18px;
    font-weight: 700;
    line-height: 1;
    display: block;
}

.rf-chart-overlay .lbl {
    font-size: 10px;
    color: #6c757d;
    display: block;
}

.rf-legend-list {
    list-style: none;
    padding: 0;
    margin: 0;
    flex-grow: 1;
}

.rf-legend-item {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin-bottom: 10px;
}

/* Table Section */
.rf-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.rf-search-group {
    display: flex;
    gap: 8px;
}

.rf-input, .rf-select {
    padding: 6px 12px;
    border: 1px solid #cccccc;
    border-radius: 6px;
    font-size: 13px;
}

.rf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.rf-table th {
    background-color: #fafafa;
    text-align: left;
    padding: 12px;
    border-bottom: 2px solid #eeeeee;
    color: #6c757d;
    font-weight: 600;
}

.rf-table td {
    padding: 12px;
    border-bottom: 1px solid #eeeeee;
}

.rf-table tr:hover {
    background-color: #fdf8f9;
}

/* Badges */
.rf-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge-diproses { background: #fff3cd; color: #856404; }
.badge-dikirim { background: #d1e7dd; color: #0f5132; }
.badge-selesai { background: var(--rf-maroon-primary); color: #ffffff; }
.badge-pending { background: #e2e3e5; color: #41464b; }

/* Styling SVG Icon Universal */
.rf-icon-svg {
    width: 20px;
    height: 20px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Icon Box Bulat Merah Marun pada 4 Card Summary */
.rf-icon-box-rounded {
    background-color: var(--rf-maroon-accent);
    color: #ffffff;
    width: 44px;
    height: 44px;
    border-radius: 50%; /* Membuat icon box bulat sempurna seperti di gambar */
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Modal Styling untuk Tombol Aksi */
.rf-modal-backdrop {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.rf-modal-backdrop.active {
    display: flex;
}
.rf-modal-content {
    background: #ffffff;
    padding: 24px;
    border-radius: 12px;
    width: 100%;
    max-width: 450px;
}
</style>
@endsection

@section('content')
<div class="rf-container">

    <!-- Header Section -->
    <div class="rf-header">
        <div>
            <h3 style="display: flex; align-items: center; gap: 8px;">
                Monitoring Progress
            </h3>
            <p>Pantau perkembangan pesanan dan proses setiap tahap secara real-time.</p>
        </div>
        <div class="rf-header-actions">
            
            <button class="rf-btn rf-btn-maroon" onclick="window.location.reload();">
                <!-- Icon Refresh White -->
                <svg class="rf-icon-svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24"><path d="M23 4v6h-6"></path><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- 4 Summary Cards Grid -->
    <div class="rf-summary-grid">
        <!-- 1. Total Pesanan (Icon Nota / Document) -->
        <div class="rf-card rf-card-summary">
            <div class="rf-card-header">
                <span class="rf-card-title">Total Pesanan</span>
                <div class="rf-icon-box-rounded">
                    <svg class="rf-icon-svg" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
            </div>
            <div class="rf-card-number">{{ $summary['cards']['total_pesanan'] ?? 0 }}</div>
            <small style="color: #198754; font-weight: 600;">↗ +12% <span style="color: #6c757d; font-weight: 400;">dari minggu sebelumnya</span></small>
        </div>

        <!-- 2. Pesanan Diproses (Icon Clock / Jam) -->
        <div class="rf-card rf-card-summary">
            <div class="rf-card-header">
                <span class="rf-card-title">Pesanan Diproses</span>
                <div class="rf-icon-box-rounded">
                    <svg class="rf-icon-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <div class="rf-card-number">{{ $summary['cards']['pesanan_diproses'] ?? 0 }}</div>
            <small style="color: #198754; font-weight: 600;">↗ +33% <span style="color: #6c757d; font-weight: 400;">dari minggu sebelumnya</span></small>
        </div>

        <!-- 3. Pesanan Dikirim (Icon Truck) -->
        <div class="rf-card rf-card-summary">
            <div class="rf-card-header">
                <span class="rf-card-title">Pesanan Dikirim</span>
                <div class="rf-icon-box-rounded">
                    <svg class="rf-icon-svg" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                </div>
            </div>
            <div class="rf-card-number">{{ $summary['cards']['pesanan_dikirim'] ?? 0 }}</div>
            <small style="color: #198754; font-weight: 600;">↗ +25% <span style="color: #6c757d; font-weight: 400;">dari minggu sebelumnya</span></small>
        </div>

        <!-- 4. Pesanan Selesai (Icon Check Circle) -->
        <div class="rf-card rf-card-summary">
            <div class="rf-card-header">
                <span class="rf-card-title">Pesanan Selesai</span>
                <div class="rf-icon-box-rounded">
                    <svg class="rf-icon-svg" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
            <div class="rf-card-number">{{ $summary['cards']['pesanan_selesai'] ?? 0 }}</div>
            <small style="color: #198754; font-weight: 600;">↗ +18% <span style="color: #6c757d; font-weight: 400;">dari minggu sebelumnya</span></small>
        </div>
    </div>

    <!-- Section Middle Grid -->
    <div class="rf-middle-grid">
        <!-- Progress Pesanan (Icon Shopping Bag) -->
        <div class="rf-card">
            <h4 style="margin: 0 0 4px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <svg class="rf-icon-svg" style="color: var(--rf-maroon-primary);" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                Progress Pesanan
            </h4>
            <p style="margin: 0 0 16px 0; color: #6c757d; font-size: 13px;">Persentase pesanan berdasarkan status saat ini.</p>

            <div class="rf-progress-item">
                <div class="rf-progress-info">
                    <span><span style="color: #ffc107;">●</span> Diproses</span>
                    <span>{{ $summary['charts']['diproses']['percentage'] ?? 0 }}% ({{ $summary['charts']['diproses']['count'] ?? 0 }})</span>
                </div>
                <div class="rf-progress-track">
                    <div class="rf-progress-bar bg-warning" style="width: {{ $summary['charts']['diproses']['percentage'] ?? 0 }}%;"></div>
                </div>
            </div>

            <div class="rf-progress-item">
                <div class="rf-progress-info">
                    <span><span style="color: #198754;">●</span> Dikirim</span>
                    <span>{{ $summary['charts']['dikirim']['percentage'] ?? 0 }}% ({{ $summary['charts']['dikirim']['count'] ?? 0 }})</span>
                </div>
                <div class="rf-progress-track">
                    <div class="rf-progress-bar bg-success" style="width: {{ $summary['charts']['dikirim']['percentage'] ?? 0 }}%;"></div>
                </div>
            </div>

            <div class="rf-progress-item">
                <div class="rf-progress-info">
                    <span><span style="color: var(--rf-maroon-primary);">●</span> Selesai</span>
                    <span>{{ $summary['charts']['selesai']['percentage'] ?? 0 }}% ({{ $summary['charts']['selesai']['count'] ?? 0 }})</span>
                </div>
                <div class="rf-progress-track">
                    <div class="rf-progress-bar bg-maroon" style="width: {{ $summary['charts']['selesai']['percentage'] ?? 0 }}%;"></div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Status Pesanan (Icon Check Square / Double Check) -->
        <div class="rf-card">
            <h4 style="margin: 0 0 16px 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <svg class="rf-icon-svg" style="color: var(--rf-maroon-primary);" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                Ringkasan Status Pesanan
            </h4>
            <div class="rf-chart-flex">
                <div class="rf-chart-box">
                    <canvas id="statusChart"></canvas>
                    <div class="rf-chart-overlay">
                        <span class="lbl">Total Pesanan</span>
                        <span class="num">{{ $summary['cards']['total_pesanan'] ?? 0 }}</span>
                    </div>
                </div>
                <ul class="rf-legend-list">
                    <li class="rf-legend-item">
                        <span><span style="color: #ffc107;">●</span> Diproses</span>
                        <strong>{{ $summary['charts']['diproses']['count'] ?? 0 }} ({{ $summary['charts']['diproses']['percentage'] ?? 0 }}%)</strong>
                    </li>
                    <li class="rf-legend-item">
                        <span><span style="color: #198754;">●</span> Dikirim</span>
                        <strong>{{ $summary['charts']['dikirim']['count'] ?? 0 }} ({{ $summary['charts']['dikirim']['percentage'] ?? 0 }}%)</strong>
                    </li>
                    <li class="rf-legend-item">
                        <span><span style="color: var(--rf-maroon-primary);">●</span> Selesai</span>
                        <strong>{{ $summary['charts']['selesai']['count'] ?? 0 }} ({{ $summary['charts']['selesai']['percentage'] ?? 0 }}%)</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="rf-card">
        <div class="rf-table-header">
            <h4 style="margin: 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <!-- Icon Table Document -->
                <svg class="rf-icon-svg" style="color: var(--rf-maroon-primary);" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                Daftar Pesanan & Progress
            </h4>
            <form action="{{ route('admin.progress.index') }}" method="GET" class="rf-search-group">
                <div style="position: relative;">
                    <input type="text" name="search" class="rf-input" style="padding-left: 32px;" placeholder="Cari nama / nomor pesanan..." value="{{ request('search') }}">
                    <!-- Icon Search -->
                    <svg class="rf-icon-svg" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #999;" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <select name="status" class="rf-select" onchange="this.form.submit()">
                    <option value="semua">Semua Status</option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="dikirim" {{ request('status') == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </form>
        </div>

        <table class="rf-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Tanggal Pesan</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $index => $order)
                    @php
                        $step = match(strtolower($order->status_pesanan)) {
                            'diproses' => ['val' => 25, 'label' => '1/4', 'class' => 'bg-warning'],
                            'dikirim'  => ['val' => 75, 'label' => '3/4', 'class' => 'bg-success'],
                            'selesai'  => ['val' => 100, 'label' => '4/4', 'class' => 'bg-maroon'],
                            default    => ['val' => 0, 'label' => '0/4', 'class' => 'bg-secondary'],
                        };
                    @endphp
                    <tr>
                        <td>{{ $orders->firstItem() + $index }}</td>
                        <td><strong>{{ $order->no_invoice }}</strong></td>
                        <td>{{ $order->user?->name ?? 'Guest' }}</td>
                        <td>{{ $order->dibuat_pada ? $order->dibuat_pada->format('d M Y H:i') : '-' }}</td>
                        <td>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                        <td>
                            @if(strtolower($order->status_pesanan) == 'diproses')
                                <span class="rf-badge badge-diproses">Diproses</span>
                            @elseif(strtolower($order->status_pesanan) == 'dikirim')
                                <span class="rf-badge badge-dikirim">Dikirim</span>
                            @elseif(strtolower($order->status_pesanan) == 'selesai')
                                <span class="rf-badge badge-selesai">Selesai</span>
                            @else
                                <span class="rf-badge badge-pending">{{ ucfirst($order->status_pesanan) }}</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div class="rf-progress-track" style="flex-grow: 1;">
                                    <div class="rf-progress-bar {{ $step['class'] }}" style="width: {{ $step['val'] }}%;"></div>
                                </div>
                                <span style="font-size: 11px; color: #6c757d;">{{ $step['label'] }}</span>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <!-- Icon Eye (Mata Aksi) -->
                            <button type="button" class="rf-btn-action" onclick="openModal('{{ $order->id_order }}')">
                                <svg class="rf-icon-svg" style="width: 14px; height: 14px;" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>

                            <!-- Modal Vanilla JavaScript -->
                            <div class="rf-modal-backdrop" id="modal-{{ $order->id_order }}">
                                <div class="rf-modal-content">
                                    <h4 style="margin-top:0;">Update Status Pesanan #{{ $order->no_invoice }}</h4>
                                    <form action="{{ route('admin.progress.updateStatus', $order->id_order) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div style="margin-bottom: 12px; text-align: left;">
                                            <label style="display:block; font-size:12px; margin-bottom:4px;">Status Pesanan Baru</label>
                                            <select name="status_pesanan" class="rf-select" style="width: 100%;" required>
                                                <option value="pending" {{ $order->status_pesanan == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="diproses" {{ $order->status_pesanan == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                                <option value="dikirim" {{ $order->status_pesanan == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                                                <option value="selesai" {{ $order->status_pesanan == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                <option value="batal" {{ $order->status_pesanan == 'batal' ? 'selected' : '' }}>Batal</option>
                                            </select>
                                        </div>
                                        <div style="margin-bottom: 16px; text-align: left;">
                                            <label style="display:block; font-size:12px; margin-bottom:4px;">Catatan (Opsional)</label>
                                            <textarea name="catatan" class="rf-input" style="width: 100%; height: 60px;"></textarea>
                                        </div>
                                        <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                            <button type="button" class="rf-btn rf-btn-outline" onclick="closeModal('{{ $order->id_order }}')">Batal</button>
                                            <button type="submit" class="rf-btn rf-btn-maroon">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #6c757d; padding: 24px;">Tidak ada data pesanan ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; font-size: 13px; color: #6c757d;">
            <span>Menampilkan {{ $orders->count() }} dari {{ $orders->total() }} pesanan</span>
            <div>
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function openModal(id) {
        document.getElementById('modal-' + id).classList.add('active');
    }
    function closeModal(id) {
        document.getElementById('modal-' + id).classList.remove('active');
    }

    document.addEventListener("DOMContentLoaded", function() {
        const chartEl = document.getElementById('statusChart');
        if (chartEl) {
            const ctx = chartEl.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Diproses', 'Dikirim', 'Selesai'],
                    datasets: [{
                        data: [
                            {{ $summary['charts']['diproses']['count'] ?? 0 }},
                            {{ $summary['charts']['dikirim']['count'] ?? 0 }},
                            {{ $summary['charts']['selesai']['count'] ?? 0 }}
                        ],
                        backgroundColor: ['#ffc107', '#198754', '#70132A'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '75%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    });
</script>
@endsection