@extends('layouts.app')
@section('title', 'Mes rendez-vous')
@section('subtitle', 'Mes rendez-vous')
@section('content')
<div class="section-head"><h2>À venir</h2><a class="link" href="{{ route('appointments.index') }}">Nouveau RDV</a></div>
<div class="cards-grid">
@forelse ($upcoming as $a)
    <article class="card next">
        <div class="row between">
            <div class="row gap"><img class="photo sm" src="{{ asset($a->doctor->photo) }}" alt=""><div><strong>{{ $a->doctor->name }}</strong><small>{{ $a->motif }}</small></div></div>
            @if ($a->visio)<span class="tag tag-blue"><span class="ms">videocam</span>Visio</span>@endif
        </div>
        <div class="grid2 info">
            <div class="info-cell"><span class="ms">calendar_today</span><div><small>Date &amp; Heure</small><b>{{ $a->starts_at->isoFormat('ddd D MMM • HH[h]mm') }}</b></div></div>
            <div class="info-cell"><span class="ms">location_on</span><div><small>Lieu</small><b>{{ $a->visio ? 'En ligne' : $a->doctor->location }}</b></div></div>
        </div>
        @if ($a->status === 'confirmed')
        <div class="grid2 actions">
            @if ($a->visio)<a href="{{ route('tele', $a) }}" class="btn btn-primary"><span class="ms">videocam</span>Rejoindre</a>@else<a href="{{ route('appointments.index', ['medecin' => $a->doctor_id]) }}" class="btn btn-primary"><span class="ms">edit_calendar</span>Reprogrammer</a>@endif
            <form method="POST" action="{{ route('appointments.cancel', $a) }}" data-confirm="Annuler ce rendez-vous ?">@csrf @method('DELETE')<button class="btn btn-ghost block" type="submit"><span class="ms">event_busy</span>Annuler</button></form>
        </div>
        @else
        <span class="tag tag-blue paid-tag"><span class="ms">task_alt</span>Consultation effectuée</span>
        @endif
        @if ($a->isPaid())
            <span class="tag tag-green paid-tag"><span class="ms">check_circle</span>Consultation réglée</span>
        @else
            <a href="{{ route('payments.create', $a) }}" class="btn btn-teal block"><span class="ms">smartphone</span>Payer {{ $a->doctor->price_label }}</a>
        @endif
    </article>
@empty
    <article class="card center"><p class="muted">Aucun rendez-vous à venir.</p></article>
@endforelse
</div>

@if ($past->isNotEmpty())
    <div class="section-head"><h2>Passés</h2></div>
    @foreach ($past as $a)
        <article class="card row between past"><div><b>{{ $a->doctor->name }}</b><small class="muted">{{ $a->motif }}</small></div><small class="muted">{{ $a->starts_at->isoFormat('D MMM YYYY') }}</small></article>
    @endforeach
@endif
@endsection
