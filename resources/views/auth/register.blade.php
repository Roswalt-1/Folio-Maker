@extends('layouts.app')

@section('title', 'Sign up | Folio Maker')

@section('content')
<div class="auth-card-page">
    <section class="auth-card auth-card-register" aria-labelledby="register-title">
        <a class="auth-card-back" href="{{ route('home') }}"><span aria-hidden="true">←</span> Back to home</a>
        <div class="auth-card-heading">
            <div class="auth-card-brand"><span class="auth-card-mark" aria-hidden="true">f.</span><span>Folio Maker</span></div>
            <h1 id="register-title">SIGN UP</h1>
        </div>
        <form class="auth-card-form" method="POST" action="{{ route('register.store') }}">
            @csrf
            <label class="auth-card-field-label" for="name">Your name</label>
            <input class="auth-card-input" id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
            @error('name')<p class="auth-card-error" role="alert">{{ $message }}</p>@enderror

            <label class="auth-card-field-label" for="email">Email address</label>
            <input class="auth-card-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email')<p class="auth-card-error" role="alert">{{ $message }}</p>@enderror

            <label class="auth-card-field-label" for="password">Password <span class="auth-card-hint">8+ characters, mixed case and a number</span></label>
            <div class="auth-card-password-wrap">
                <input class="auth-card-input" id="password" name="password" type="password" autocomplete="new-password" required>
                <button type="button" class="auth-card-password-toggle" aria-label="Show password" data-password-toggle>Show</button>
            </div>
            @error('password')<p class="auth-card-error" role="alert">{{ $message }}</p>@enderror

            <label class="auth-card-field-label" for="password_confirmation">Confirm password</label>
            <input class="auth-card-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            @error('password_confirmation')<p class="auth-card-error" role="alert">{{ $message }}</p>@enderror

            <button class="auth-card-submit" type="submit">Create account <span aria-hidden="true">↗</span></button>
        </form>
        <p class="auth-card-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
    </section>
</div>
@endsection
