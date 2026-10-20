@extends('layouts.app')

@section('title', 'Folio Maker')

@section('content')
<div class="landing-page">
    <header class="landing-header" id="landing-header">
        <a class="wordmark" href="{{ route('home') }}" aria-label="Folio Maker home"><span class="wordmark-mark">f.</span>Folio Maker</a>
        <button class="landing-menu-toggle" type="button" aria-expanded="false" aria-controls="landing-navigation" aria-label="Open navigation">
            <span></span><span></span><span></span>
        </button>
        <nav class="landing-navigation" id="landing-navigation" aria-label="Main navigation">
            <a class="landing-login" href="{{ route('login') }}">Log in</a>
            <a class="button landing-nav-cta" href="{{ route('register') }}">Sign up <span aria-hidden="true">↗</span></a>
        </nav>
    </header>

    <section class="landing-hero" aria-labelledby="landing-title">
        <div class="hero-copy">
            <h1 id="landing-title">Design your<br><em>ways.</em></h1>
            <p class="hero-description">Bring your projects together in a portfolio that feels like you. Thoughtful, easy to update, and always ready to share.</p>
            <div class="hero-actions">
                <a class="button hero-button-primary" href="{{ route('register') }}">Start your portfolio <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </section>
</div>
<script>
    (() => {
        const header = document.getElementById('landing-header');
        const toggle = document.querySelector('.landing-menu-toggle');
        const navigation = document.getElementById('landing-navigation');
        const updateHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 12);
        const closeMenu = () => {
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Open navigation');
            navigation.classList.remove('is-open');
        };

        updateHeader();
        window.addEventListener('scroll', updateHeader, { passive: true });
        toggle.addEventListener('click', () => {
            const isOpen = toggle.getAttribute('aria-expanded') !== 'true';
            toggle.setAttribute('aria-expanded', String(isOpen));
            toggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
            navigation.classList.toggle('is-open', isOpen);
        });
        navigation.addEventListener('click', event => {
            if (event.target.closest('a')) closeMenu();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeMenu();
        });
    })();
</script>
@endsection