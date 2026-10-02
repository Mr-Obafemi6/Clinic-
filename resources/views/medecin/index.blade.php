@extends('layouts.app')
@section('title', 'Mon agenda')
@section('subtitle', 'Espace médecin')
@section('wide', '1')
@section('content')
<div class="row between wrap">
    <div><p class="eyebrow"><i class="dot"></i> {{ $doctor->name }}</p><h1>{{ now()->isoFormat('dddd D MMMM') }}</h1></div>
    <span class="tag tag-blue">{{ $today->count() }} consultation(s) aujourd'hui • {{ $today->where('status', 'done')->count() }} terminée(s)</span>
</div>

<div class="split caisse-split">
<div class="col">
    <div class="section-head"><h2>Aujourd'hui</h2></div>
    <article class="card">
        @forelse ($today as $a)
            <div class="entry {{ $a->status === 'done' ? 'done' : '' }}">
                <span class="time">{{ $a->starts_at->format('H\hi') }}</span>
                <span class="info">
                    <a href="{{ route('medecin.patient', $a->user_id) }}"><b>{{ $a->user->name }}</b></a>
                    <small class="muted">{{ $a->motif }}@if ($a->visio) • Visio @endif • {{ $a->isPaid() ? 'Réglé' : 'Non réglé' }}</small>
                </span>
                @if ($a->visio && $a->status === 'confirmed')<a class="btn btn-ghost sm" href="{{ route('tele', $a) }}"><span class="ms">videocam</span>Visio</a>@endif
                @if ($a->status === 'confirmed')
                    <form method="POST" action="{{ route('medecin.done', $a) }}">@csrf<button class="btn btn-teal sm" type="submit">Terminé</button></form>
                @else
                    <span class="tag tag-green">Terminé</span>
                @endif
            </div>
        @empty
            <p class="muted">Aucune consultation prévue aujourd'hui.</p>
        @endforelse
    </article>

    <div class="section-head"><h2>7 prochains jours</h2></div>
    <article class="card">
        @forelse ($upcoming as $a)
            <div class="entry">
                <span class="time">{{ $a->starts_at->isoFormat('ddd D') }}<small>{{ $a->starts_at->format('H\hi') }}</small></span>
                <span class="info"><a href="{{ route('medecin.patient', $a->user_id) }}"><b>{{ $a->user->name }}</b></a><small class="muted">{{ $a->motif }}@if ($a->visio) • Visio @endif</small></span>
            </div>
        @empty
            <p class="muted">Rien de prévu cette semaine.</p>
        @endforelse
    </article>
</div>

<div class="col">
    <div class="section-head" id="rappels"><h2>Demandes de rappel</h2><span class="tag {{ $callbacks->isEmpty() ? 'tag-grey' : 'tag-red' }}">{{ $callbacks->count() }} en attente</span></div>
    <article class="card">
        @forelse ($callbacks as $c)
            <div class="entry">
                <span class="svc-ico mint"><span class="ms">call</span></span>
                <span class="info"><a href="tel:{{ preg_replace('/\D/', '', $c->phone) }}"><b>+228 {{ $c->phone }}</b></a><small class="muted">{{ $c->user?->name ?? 'Patient non connecté' }} • {{ $c->created_at->diffForHumans() }}</small></span>
                <form method="POST" action="{{ route('medecin.callbacks.done', $c) }}">@csrf<button class="btn btn-ghost sm" type="submit">Rappelé</button></form>
            </div>
        @empty
            <p class="muted">Aucune demande en attente.</p>
        @endforelse
    </article>

    <div class="section-head"><h2>Patients récents</h2></div>
    <article class="card">
        @forelse ($recent as $a)
            <a class="entry" href="{{ route('medecin.patient', $a->user_id) }}">
                <span class="avatar">{{ $a->user->initials }}</span>
                <span class="info"><b>{{ $a->user->name }}</b><small class="muted">Dernier rendez-vous : {{ $a->starts_at->isoFormat('D MMM YYYY') }}</small></span>
                <span class="ms">chevron_right</span>
            </a>
        @empty
            <p class="muted">Aucun patient pour le moment.</p>
        @endforelse
    </article>
</div>
</div>
@endsection
