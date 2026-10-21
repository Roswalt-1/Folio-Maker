<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($pageTitle = trim($__env->yieldContent('title', config('app.name'))))
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="@yield('description', 'Create and share personal portfolios with Folio Maker.')">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="@yield('description', 'Create and share personal portfolios with Folio Maker.')">
    <script>
        (() => {
            const savedTheme = localStorage.getItem('folio-generator-theme');
            const theme = ['light', 'dark'].includes(savedTheme)
                ? savedTheme
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Lora:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Raleway:wght@400;500;600;700&family=Roboto+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body @class(['logged-in-app' => auth()->check() && !request()->routeIs('home')])>
    @if (!request()->routeIs('home') && !str_starts_with(request()->path(), 'login') && !str_starts_with(request()->path(), 'signup') && !str_starts_with(request()->path(), 'forgot-password') && !str_starts_with(request()->path(), 'reset-password'))
        <header class="site-header">
            <a class="wordmark" href="{{ route('home') }}"><span class="wordmark-mark">f.</span>Folio Maker</a>
            <nav class="main-nav" aria-label="Main navigation">
                <a href="{{ route('portfolios.index') }}" @class(['active' => request()->routeIs('portfolios.index')])>
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-7 9 7"/><path d="M5 9v12h14V9M9 21v-7h6v7"/></svg>
                    <span>Home</span>
                </a>
                <button class="site-theme-toggle" type="button" data-site-theme-toggle aria-label="Switch theme" aria-pressed="false" title="Switch theme">☾</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button button-quiet button-small logout-button" type="submit">
                        <span>Log out</span>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"/><path d="m14 17 5-5-5-5M19 12H9"/></svg>
                    </button>
                </form>
            </nav>
        </header>
    @endif

    @if (session('success'))
        <div class="toast toast-success" role="status" data-toast>{{ session('success') }}<button type="button" aria-label="Dismiss" data-dismiss-toast>×</button></div>
    @endif

    <main class="page-content">
        @yield('content')
    </main>
</body>
</html>