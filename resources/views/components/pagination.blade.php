@props([
    'paginator',
    'showInfo' => true,
    'showPerPage' => true,
    'perPageOptions' => [10, 15, 25, 50],
    'pageName' => 'page'
])

@php
    $currentPerPage = request()->get('per_page', $paginator->perPage());
@endphp

@if ($paginator->hasPages() || $showPerPage)
<div class="pagination-wrapper" data-pagination data-page-name="{{ $pageName }}">
    <div class="pagination-controls">
        @if($showPerPage)
        <div class="pagination-per-page">
            <label for="perPage-{{ $pageName }}">Mostrar:</label>
            <select id="perPage-{{ $pageName }}" class="pagination-select" data-per-page-select>
                @foreach($perPageOptions as $option)
                    <option value="{{ $option }}" {{ $currentPerPage == $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
            <span>por página</span>
        </div>
        @endif

        @if($showInfo && $paginator->total() > 0)
        <div class="pagination-info">
            Mostrando {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
        </div>
        @endif
    </div>

    @if ($paginator->hasPages())
    <nav class="pagination" aria-label="Paginacion">
        {{-- First Page --}}
        @if (!$paginator->onFirstPage())
            <a href="{{ $paginator->url(1) }}" class="pagination-btn" data-page="1" title="Primera página">
                <i class="fas fa-angle-double-left"></i>
            </a>
        @else
            <span class="pagination-btn disabled" aria-disabled="true">
                <i class="fas fa-angle-double-left"></i>
            </span>
        @endif

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn disabled" aria-disabled="true">
                <i class="fas fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" rel="prev" data-page="{{ $paginator->currentPage() - 1 }}">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        {{-- Page Numbers --}}
        <div class="pagination-pages">
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="pagination-page active" aria-current="page" data-page="{{ $page }}">{{ $page }}</span>
                @elseif ($page == 1 || $page == $paginator->lastPage() || abs($page - $paginator->currentPage()) <= 2)
                    <a href="{{ $url }}" class="pagination-page" data-page="{{ $page }}">{{ $page }}</a>
                @elseif (abs($page - $paginator->currentPage()) == 3)
                    <span class="pagination-ellipsis">...</span>
                @endif
            @endforeach
        </div>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" rel="next" data-page="{{ $paginator->currentPage() + 1 }}">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <span class="pagination-btn disabled" aria-disabled="true">
                <i class="fas fa-chevron-right"></i>
            </span>
        @endif

        {{-- Last Page --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="pagination-btn" data-page="{{ $paginator->lastPage() }}" title="Última página">
                <i class="fas fa-angle-double-right"></i>
            </a>
        @else
            <span class="pagination-btn disabled" aria-disabled="true">
                <i class="fas fa-angle-double-right"></i>
            </span>
        @endif
    </nav>
    @endif
</div>
@endif
