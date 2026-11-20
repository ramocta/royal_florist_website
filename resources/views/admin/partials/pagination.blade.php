@if ($paginator->hasPages())
    @php
        $sekarang = $paginator->currentPage();
        $terakhir = $paginator->lastPage();
        $mulai    = max(1, $sekarang - 2);
        $akhir    = min($terakhir, $sekarang + 2);
    @endphp

    <nav class="paging" aria-label="Navigasi halaman">
        {{-- Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="nonaktif" aria-disabled="true">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">&lsaquo;</a>
        @endif

        {{-- Halaman pertama + titik-titik --}}
        @if ($mulai > 1)
            <a href="{{ $paginator->url(1) }}">1</a>
            @if ($mulai > 2)
                <span class="titik">&hellip;</span>
            @endif
        @endif

        {{-- Nomor di sekitar halaman aktif --}}
        @for ($i = $mulai; $i <= $akhir; $i++)
            @if ($i === $sekarang)
                <span class="aktif" aria-current="page">{{ $i }}</span>
            @else
                <a href="{{ $paginator->url($i) }}">{{ $i }}</a>
            @endif
        @endfor

        {{-- Titik-titik + halaman terakhir --}}
        @if ($akhir < $terakhir)
            @if ($akhir < $terakhir - 1)
                <span class="titik">&hellip;</span>
            @endif
            <a href="{{ $paginator->url($terakhir) }}">{{ $terakhir }}</a>
        @endif

        {{-- Berikutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">&rsaquo;</a>
        @else
            <span class="nonaktif" aria-disabled="true">&rsaquo;</span>
        @endif
    </nav>
@endif