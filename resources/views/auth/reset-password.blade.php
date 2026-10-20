@extends('layouts.app')

@section('title', 'Choose a new password | Folio Maker')

@section('content')
<div class="auth-page auth-page-simple">
    <a class="wordmark auth-wordmark" href="{{ route('home') }}"><span class="wordmark-mark">f.</span>Folio Maker</a>
    <section class="auth-simple-panel">
        <a class="back-link" href="{{ route('login') }}"><span aria-hidden="true">←</span> Back to log in</a>
        <div class="auth-heading"><p class="eyebrow">A FRESH START</p><h1>Choose a<br><em>new password.</em></h1></div>
        <form class="form-stack" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label class="field-label" for="email">Email address</label>
            <input class="text-input" id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
            <label class="field-label" for="password">New password</label>
            <div class="password-field"><input class="text-input" id="password" name="password" type="password" autocomplete="new-password" required><button type="button" class="password-toggle" aria-label="Show password" data-password-toggle>Show</button></div>
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
            <label class="field-label" for="password_confirmation">Confirm new password</label>
            <input class="text-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            <button class="button button-dark button-wide" type="submit">Update password <span aria-hidden="true">↗</span></button>
        </form>
    </section>
</div>
@endsection