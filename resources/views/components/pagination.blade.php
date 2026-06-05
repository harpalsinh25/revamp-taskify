@if ($paginator->hasPages())
    <nav class="pagination tk-pagination mt-4 d-flex justify-content-end" style="gap: 4px;">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button disabled style="border: 0; background: transparent; opacity: 0.5; cursor: not-allowed; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; color: var(--fg-1);"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m15 6-6 6 6 6"/></svg></button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" style="border: 0; background: transparent; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: var(--fg-1);"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m15 6-6 6 6 6"/></svg></a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <button disabled style="border: 0; background: transparent; opacity: 0.5; cursor: not-allowed; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; color: var(--fg-1);">{{ $element }}</button>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <button class="on" style="border: 0; background: var(--bg-3); color: var(--fg-0); font-weight: 600; border-radius: 4px; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" style="border: 0; background: transparent; text-decoration: none; color: var(--fg-1); border-radius: 4px; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="border: 0; background: transparent; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: var(--fg-1);"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m9 6 6 6-6 6"/></svg></a>
        @else
            <button disabled style="border: 0; background: transparent; opacity: 0.5; cursor: not-allowed; min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; color: var(--fg-1);"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m9 6 6 6-6 6"/></svg></button>
        @endif
    </nav>
@endif
