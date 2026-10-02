@extends('layouts.app')
@section('title', 'Connexion — Clinique Saint-Luc')
@section('subtitle', 'Espace patient')
@section('nonav', '1')
@section('content')
<section class="auth">
    <h1>Connexion</h1>
    <p class="muted">Accédez à vos rendez-vous, résultats et ordonnances.</p>
    <form method="POST" action="{{ route('login') }}" class="card form">
        @csrf
        <label>Adresse e-mail
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </label>
        <label>Mot de passe
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        @error('email')<p class="field-error">{{ $message }}</p>@enderror
        <label class="check"><input type="checkbox" name="remember"> Rester connecté</label>
        <button class="btn btn-primary" type="submit">Se connecter</button>
    </form>
    <p class="center muted">Pas encore de compte ? <a href="{{ route('register') }}" class="link">Créer mon espace patient</a></p>
</section>
@endsection
