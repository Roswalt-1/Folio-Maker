@extends('layouts.app')

@section('title', 'Home | Folio Maker')

@section('content')
<div class="dashboard-wrap">
    <section class="dashboard-intro">
        <div>
            <h1>My <em>portfolios.</em></h1>
        </div>
        <a class="button button-dark" href="{{ route('portfolios.create') }}"><span class="plus-icon">+</span> New portfolio</a>
    </section>

    @if ($portfolios->isNotEmpty() || $search !== '')
        <section class="portfolio-toolbar" aria-label="Portfolio filters">
            <form class="search-form" method="GET" action="{{ route('portfolios.index') }}">
                <span aria-hidden="true" class="search-icon">⌕</span>
                <input type="search" name="q" value="{{ $search }}" placeholder="Find a portfolio" aria-label="Search portfolios">
                @if ($sort !== 'updated')<input type="hidden" name="sort" value="{{ $sort }}">@endif
                <button type="submit" class="sr-only">Search</button>
            </form>
            <label class="sort-control">Sort by
                <select name="sort" aria-label="Sort portfolios" data-auto-submit>
                    <option value="updated" @selected($sort === 'updated')>Recently updated</option>
                    <option value="title" @selected($sort === 'title')>Name A–Z</option>
                    <option value="oldest" @selected($sort === 'oldest')>Oldest updated</option>
                </select>
            </label>
        </section>
    @endif

    @if ($portfolios->isEmpty())
        @if ($search !== '')
            <div class="empty-state empty-search"><span class="empty-mark">⌕</span><h2>No portfolios found</h2><p>Try another title or clear your search.</p><a class="text-link" href="{{ route('portfolios.index') }}">Clear search</a></div>
        @else
            <div class="empty-state"><span class="empty-mark">✳</span><p class="eyebrow">A BLANK CANVAS</p><h2>No portfolios yet.</h2><p>Your next favorite project belongs here. Start with a title and make it yours as you go.</p><a class="button button-dark" href="{{ route('portfolios.create') }}"><span class="plus-icon">+</span> Create your first portfolio</a></div>
        @endif
    @else
        <p class="result-count">{{ $portfolios->count() }} {{ Str::plural('portfolio', $portfolios->count()) }} <span>·</span> updated as you go</p>
        <section class="portfolio-grid" aria-label="Your portfolios">
            @foreach ($portfolios as $portfolio)
                @php($template = in_array(data_get($portfolio->appearance_data, 'style.template'), ['card', 'sidebar'], true) ? data_get($portfolio->appearance_data, 'style.template') : 'minimal')
                <article @class(['portfolio-card', 'portfolio-card-'.$template])>
                    <a class="portfolio-card-image" href="{{ route('portfolios.show', $portfolio) }}" @if($portfolio->cover_image) style="background-image:url('{{ route('portfolios.media', [$portfolio, ...explode('/', $portfolio->cover_image)]) }}')" @endif aria-label="Open {{ $portfolio->title }}">
                        @unless($portfolio->cover_image)<span class="cover-placeholder">{{ Str::upper(Str::substr($portfolio->title, 0, 1)) }}</span>@endunless
                        <span class="template-label">{{ $template === 'card' ? 'Card-based' : Str::ucfirst($template) }}</span>
                        <span class="card-open" aria-hidden="true">↗</span>
                    </a>
                    <div class="portfolio-card-body">
                        <div class="card-title-row"><h2><a href="{{ route('portfolios.show', $portfolio) }}">{{ $portfolio->title }}</a></h2><span class="card-item-count">{{ $portfolio->items_count }} {{ Str::plural('project', $portfolio->items_count) }}</span></div>
                        <p>{{ Str::limit($portfolio->description, 105) }}</p>
                        <div class="card-meta"><span>{{ $portfolio->category ?: 'Personal collection' }}</span><time datetime="{{ $portfolio->updated_at->toDateString() }}">Updated {{ $portfolio->updated_at->diffForHumans() }}</time></div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</div>
@endsection