@extends('layouts.app')

@section('title', 'Reset password | Folio Maker')

@section('content')
<div class="auth-page auth-page-simple">
    <a class="wordmark auth-wordmark" href="{{ route('home') }}"><span class="wordmark-mark">f.</span>Folio Maker</a>
    <section class="auth-simple-panel">
        <a class="back-link" href="{{ route('login') }}"><span aria-hidden="true">←</span> Back to log in</a>
        <div class="auth-heading"><p class="eyebrow">IT HAPPENS</p><h1>Find your<br><em>way back.</em></h1><p>We’ll email you a link to choose a new password.</p></div>
        <form class="form-stack" method="POST" action="{{ route('password.email') }}">
            @csrf
            <label class="field-label" for="email">Email address</label>
            <input class="text-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
            <button class="button button-dark button-wide" type="submit">Send reset link <span aria-hidden="true">↗</span></button>
        </form>
    </section>
</div>
@endsection