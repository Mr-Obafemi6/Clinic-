@extends('layouts.app')
@section('title', 'Urgences & contact')
@section('subtitle', 'Urgences & Contact')
@section('content')
<div class="split">
<div class="col">
<section class="vital">
    <div class="row gap top"><span class="vital-ico"><span class="ms">emergency</span></span>
        <div><h2>Urgence Vitale Immédiate</h2><p>Difficulté respiratoire sévère, douleur thoracique intense ou perte de connaissance. Appelez les urgences sans attendre.</p></div></div>
    <div class="grid2"><a class="btn btn-white" href="tel:{{ config('clinique.phone_tel') }}"><span class="ms">call</span>Urgences clinique</a><a class="btn btn-pink" href="tel:{{ config('clinique.pompiers') }}"><span class="ms">call</span>Pompiers {{ config('clinique.pompiers') }}</a></div>
    <p class="vital-note">Police : {{ config('clinique.police') }}@if (config('clinique.samu')) • SAMU : <a href="tel:{{ config('clinique.samu') }}">{{ config('clinique.samu') }}</a>@endif</p>
</section>

<article class="card">
    <div class="row between"><div><small class="ok caps">Service ouvert 24h/7j</small><h2>Urgences {{ config('clinique.name') }}</h2></div><span class="tag tag-green"><i class="dot"></i>12 min d'attente</span></div>
    <a class="map" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode(config('clinique.address')) }}"><span class="ms">navigation</span> Entrée B • Parking Urgences P1 (Accès direct)</a>
    <p class="muted"><span class="ms">location_on</span> {{ config('clinique.address') }} (Rez-de-chaussée Aile Ouest)</p>
    <div class="grid2 actions">
        <a class="btn btn-primary" href="tel:{{ preg_replace('/\s/', '', config('clinique.phone')) }}"><span class="ms">call</span>{{ config('clinique.phone') }}</a>
        <a class="btn btn-ghost" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode(config('clinique.address')) }}"><span class="ms">directions_car</span>Itinéraire GPS</a>
    </div>
</article>

<article class="card callback">
    <div class="row gap top"><span class="svc-ico mint"><span class="ms">sync_alt</span></span><div><h3>Rappel soignant express</h3><p class="muted">Une infirmière de régulation vous recontacte en moins de 10 min pour qualifier vos symptômes.</p></div></div>
    <form method="POST" action="{{ route('emergency.callback') }}" class="form">
        @csrf
        <label>Votre numéro de mobile
            <span class="input-ico"><span class="ms">call</span><input type="tel" name="phone" value="{{ old('phone') }}" placeholder="90 12 34 56" required autocomplete="tel"></span>
            @error('phone')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <button class="btn btn-teal block" type="submit"><span class="ms">support_agent</span>Être rappelé par un soignant</button>
    </form>
</article>
</div>
<div class="col">
<div class="section-head"><h2>Permanence des Soins</h2><small class="muted">Sans interruption</small></div>
<article class="card row between"><div class="row gap"><span class="svc-ico mint"><span class="ms">video_chat</span></span><div><b>Médecin de garde <i class="dot"></i></b><small class="muted">Téléconsultation en moins de 15 min</small></div></div><a class="btn btn-teal" href="{{ route('tele') }}">Démarrer</a></article>
<div class="grid2">
    <article class="card"><span class="svc-ico blue"><span class="ms">support_agent</span></span><small class="muted">Lun - Dim • 7h - 22h</small><b>Standard</b><a class="link" href="tel:{{ config('clinique.phone_tel') }}">{{ config('clinique.phone') }}</a></article>
    <article class="card"><span class="svc-ico mint"><span class="ms">medical_services</span></span><small class="ok">Triage immédiat</small><b>Conseil Infirmier</b><small class="muted">Chat &amp; Orientation</small></article>
</div>

<div class="section-head"><div><h2>Que faire selon vos symptômes ?</h2><small class="muted">Sélectionnez une situation pour un conseil immédiat</small></div></div>
<div class="chips scroll" data-tips>
    @foreach ($tips as $i => [$label, $icon])
        <button type="button" class="chip {{ $i === 0 ? 'active' : '' }}" data-target="tip-{{ $i }}"><span class="ms">{{ $icon }}</span>{{ $label }}</button>
    @endforeach
</div>
@foreach ($tips as $i => [$label, $icon, $title, $text])
    <article class="card tip" id="tip-{{ $i }}" {{ $i === 0 ? '' : 'hidden' }}>
        <h3><span class="ms">info</span> {{ $title }}</h3><p>{{ $text }}</p>
        <div class="row between"><small class="ok">Radiologie sur place disponible</small><a class="link" href="{{ route('appointments.index') }}">En savoir plus</a></div>
    </article>
@endforeach

</div>
</div>
<p class="legal">{{ config('clinique.name') }} • Agréée par le Ministère de la Santé du Togo<br>Vos données sont protégées par le secret médical et la législation togolaise sur les données personnelles.</p>
@endsection
