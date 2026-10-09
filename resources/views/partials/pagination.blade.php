@if ($paginator->hasPages())
    <nav class="pager" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pager-link is-disabled">Previous</span>
        @else
            <a class="pager-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif

        <span class="pager-status">
            Page {{ $paginator->currentPage() }}
            @if (method_exists($paginator, 'lastPage')) of {{ $paginator->lastPage() }} @endif
        </span>

        @if ($paginator->hasMorePages())
            <a class="pager-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="pager-link is-disabled">Next</span>
        @endif
    </nav>
@endif
