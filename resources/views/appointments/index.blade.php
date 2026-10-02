@extends('layouts.app')
@section('title', 'Prendre rendez-vous')
@section('subtitle', 'Prendre rendez-vous')
@section('content')
<div class="trust"><span class="ms">verified_user</span><span>Clinique agréée par le Ministère de la Santé</span><span class="tag tag-white">Assurances acceptées</span></div>

<form method="GET" action="{{ route('appointments.index') }}" class="search">
    <span class="ms">search</span>
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un médecin, une spécialité">
    @if (request('specialite'))<input type="hidden" name="specialite" value="{{ request('specialite') }}">@endif
    <button class="icon-btn plain" aria-label="Rechercher"><span class="ms">tune</span></button>
</form>

<div class="chips scroll">
    <a class="chip {{ request('specialite') ? '' : 'active' }}" href="{{ route('appointments.index') }}">Tous</a>
    @foreach ($specialties as $s)
        <a class="chip {{ request('specialite') === $s->slug ? 'active' : '' }}" href="{{ route('appointments.index', ['specialite' => $s->slug]) }}"><span class="ms">{{ $s->icon }}</span>{{ $s->name }}</a>
    @endforeach
</div>

@if (! $doctor)
    <article class="card center"><p class="muted">Aucun médecin ne correspond à votre recherche.</p><a class="btn btn-primary" href="{{ route('appointments.index') }}">Voir tous les médecins</a></article>
@else
<div class="split"><div class="col">
    @if ($doctors->count() > 1)
        <div class="chips scroll small">
            @foreach ($doctors as $d)
                <a class="chip {{ $d->id === $doctor->id ? 'active' : '' }}" href="{{ route('appointments.index', array_filter(['medecin' => $d->id, 'specialite' => request('specialite'), 'q' => request('q')])) }}">{{ $d->name }}</a>
            @endforeach
        </div>
    @endif

    <article class="card doctor">
        <div class="row gap top">
            <div class="photo-wrap"><img class="photo" src="{{ asset($doctor->photo) }}" alt="{{ $doctor->name }}"><i class="presence"></i></div>
            <div>
                <h2>{{ $doctor->name }} <span class="ms verified">verified</span></h2>
                <p class="muted">{{ $doctor->title }}</p>
                <div class="row gap-s"><span class="tag tag-grey"><span class="ms">star</span>{{ number_format($doctor->rating, 1) }} ({{ $doctor->reviews_count }} avis)</span><span class="tag tag-blue">{{ $doctor->sector }}</span></div>
            </div>
        </div>
        <div class="grid2 info">
            <div class="info-cell"><span class="ms">location_on</span><div><small>Cabinet</small><b>{{ $doctor->location }}</b></div></div>
            <div class="info-cell"><span class="ms">payments</span><div><small>Consultation</small><b>{{ $doctor->tarif }}</b></div></div>
        </div>
    </article>
<article class="card clinic-foot"><span class="svc-ico blue"><span class="ms">local_hospital</span></span><div><b>{{ config('clinique.name') }}</b><small class="muted">{{ config('clinique.address') }}</small></div><a href="tel:{{ preg_replace('/\s/', '', config('clinique.phone')) }}" class="icon-btn" aria-label="Appeler"><span class="ms">call</span></a></article>
</div>
<div class="col">
    <form method="POST" action="{{ route('appointments.store') }}" id="booking" data-slots-url="{{ route('appointments.slots', $doctor) }}">
        @csrf
        <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
        <input type="hidden" name="date" value="{{ old('date') }}">
        <input type="hidden" name="time" value="{{ old('time') }}">

        <div class="section-head"><h2>Motif de consultation</h2><small class="muted">Obligatoire</small></div>
        <div class="grid2 motifs">
            @foreach ($motifs as $key => [$label, $icon])
                <label class="motif">
                    <input type="radio" name="motif" value="{{ $key }}" {{ old('motif', 'suivi') === $key ? 'checked' : '' }}>
                    <span class="ms">{{ $icon }}</span><b>{{ $label }}</b><i class="radio"></i>
                </label>
            @endforeach
        </div>

        <div class="section-head"><h2>Créneaux disponibles</h2></div>
        <div class="days">
            @foreach ($days as $d)
                @php $label = $d['date']->isToday() ? "Aujourd'hui" : ($d['date']->isTomorrow() ? 'Demain' : $d['date']->isoFormat('dddd')); @endphp
                <button type="button" class="day" data-date="{{ $d['date']->toDateString() }}" data-label="{{ $label }}" {{ $d['count'] === 0 ? 'disabled' : '' }}>
                    <small>{{ $label }}</small><b>{{ $d['date']->isoFormat('D MMM') }}</b><em>{{ $d['count'] }} dispo</em>
                </button>
            @endforeach
        </div>

        <div class="card slots-card">
            <div class="row between wrap"><span class="muted"><span class="ms">schedule</span> Horaires disponibles pour <b id="slots-label">…</b></span><span class="tag tag-mint">{{ $doctor->location }}</span></div>
            <div id="slots" class="slots"></div>
            <p class="muted small"><span class="ms">info</span> Durée estimée de la séance : <b>30 minutes</b></p>
        </div>

        <label class="check card visio"><input type="checkbox" name="visio" value="1" {{ old('visio') ? 'checked' : '' }}> <span class="ms">videocam</span> Consultation en visio (téléconsultation)</label>

        <div class="sticky-cta">
            <p class="muted small center"><span class="ms">sms</span> Rappel SMS &amp; confirmation immédiate</p>
            <button type="submit" class="btn btn-primary block" disabled>Confirmer le rendez-vous <span class="ms">arrow_forward</span></button>
        </div>
    </form>
</div>
</div>
@endif

<p class="center"><a class="link" href="{{ route('appointments.mine') }}">Voir mes rendez-vous</a></p>
@endsection
