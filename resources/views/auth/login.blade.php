@extends('layouts.app')

@section('title', 'Login | Folio Maker')

@section('content')
<div class="auth-card-page">
    <section class="auth-card" aria-labelledby="login-title">
        <a class="auth-card-back" href="{{ route('home') }}"><span aria-hidden="true">←</span> Back to home</a>
        <div class="auth-card-heading">
            <div class="auth-card-brand"><span class="auth-card-mark" aria-hidden="true">f.</span><span>Folio Maker</span></div>
            <h1 id="login-title">LOGIN</h1>
        </div>
        <form class="auth-card-form" method="POST" action="{{ route('login.store') }}">
            @csrf
            <label class="auth-card-field-label" for="email">Email address</label>
            <input class="auth-card-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email')<p class="auth-card-error" role="alert">{{ $message }}</p>@enderror

            <div class="auth-card-password-label">
                <label class="auth-card-field-label" for="password">Password</label>
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <div class="auth-card-password-wrap">
                <input class="auth-card-input" id="password" name="password" type="password" autocomplete="current-password" required>
                <button type="button" class="auth-card-password-toggle" aria-label="Show password" data-password-toggle>Show</button>
            </div>
            @error('password')<p class="auth-card-error" role="alert">{{ $message }}</p>@enderror
            <label class="auth-card-remember"><input type="checkbox" name="remember" value="1"><span>Keep me logged in</span></label>
            <button class="auth-card-submit" type="submit">Log in <span aria-hidden="true">↗</span></button>
        </form>
        <p class="auth-card-switch">New to Folio Maker? <a href="{{ route('register') }}">Create an account</a></p>
    </section>
</div>
@endsection