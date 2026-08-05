@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes->class('flex flex-col gap-3 border-t border-base-300 pt-4 sm:flex-row sm:items-center sm:justify-between') }} data-pagination>
        @if ($paginator->total() > 0)
            <p class="text-sm text-base-content/60">
                {!! __('Showing') !!} {{ $paginator->firstItem() }} {!! __('to') !!} {{ $paginator->lastItem() }} {!! __('of') !!} {{ $paginator->total() }} {!! __('results') !!}
            </p>
        @else
            <div></div>
        @endif

        <div class="join">
            @if ($paginator->onFirstPage())
                <button type="button" class="btn btn-sm join-item btn-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    ‹
                </button>
            @else
                <button
                    type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    class="btn btn-sm join-item"
                    aria-label="{{ __('pagination.previous') }}"
                >
                    ‹
                </button>
            @endif

            @foreach (\Livewire\invade($paginator)->elements() as $element)
                @if (is_string($element))
                    <button type="button" class="btn btn-sm join-item btn-disabled" disabled>{{ $element }}</button>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button
                                type="button"
                                wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}"
                                class="btn btn-sm join-item btn-active"
                                aria-current="page"
                            >
                                {{ $page }}
                            </button>
                        @else
                            <button
                                type="button"
                                wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}"
                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                class="btn btn-sm join-item"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button
                    type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    class="btn btn-sm join-item"
                    aria-label="{{ __('pagination.next') }}"
                >
                    ›
                </button>
            @else
                <button type="button" class="btn btn-sm join-item btn-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    ›
                </button>
            @endif
        </div>
    </div>
@endif
