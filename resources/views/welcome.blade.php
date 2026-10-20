@extends('layouts.app')

@section('title', 'Welcome | Folio Maker')

@section('content')
<section class="dashboard-wrap">
    <div class="dashboard-intro">
        <div>
            <p class="eyebrow"><span class="eyebrow-dot"></span> YOUR CREATIVE SPACE</p>
            <h1>Welcome to <em>Folio Maker.</em></h1>
            <p class="muted-copy">A home for what you make.</p>
        </div>
        <a class="button button-dark" href="{{ route('home') }}">Continue <span aria-hidden="true">↗</span></a>
    </div>
</section>
@endsection
