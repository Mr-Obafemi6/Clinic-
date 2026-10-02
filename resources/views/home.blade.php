@extends('layouts.app')
@section('subtitle', 'Accueil')
@section('wide', '1')
@section('content')
<p class="eyebrow"><i class="dot"></i> Espace Patient Protégé</p>
<div class="row between">
    <div>
        <h1>Bonjour {{ auth()->user()->first_name }},</h1>
        <p class="muted">Comment vous sentez-vous aujourd'hui ?</p>
    </div>
    <a class="icon-btn big" href="{{ route('record') }}" aria-label="Mon dossier"><span class="ms fill">favorite</span></a>
</div>

<div class="chips scroll" data-chips>
    @foreach (['✨ En forme', '😴 Fatigue', '🩹 Gêne / Douleur', '🤒 Fièvre'] as $mood)
        <button type="button" class="chip">{{ $mood }}</button>
    @endforeach
</div>

<section class="urgent-card">
    <div class="row between">
        <div class="row gap"><span class="ms">emergency</span><div><strong>Urgences &amp; Soins 24/7</strong><small>Temps d'attente estimé : <b>12 min</b></small></div></div>
        <span class="tag tag-red">DIRECT</span>
    </div>
    <div class="grid2">
        <a href="tel:{{ config('clinique.phone_tel') }}" class="btn btn-red"><span class="ms">call</span>Appeler les urgences</a>
        <a href="{{ route('emergency') }}" class="btn btn-light"><span class="ms">timer</span>Triage direct</a>
    </div>
</section>

<div class="section-head"><h2>Prochain rendez-vous</h2><a class="link" href="{{ route('appointments.mine') }}">Tous mes RDV</a></div>
@if ($next)
    <article class="card next">
        <div class="row between">
            <div class="row gap">
                <img class="photo sm" src="{{ asset($next->doctor->photo) }}" alt="">
                <div><strong>{{ $next->doctor->name }}</strong><small>{{ $next->doctor->specialty->name }}</small></div>
            </div>
            <span class="tag tag-blue"><span class="ms">event</span>J-{{ (int) now()->startOfDay()->diffInDays($next->starts_at->copy()->startOfDay()) }}</span>
        </div>
        <div class="grid2 info">
            <div class="info-cell"><span class="ms">calendar_today</span><div><small>Date &amp; Heure</small><b>{{ $next->starts_at->isoFormat('ddd D MMM • HH[h]mm') }}</b></div></div>
            <div class="info-cell"><span class="ms">location_on</span><div><small>Localisation</small><b>{{ $next->visio ? 'Téléconsultation' : $next->doctor->location }}</b></div></div>
        </div>
        <div class="grid2 actions">
            @if ($next->visio)
                <a href="{{ route('tele', $next) }}" class="btn btn-primary"><span class="ms">videocam</span>Rejoindre la visio</a>
            @else
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(config('clinique.address')) }}" target="_blank" rel="noopener" class="btn btn-primary"><span class="ms">navigation</span>Itinéraire &amp; Accès</a>
            @endif
            <a href="{{ route('appointments.index', ['medecin' => $next->doctor_id]) }}" class="btn btn-ghost"><span class="ms">edit_calendar</span>Modifier</a>
        </div>
        @if ($next->isPaid())
            <span class="tag tag-green paid-tag"><span class="ms">check_circle</span>Consultation réglée</span>
        @else
            <a href="{{ route('payments.create', $next) }}" class="btn btn-teal block"><span class="ms">smartphone</span>Payer {{ $next->doctor->price_label }} (T-Money / Flooz)</a>
        @endif
    </article>
@else
    <article class="card center"><p class="muted">Aucun rendez-vous à venir.</p><a href="{{ route('appointments.index') }}" class="btn btn-primary">Prendre rendez-vous</a></article>
@endif

<div class="section-head"><h2>Services essentiels</h2></div>
<div class="grid2 services">
    <a class="card svc" href="{{ route('appointments.index') }}"><span class="svc-ico blue"><span class="ms">alarm_add</span></span><b>Prendre RDV</b><small>En cabinet ou à distance</small></a>
    <a class="card svc" href="{{ route('record', ['type' => 'analyse']) }}"><span class="svc-ico mint"><span class="ms">lab_profile</span></span><b>Résultats</b><small>Prise de sang, IRM</small></a>
    <a class="card svc" href="{{ route('tele') }}"><span class="svc-ico blue"><span class="ms">videocam</span></span><b>Téléconsultation</b><small>Visio sécurisée</small></a>
    <a class="card svc" href="{{ route('record', ['type' => 'ordonnance']) }}"><span class="svc-ico grey"><span class="ms">prescriptions</span></span><b>Ordonnances</b><small>Renouvellements actifs</small></a>
</div>

<div class="section-head"><div><h2>Pôles d'excellence</h2><small class="muted">Praticiens disponibles cette semaine</small></div><a class="link" href="{{ route('appointments.index') }}">Voir les {{ $specialties->count() }}</a></div>
<div class="hscroll">
    @foreach ($specialties as $s)
        <a class="card pole" href="{{ route('appointments.index', ['specialite' => $s->slug]) }}">
            <span class="svc-ico {{ ['blue', 'mint', 'grey'][$loop->index % 3] }}"><span class="ms">{{ $s->icon }}</span></span>
            <b>{{ $s->name }}</b><small class="ok"><i class="dot"></i>{{ $s->doctors_count }} {{ Str::plural('Médecin', $s->doctors_count) }}</small>
        </a>
    @endforeach
</div>

<div class="section-head"><h2>Actualités &amp; Conseils</h2><a class="link" href="#">Le Mag Santé</a></div>
<div class="articles">
@foreach ($articles as $a)
    <article class="card article">
        <div class="cover cover-{{ $loop->index % 3 }}"><span class="tag tag-mint">{{ $a->category }}</span><span class="read"><span class="ms">schedule</span>{{ $a->read_minutes }} min de lecture</span></div>
        <h3>{{ $a->title }}</h3>
        <p class="muted">{{ $a->excerpt }}</p>
        <div class="row between"><small class="muted">Mis à jour {{ $a->published_at->diffForHumans() }}</small><a href="#" class="link">Lire les consignes</a></div>
    </article>
@endforeach
</div>
@endsection
