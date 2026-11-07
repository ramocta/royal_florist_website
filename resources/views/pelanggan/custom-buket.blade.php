@extends('layouts.pelanggan')

@section('title', 'Custom Buket - Royal Florist')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/custom-buket.css') }}">
@endpush

@section('content')
<div class="cb-page">
    @if (session('success'))
        <div class="cb-alert">{{ session('success') }}</div>
    @endif

    <header class="cb-header">
        <h1>Custom Buket</h1>
        <p>Rakit buket impian Anda sendiri, pilih jenis bunga, warna wrapping, hingga kartu ucapan</p>
    </header>

    <div class="cb-layout">
        {{-- ============ KIRI: LIVE PREVIEW ============ --}}
        <aside class="cb-preview-card">
            <div class="cb-preview-badge">💐 LIVE PREVIEW BUKET</div>

            <div class="cb-preview-stage">
                <svg id="bouquetSvg" viewBox="0 0 260 300" xmlns="http://www.w3.org/2000/svg" aria-label="Preview buket"></svg>
            </div>

            <div class="cb-summary">
                <h4>Ringkasan Komponen:</h4>
                <ul>
                    <li>Bunga: <span id="sumFlowers">-</span></li>
                    <li>Wrapping: <span id="sumWrap">-</span></li>
                    <li>Pita: <span id="sumRibbon">-</span></li>
                    <li>Kartu Ucapan: <span id="sumCard">-</span></li>
                </ul>
            </div>
        </aside>

        {{-- ============ KANAN: FORM ============ --}}
        <section class="cb-form">
            <form id="customForm" action="{{ route('custom-buket.store') }}" method="POST">
                @csrf

                {{-- Pilih komposisi bunga (dari database) --}}
                <div class="cb-box">
                    <h3 class="cb-title">Pilih Komposisi Bunga</h3>

                    @if ($flowers->isEmpty())
                        <p class="cb-label">Maaf, stok bunga sedang kosong. Silakan coba lagi nanti.</p>
                    @else
                        <div class="cb-flowers">
                            @foreach ($flowers as $f)
                                @php
                                    $qty = min($defaults[$f->kode] ?? 0, $f->stok);
                                @endphp
                                <div class="cb-flower" data-flower="{{ $f->kode }}"
                                     data-name="{{ $f->nama }}" data-price="{{ (int) $f->harga }}"
                                     data-max="{{ $f->stok }}">
                                    <div class="cb-flower-icon cb-icon-{{ $f->kode }}"></div>
                                    <div class="cb-flower-name">{{ $f->nama }}</div>
                                    <div class="cb-flower-price">Rp {{ number_format($f->harga, 0, ',', '.') }} / {{ $f->satuan }}</div>
                                    <div class="cb-qty">
                                        <button type="button" class="qty-minus" aria-label="Kurangi">-</button>
                                        <span class="qty-value">{{ $qty }}</span>
                                        <button type="button" class="qty-plus" aria-label="Tambah">+</button>
                                    </div>
                                    <input type="hidden" name="flowers[{{ $f->id_bunga }}]" value="{{ $qty }}">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Tema warna wrapping & pita --}}
                <div class="cb-box">
                    <h3 class="cb-title">Tema Warna Wrapping &amp; Pita</h3>
                    <div class="cb-theme">
                        <div>
                            <p class="cb-label">Warna Kertas Pembungkus:</p>
                            <div class="cb-swatches">
                                @foreach ($wraps as $key => $w)
                                    <button type="button" class="cb-swatch {{ $loop->first ? 'active' : '' }}"
                                            data-wrap="{{ $key }}" data-name="{{ $w['name'] }}"
                                            data-outer="{{ $w['outer'] }}" data-inner="{{ $w['inner'] }}"
                                            style="background: {{ $w['outer'] }}" title="{{ $w['name'] }}"></button>
                                @endforeach
                            </div>
                            <input type="hidden" name="wrap" id="wrapInput" value="{{ array_key_first($wraps) }}">
                        </div>

                        <div>
                            <p class="cb-label">Aksesoris Pita:</p>
                            <div class="cb-ribbons">
                                @foreach ($ribbons as $key => $r)
                                    <button type="button" class="cb-ribbon {{ $loop->first ? 'active' : '' }}"
                                            data-ribbon="{{ $key }}" data-name="{{ $r['name'] }}"
                                            data-price="{{ $r['price'] }}" data-color="{{ $r['color'] }}">
                                        {{ $r['name'] }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="ribbon" id="ribbonInput" value="{{ array_key_first($ribbons) }}">
                        </div>
                    </div>
                </div>

                {{-- Kartu ucapan --}}
                <div class="cb-box">
                    <h3 class="cb-title">Tulis Kartu Ucapan (Gratis)</h3>
                    <div class="cb-card-input">
                        <textarea name="card_message" id="cardMessage" rows="2" maxlength="200"
                                  placeholder="Tulis ucapan Anda di sini...">Happy Graduation, Siska! Semoga sukses selalu di babak baru kehidupanmu.🎉</textarea>
                        <input type="text" name="card_from" id="cardFrom" maxlength="60"
                               placeholder="- Dari ..." value="- Dari Budi & Teman-teman">
                    </div>
                </div>

                {{-- Total --}}
                <div class="cb-total">
                    <div>
                        <span class="cb-total-label">Total Estimasi Harga Custom:</span>
                        <div class="cb-total-row">
                            <strong id="totalPrice">Rp 0</strong>
                            <small>*Sudah termasuk jasa rakit &amp; pita</small>
                        </div>
                    </div>
                    <button type="submit" class="cb-btn-cart">🛒 Tambah ke Keranjang</button>
                </div>

                @if ($errors->any())
                    <p class="cb-error">{{ $errors->first() }}</p>
                @endif
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.CB_CONFIG = {
        assemblyFee: {{ $assemblyFee }},
    };
</script>
<script src="{{ asset('js/custom-buket.js') }}"></script>

@if (session('success'))
<script>
    // Sinkronkan dengan keranjang di navbar (localStorage)
    document.addEventListener('DOMContentLoaded', function () {
        RF.add('custom-{{ now()->timestamp }}', Infinity, document.getElementById('bouquetSvg'));
        RF.toast(@json(session('success')));
    });
</script>
@endif
@endpush