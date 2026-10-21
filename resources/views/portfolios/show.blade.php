@extends('layouts.app')

@section('title', $portfolio->title . ' | Folio Maker')

@section('content')
@php
    $template = match (data_get($portfolio->appearance_data, 'style.template')) {
        'card' => 'Card-based',
        'sidebar' => 'Sidebar',
        default => 'Minimal',
    };
    $coverUrl = $portfolio->cover_image
        ? route('portfolios.media', [$portfolio, ...explode('/', $portfolio->cover_image)])
        : null;
@endphp
<div class="detail-page" data-portfolio-page>
    <div class="detail-wrap">
        <div class="detail-topline">
            <a class="back-link" href="{{ route('portfolios.index') }}"><span class="back-arrow" aria-hidden="true">←</span> Home</a>
            <div class="detail-actions" data-main-toolbar>
                <a class="button button-primary button-small action-button action-edit" href="{{ route('portfolios.edit', $portfolio) }}" title="Edit portfolio">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16 4 4 4L9 19l-5 1 1-5Z"/><path d="m14 6 4 4"/></svg>
                    <span>Edit portfolio</span>
                </a>
                <button class="button button-outline button-small action-button action-pdf" type="button" data-unavailable="PDF download" title="Download PDF">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M5 17v4h14v-4"/></svg>
                    <span>Download PDF</span>
                </button>
                <button class="button button-outline button-small action-button action-print" type="button" data-print-page title="Print portfolio">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 8V3h10v5"/><path d="M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M7 14h10v7H7z"/><path d="M17 11h.01"/></svg>
                    <span>Print</span>
                </button>
                <div class="action-menu-wrap" data-menu-wrap>
                    <button class="button button-outline button-small action-button action-more" type="button" data-menu-trigger aria-haspopup="menu" aria-expanded="false" aria-controls="portfolio-more-menu" title="More actions">
                        <span>More</span>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
                    </button>
                    <div class="action-menu" id="portfolio-more-menu" role="menu" aria-label="More portfolio actions" hidden>
                        <button class="action-menu-item mobile-menu-only" type="button" role="menuitem" data-unavailable="PDF download">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M5 17v4h14v-4"/></svg>
                            <span>Download PDF</span>
                        </button>
                        <button class="action-menu-item mobile-menu-only" type="button" role="menuitem" data-print-page>
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 8V3h10v5"/><path d="M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M7 14h10v7H7z"/><path d="M17 11h.01"/></svg>
                            <span>Print</span>
                        </button>
                        <button class="action-menu-item" type="button" role="menuitem" data-share-page>
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.1 0l3-3a5 5 0 0 0-7.1-7.1l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.1 0l-3 3A5 5 0 0 0 11 21.1l1.7-1.7"/></svg>
                            <span>Share</span>
                        </button>
                        <button class="action-menu-item" type="button" role="menuitem" data-unavailable="Backup download">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16v13H4z"/><path d="M2 4h20v3H2zM9 12h6m-3-3v6"/></svg>
                            <span>Download backup</span>
                        </button>
                        <button class="action-menu-item" type="button" role="menuitem" data-unavailable="Portfolio duplication">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg>
                            <span>Duplicate</span>
                        </button>
                        <div class="action-menu-divider" role="separator"></div>
                        <button class="action-menu-item action-menu-delete" type="button" role="menuitem" data-open-delete>
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7"/></svg>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="detail-hero">
            @if($coverUrl)
                <button class="detail-cover detail-cover-button" type="button" data-cover-open aria-label="View full cover image">
                    <span class="detail-cover-image" style="background-image:url('{{ $coverUrl }}')"></span>
                    <span class="cover-zoom-hint" aria-hidden="true">View cover</span>
                    <span class="cover-label">{{ $portfolio->category ?: 'PORTFOLIO' }} <i>·</i> {{ $portfolio->items->count() }} {{ Str::plural('PROJECT', $portfolio->items->count()) }}</span>
                </button>
            @else
                <div class="detail-cover detail-cover-empty">
                    <span>{{ Str::upper(Str::substr($portfolio->title, 0, 1)) }}</span>
                    <div class="cover-label">{{ $portfolio->category ?: 'PORTFOLIO' }} <i>·</i> {{ $portfolio->items->count() }} {{ Str::plural('PROJECT', $portfolio->items->count()) }}</div>
                </div>
            @endif
            <div class="detail-heading">
                <p class="eyebrow">CURATED BY {{ Str::upper(auth()->user()->name) }}</p>
                <h1>{{ $portfolio->title }}</h1>
                <p class="detail-description">{{ $portfolio->description }}</p>
                <div class="detail-stat-list" aria-label="Portfolio details">
                    <span class="detail-stat">{{ $portfolio->items->count() }} {{ Str::plural('project', $portfolio->items->count()) }}</span>
                    <span class="detail-stat">{{ $template }} template</span>
                    <span class="detail-stat">Updated {{ $portfolio->updated_at->format('F j, Y') }}</span>
                </div>
                @if($portfolio->tags)<div class="tag-list">@foreach($portfolio->tags as $tag)<span>{{ $tag }}</span>@endforeach</div>@endif
            </div>
        </section>

        <section class="detail-projects">
            <div class="detail-section-heading"><div><p class="eyebrow">THE COLLECTION</p><h2>Selected <em>projects.</em></h2></div><span>{{ str_pad($portfolio->items->count(), 2, '0', STR_PAD_LEFT) }} PIECES</span></div>
            @if($portfolio->items->isEmpty())
                <div class="detail-empty">
                    <span class="detail-empty-mark" aria-hidden="true">✳</span>
                    <h3>Your collection starts here.</h3>
                    <p>Add a project to make this portfolio your own.</p>
                    <a class="button button-primary button-small" href="{{ route('portfolios.edit', $portfolio) }}">Add your first project</a>
                </div>
            @else
                <div class="detail-project-list">
                    @foreach($portfolio->items as $item)
                        <article class="detail-project">
                            <div class="project-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} <span>/ {{ str_pad($portfolio->items->count(), 2, '0', STR_PAD_LEFT) }}</span></div>
                            <div class="project-content">
                                <div class="project-detail-head">
                                    @if($item->link)<h3><a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer">{{ $item->title }}</a></h3><a class="external-link" href="{{ $item->link }}" target="_blank" rel="noopener noreferrer" aria-label="Open {{ $item->title }} project link" title="Open project in a new tab">↗</a>
                                    @else<h3>{{ $item->title }}</h3>@endif
                                </div>
                                @if($item->description)<p>{{ $item->description }}</p>@endif
                                @if($item->images)<div class="project-image-grid">@foreach($item->images as $image)<img src="{{ route('portfolios.media', [$portfolio, ...explode('/', $image)]) }}" alt="{{ $item->title }} image" loading="lazy">@endforeach</div>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <div class="detail-sticky-toolbar" data-sticky-toolbar aria-label="Portfolio quick actions" hidden>
        <span class="sticky-title">{{ $portfolio->title }}</span>
        <a class="button button-primary button-small action-button action-edit" href="{{ route('portfolios.edit', $portfolio) }}" title="Edit portfolio">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m16 4 4 4L9 19l-5 1 1-5Z"/><path d="m14 6 4 4"/></svg>
            <span>Edit</span>
        </a>
        <button class="button button-outline button-small action-button action-pdf" type="button" data-unavailable="PDF download" aria-label="Download PDF" title="Download PDF">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M5 17v4h14v-4"/></svg>
            <span>Download PDF</span>
        </button>
    </div>

    @if($coverUrl)
        <div class="detail-lightbox" data-lightbox role="dialog" aria-modal="true" aria-label="Portfolio cover image" hidden>
            <button class="lightbox-close" type="button" data-lightbox-close aria-label="Close image">×</button>
            <img src="{{ $coverUrl }}" alt="{{ $portfolio->title }} cover image">
        </div>
    @endif

    <div class="detail-toast" data-page-toast role="status" aria-live="polite" aria-atomic="true" hidden></div>

    <div class="detail-modal-backdrop" data-delete-modal role="dialog" aria-modal="true" aria-labelledby="delete-modal-title" aria-describedby="delete-modal-description" tabindex="-1" hidden>
        <div class="detail-confirm-modal">
            <span class="modal-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7"/></svg>
            </span>
            <h2 id="delete-modal-title">Delete this portfolio?</h2>
            <p id="delete-modal-description">This permanently deletes the portfolio and cannot be undone.</p>
            <div class="modal-actions">
                <button class="button button-outline button-small" type="button" data-cancel-delete>Cancel</button>
                <form method="POST" action="{{ route('portfolios.destroy', $portfolio) }}">
                    @csrf
                    @method('DELETE')
                    <button class="button button-small button-confirm-delete" type="submit">Delete portfolio</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('js/portfolio-show.js') }}" defer></script>
@endsection
