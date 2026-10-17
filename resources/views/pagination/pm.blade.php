@if ($paginator->hasPages())
    <div class="pagination">
        <p>Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} {{ $entity ?? 'Results' }}</p>
        <ul>
            <li class="prev_next_btn {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span><i class="bi bi-chevron-left"></i></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="disabled"><span>{{ $element }}</span></li>
                @else
                    @foreach ($element as $page => $url)
                        <li class="{{ $page === $paginator->currentPage() ? 'active' : '' }}">
                            <a href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li class="prev_next_btn {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span><i class="bi bi-chevron-right"></i></span>
                @endif
            </li>
        </ul>
    </div>
@elseif ($paginator->total())
    <div class="pagination">
        <p>Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} {{ $entity ?? 'Results' }}</p>
    </div>
@endif
