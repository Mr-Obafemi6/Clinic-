@extends('layouts.app')
@section('title', 'Inscription — Clinique Saint-Luc')
@section('subtitle', 'Espace patient')
@section('nonav', '1')
@section('content')
<section class="auth">
    <h1>Créer mon espace patient</h1>
    <p class="muted">Vos données de santé restent chiffrées et accessibles à vous seul.</p>
    <form method="POST" action="{{ route('register') }}" class="card form">
        @csrf
        <label>Nom complet
            <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
            @error('name')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <label>Adresse e-mail
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <label>Mot de passe (8 caractères minimum)
            <input type="password" name="password" required autocomplete="new-password">
            @error('password')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <label>Confirmer le mot de passe
            <input type="password" name="password_confirmation" required autocomplete="new-password">
        </label>
        <button class="btn btn-primary" type="submit">Créer mon compte</button>
    </form>
    <p class="center muted">Déjà inscrit ? <a href="{{ route('login') }}" class="link">Se connecter</a></p>
</section>
@endsection
