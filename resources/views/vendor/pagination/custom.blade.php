@if ($paginator->hasPages())
    <div class="pag-wrap">
        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="pag-btn pag-disabled">‹ Anterior</span>
        @else
            <a class="pag-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Anterior</a>
        @endif

        {{-- Números de página --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pag-btn pag-dots">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pag-btn pag-active">{{ $page }}</span>
                    @else
                        <a class="pag-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <a class="pag-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente ›</a>
        @else
            <span class="pag-btn pag-disabled">Siguiente ›</span>
        @endif
    </div>
@endif
