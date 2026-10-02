@extends('layouts.app')
@section('title', 'Mon dossier')
@section('subtitle', 'Mon Dossier')
@section('content')
<div class="split rec">
<div class="col">
<article class="card patient">
    <div class="row between">
        <div class="row gap"><span class="avatar big">{{ $user->initials }}</span>
            <div><h2>{{ $user->name }} <span class="ms verified">verified</span></h2><small class="muted">N° assuré : {{ $user->num_secu ?: 'non renseigné' }}</small></div>
        </div>
        <span class="icon-btn" aria-hidden="true"><span class="ms">qr_code_2</span></span>
    </div>
    <div class="grid3 facts">
        <div class="fact"><small><span class="ms">water_drop</span>Groupe</small><b>{{ $user->groupe_sanguin ?: '—' }}</b></div>
        <div class="fact fact-red"><small><span class="ms">warning</span>Allergies</small><b>{{ $user->allergies ?: 'Aucune' }}</b></div>
        <div class="fact"><small><span class="ms">stethoscope</span>Médecin</small><b>{{ $user->medecin_traitant ?: '—' }}</b></div>
    </div>
</article>

<article class="card hds"><span class="svc-ico mint"><span class="ms">shield_lock</span></span>
    <div><b>Espace de santé sécurisé <span class="ms">lock_open</span></b>
    <p class="muted">Vos informations sont protégées par le <b>secret médical</b> et la législation togolaise sur les <b>données personnelles</b>. Seuls vous et les professionnels autorisés pouvez y accéder.</p></div></article>

<form method="POST" action="{{ route('logout') }}" class="center">@csrf<button class="btn btn-ghost" type="submit"><span class="ms">logout</span>Se déconnecter</button></form>
</div>
<div class="col">
<div class="section-head"><h2>Documents médicaux</h2><span class="tag tag-grey">{{ $total }} {{ Str::plural('récent', $total) }}</span></div>
<div class="chips scroll">
    <a class="chip {{ $filter ? '' : 'active' }}" href="{{ route('record') }}"><span class="ms">grid_view</span>Tous</a>
    @foreach ($filters as $key => $label)
        <a class="chip {{ $filter === $key ? 'active' : '' }}" href="{{ route('record', ['type' => $key]) }}">{{ $label }}</a>
    @endforeach
</div>

@foreach ($documents as $doc)
    <article class="card doc">
        <div class="row between top">
            <div class="row gap"><span class="svc-ico {{ $doc->type === 'analyse' ? 'mint' : 'blue' }}"><span class="ms">{{ $doc->type === 'analyse' ? 'water_drop' : 'radiology' }}</span></span>
                <div><b>{{ $doc->title }}</b><small class="muted">{{ $doc->source }} • {{ $doc->issued_at->isYesterday() ? 'Hier, '.$doc->issued_at->format('H:i') : $doc->issued_at->isoFormat('D MMMM YYYY') }}</small></div></div>
            <span class="tag {{ $doc->status === 'Normal' ? 'tag-green' : 'tag-blue' }}">{{ $doc->status }}</span>
        </div>
        @if ($doc->type === 'imagerie')
            <div class="imaging"><span class="ms">radiology</span><div><b>Compte-rendu d'imagerie</b><small>Clichés haute définition (DICOM) inclus</small></div></div>
        @endif
        @if ($doc->summary)
            <p class="summary">@if ($doc->type === 'imagerie')<b>Extrait conclusion :</b> @else<span class="ms">task_alt</span> @endif{{ Str::limit($doc->summary, 110) }}@if ($doc->pages)<small>{{ $doc->pages }} pages</small>@endif</p>
        @endif
        <div class="grid2 actions">
            <a class="btn btn-ghost" href="{{ route('record.download', [$doc, 'inline' => 1]) }}" target="_blank"><span class="ms">visibility</span>{{ $doc->type === 'imagerie' ? 'Lire le compte-rendu' : 'Aperçu' }}</a>
            <a class="btn btn-primary" href="{{ route('record.download', $doc) }}"><span class="ms">download</span>Télécharger</a>
        </div>
    </article>
@endforeach

@foreach ($prescriptions as $p)
    <article class="card doc">
        <div class="row between top">
            <div class="row gap"><span class="svc-ico mint"><span class="ms">assignment</span></span><div><b>{{ $p->title }}</b><small class="muted">{{ $p->doctor->name }}</small></div></div>
            <span class="tag {{ $p->valid_until->isPast() ? 'tag-red' : 'tag-green' }}"><span class="ms">event_available</span>{{ $p->valid_until->isPast() ? 'Expirée' : 'Active' }}</span>
        </div>
        <div class="validity">
            <div class="row between"><small>Validité de dispensation :</small><b>Jusqu'au {{ $p->valid_until->isoFormat('D MMMM YYYY') }}</b></div>
            <div class="bar"><i style="width: {{ $p->progress }}%"></i></div>
            <small class="right">Renouvellement {{ $p->renewals_done }}/{{ $p->renewals_total }} effectué</small>
        </div>
        @if ($p->sent_at)
            <p class="muted small center"><span class="ms">check_circle</span> Envoyée à votre pharmacie le {{ $p->sent_at->isoFormat('D MMM [à] HH[h]mm') }}</p>
        @else
            <form method="POST" action="{{ route('record.pharmacy', $p) }}" class="send">@csrf<button class="btn btn-teal block" type="submit"><span class="ms">local_pharmacy</span>Envoyer à ma pharmacie</button></form>
        @endif
    </article>
@endforeach

@if ($documents->isEmpty() && $prescriptions->isEmpty())
    <article class="card center"><p class="muted">Aucun document dans cette catégorie.</p></article>
@endif

</div>
</div>
@endsection
