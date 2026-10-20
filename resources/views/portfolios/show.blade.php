@extends('layouts.app')

@section('title', $portfolio->title . ' | Folio Maker')

@section('content')
<div class="detail-wrap">
    <div class="detail-topline"><a class="back-link" href="{{ route('portfolios.index') }}"><span aria-hidden="true">←</span> My portfolios</a><div class="detail-actions"><a class="button button-outline button-small" href="{{ route('portfolios.edit', $portfolio) }}">Edit portfolio <span aria-hidden="true">↗</span></a><form method="POST" action="{{ route('portfolios.destroy', $portfolio) }}" data-confirm-delete data-confirm-message="Delete ‘{{ $portfolio->title }}’? This can’t be undone.">@csrf @method('DELETE')<button class="button button-danger-quiet button-small" type="submit">Delete</button></form></div></div>

    <section class="detail-hero">
        <div class="detail-cover @unless($portfolio->cover_image) detail-cover-empty @endunless" @if($portfolio->cover_image) style="background-image:url('{{ route('portfolios.media', [$portfolio, ...explode('/', $portfolio->cover_image)]) }}')" @endif>
            @unless($portfolio->cover_image)<span>{{ Str::upper(Str::substr($portfolio->title, 0, 1)) }}</span>@endunless
            <div class="cover-label">{{ $portfolio->category ?: 'PORTFOLIO' }} <i>·</i> {{ $portfolio->items->count() }} {{ Str::plural('PROJECT', $portfolio->items->count()) }}</div>
        </div>
        <div class="detail-heading"><p class="eyebrow">CURATED BY {{ Str::upper(auth()->user()->name) }}</p><h1>{{ $portfolio->title }}</h1><p class="detail-description">{{ $portfolio->description }}</p>
            @if($portfolio->tags)<div class="tag-list">@foreach($portfolio->tags as $tag)<span>{{ $tag }}</span>@endforeach</div>@endif
            <p class="detail-date">Last updated {{ $portfolio->updated_at->format('F j, Y') }}</p>
        </div>
    </section>

    <section class="detail-projects">
        <div class="detail-section-heading"><div><p class="eyebrow">THE COLLECTION</p><h2>Selected <em>projects.</em></h2></div><span>{{ str_pad($portfolio->items->count(), 2, '0', STR_PAD_LEFT) }} PIECES</span></div>
        @if($portfolio->items->isEmpty())
            <div class="detail-empty"><p>No projects have been added yet.</p><a class="text-link" href="{{ route('portfolios.edit', $portfolio) }}">Add your first project ↗</a></div>
        @else
            <div class="detail-project-list">
                @foreach($portfolio->items as $index => $item)
                    <article class="detail-project">
                        <div class="project-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} <span>/ {{ str_pad($portfolio->items->count(), 2, '0', STR_PAD_LEFT) }}</span></div>
                        <div class="project-content"><div class="project-detail-head"><h3>{{ $item->title }}</h3>@if($item->link)<a class="external-link" href="{{ $item->link }}" target="_blank" rel="noopener noreferrer" aria-label="Open {{ $item->title }} project link">↗</a>@endif</div>
                            @if($item->description)<p>{{ $item->description }}</p>@endif
                            @if($item->images)<div class="project-image-grid">@foreach($item->images as $image)<img src="{{ route('portfolios.media', [$portfolio, ...explode('/', $image)]) }}" alt="{{ $item->title }} image" loading="lazy">@endforeach</div>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection