<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0b5f94">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Saint-Luc">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">
    <title>@yield('title', 'Clinique Saint-Luc')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="app">
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">
            <span class="logo"><span class="ms">add</span></span>
            <span class="brand-text">
                <span class="brand-line"><strong>{{ config('clinique.name') }}</strong><em class="pill pill-open"><i></i>Ouvert</em></span>
                <small>@yield('subtitle', 'Accueil')</small>
            </span>
        </a>
        @auth
            @if (auth()->user()->isCashier())
            <nav class="desknav" aria-label="Navigation caisse">
                <a href="{{ route('caisse.index') }}" class="{{ request()->routeIs('caisse.index') ? 'active' : '' }}">Caisse</a>
                <a href="{{ route('caisse.create') }}" class="{{ request()->routeIs('caisse.create', 'caisse.store') ? 'active' : '' }}">Encaisser</a>
                <a href="{{ route('caisse.journal') }}" class="{{ request()->routeIs('caisse.journal') ? 'active' : '' }}">Journal</a>
            </nav>
            @elseif (auth()->user()->isDoctor())
            <nav class="desknav" aria-label="Navigation médecin">
                <a href="{{ route('medecin.index') }}" class="{{ request()->routeIs('medecin.*') ? 'active' : '' }}">Agenda</a>
                <a href="{{ route('medecin.index') }}#rappels">Rappels</a>
            </nav>
            @elseif (auth()->user()->isManager())
            <nav class="desknav" aria-label="Navigation responsable">
                <a href="{{ route('responsable.index') }}" class="{{ request()->routeIs('responsable.index') ? 'active' : '' }}">Supervision</a>
                <a href="{{ route('caisse.journal') }}" class="{{ request()->routeIs('caisse.*') ? 'active' : '' }}">Journal</a>
                <a href="{{ route('responsable.team') }}" class="{{ request()->routeIs('responsable.team*') ? 'active' : '' }}">Équipe</a>
            </nav>
            @else
            <nav class="desknav" aria-label="Navigation du site">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Accueil</a>
                <a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">Rendez-vous</a>
                <a href="{{ route('tele') }}" class="{{ request()->routeIs('tele') ? 'active' : '' }}">Téléconsultation</a>
                <a href="{{ route('record') }}" class="{{ request()->routeIs('record*') ? 'active' : '' }}">Mon Dossier</a>
                <a href="{{ route('emergency') }}" class="urgent {{ request()->routeIs('emergency') ? 'active' : '' }}">Urgences</a>
            </nav>
            @endif
            <div class="top-actions">
                <button type="button" class="btn-install" data-install hidden><span class="ms">install_mobile</span><span>Installer</span></button>
                @if (auth()->user()->isStaff())
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="icon-btn plain" aria-label="Se déconnecter"><span class="ms">logout</span></button></form>
                @else
                    <button type="button" class="icon-btn plain" aria-label="Notifications"><span class="ms">notifications</span></button>
                @endif
                <a href="{{ auth()->user()->staffHomeUrl() ?? route('record') }}" class="avatar" aria-label="Mon espace">{{ auth()->user()->initials }}<i></i></a>
            </div>
        @endauth
    </header>

    <main class="content {{ $__env->hasSection('nonav') ? 'no-nav' : '' }} {{ $__env->hasSection('wide') ? 'wide' : '' }}">
        @if (session('status'))
            <div class="flash" role="status"><span class="ms">check_circle</span> {{ session('status') }}</div>
        @endif
        @if ($errors->any() && ! $__env->hasSection('nonav'))
            <div class="flash flash-error" role="alert"><span class="ms">error</span> {{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>

    @unless ($__env->hasSection('nonav'))
        @if (auth()->user()?->isCashier())
            <nav class="tabbar three" aria-label="Navigation caisse">
                <a href="{{ route('caisse.index') }}" class="{{ request()->routeIs('caisse.index') ? 'active' : '' }}"><span class="ms">point_of_sale</span>Caisse</a>
                <a href="{{ route('caisse.create') }}" class="{{ request()->routeIs('caisse.create', 'caisse.store') ? 'active' : '' }}"><span class="ms">add_card</span>Encaisser</a>
                <a href="{{ route('caisse.journal') }}" class="{{ request()->routeIs('caisse.journal') ? 'active' : '' }}"><span class="ms">receipt_long</span>Journal</a>
            </nav>
        @elseif (auth()->user()?->isDoctor())
            <nav class="tabbar two" aria-label="Navigation médecin">
                <a href="{{ route('medecin.index') }}" class="{{ request()->routeIs('medecin.*') ? 'active' : '' }}"><span class="ms">calendar_month</span>Agenda</a>
                <a href="{{ route('medecin.index') }}#rappels"><span class="ms">call</span>Rappels</a>
            </nav>
        @elseif (auth()->user()?->isManager())
            <nav class="tabbar three" aria-label="Navigation responsable">
                <a href="{{ route('responsable.index') }}" class="{{ request()->routeIs('responsable.index') ? 'active' : '' }}"><span class="ms">monitoring</span>Supervision</a>
                <a href="{{ route('caisse.journal') }}" class="{{ request()->routeIs('caisse.*') ? 'active' : '' }}"><span class="ms">receipt_long</span>Journal</a>
                <a href="{{ route('responsable.team') }}" class="{{ request()->routeIs('responsable.team*') ? 'active' : '' }}"><span class="ms">groups</span>Équipe</a>
            </nav>
        @else
        <nav class="tabbar" aria-label="Navigation principale">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><span class="ms">home</span>Accueil</a>
            <a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}"><span class="ms">calendar_month</span>Rendez-vous</a>
            <a href="{{ route('emergency') }}" class="urgent {{ request()->routeIs('emergency') ? 'active' : '' }}"><span class="ms">emergency</span>Urgences</a>
            <a href="{{ route('record') }}" class="{{ request()->routeIs('record*') ? 'active' : '' }}"><span class="ms">folder_shared</span>Mon Dossier</a>
        </nav>
        @endif
    @endunless
</div>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
