@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="opacity:0.4;cursor:default;">← Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Prev</a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="opacity:0.5;border:none;">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="active"><span>{{ $page }}</span></span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a>
        @else
            <span style="opacity:0.4;cursor:default;">Next →</span>
        @endif
    </nav>
@endif