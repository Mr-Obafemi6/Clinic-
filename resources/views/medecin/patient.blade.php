@extends('layouts.app')
@section('title', $patient->name)
@section('subtitle', 'Dossier patient')
@section('wide', '1')
@section('content')
<p><a class="link" href="{{ route('medecin.index') }}">← Retour à l'agenda</a></p>
<div class="split rec">
<div class="col">
    <article class="card patient">
        <div class="row gap"><span class="avatar big">{{ $patient->initials }}</span>
            <div><h2>{{ $patient->name }}</h2><small class="muted">N° assuré : {{ $patient->num_secu ?: 'non renseigné' }}</small></div></div>
        <div class="grid3 facts">
            <div class="fact"><small><span class="ms">water_drop</span>Groupe</small><b>{{ $patient->groupe_sanguin ?: '—' }}</b></div>
            <div class="fact fact-red"><small><span class="ms">warning</span>Allergies</small><b>{{ $patient->allergies ?: 'Aucune' }}</b></div>
            <div class="fact"><small><span class="ms">stethoscope</span>Médecin</small><b>{{ $patient->medecin_traitant ?: '—' }}</b></div>
        </div>
    </article>

    <article class="card">
        <h3>Rendez-vous avec vous</h3>
        @foreach ($visits as $v)
            <div class="mrow"><span class="ms">event</span><span>{{ $v->starts_at->isoFormat('D MMM YYYY • HH[h]mm') }} — {{ $v->motif }}</span><b>{{ $v->status === 'done' ? 'Terminé' : ($v->status === 'cancelled' ? 'Annulé' : 'Prévu') }}</b></div>
        @endforeach
    </article>
</div>

<div class="col">
    <div class="section-head" style="margin-top:0"><h2>Documents du dossier</h2></div>
    <article class="card">
        @forelse ($documents as $d)
            <div class="entry">
                <span class="svc-ico {{ $d->type === 'analyse' ? 'mint' : 'blue' }}"><span class="ms">{{ $d->type === 'analyse' ? 'water_drop' : 'radiology' }}</span></span>
                <span class="info"><b>{{ $d->title }}</b><small class="muted">{{ $d->source }} • {{ $d->issued_at->isoFormat('D MMM YYYY') }}</small></span>
                <span class="tag {{ $d->status === 'Normal' ? 'tag-green' : 'tag-blue' }}">{{ $d->status }}</span>
            </div>
        @empty
            <p class="muted">Aucun document.</p>
        @endforelse
    </article>

    <form method="POST" action="{{ route('medecin.documents.store', $patient) }}" enctype="multipart/form-data" class="card form">
        @csrf
        <h3>Ajouter un document (résultat, compte-rendu)</h3>
        <label>Titre<input type="text" name="title" maxlength="120" required value="{{ old('title') }}" placeholder="Ex. : Bilan sanguin complet"></label>
        <div class="grid2">
            <label>Type<select name="type"><option value="analyse">Résultat d'analyse</option><option value="imagerie">Imagerie</option></select></label>
            <label>Résultat<select name="status"><option value="Disponible">Disponible</option><option value="Normal">Normal</option></select></label>
        </div>
        <label>Résumé (facultatif)<input type="text" name="summary" maxlength="500" value="{{ old('summary') }}"></label>
        <label>Fichier (PDF, JPG ou PNG, 10 Mo max)<input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required></label>
        @error('file')<p class="field-error">{{ $message }}</p>@enderror
        @error('title')<p class="field-error">{{ $message }}</p>@enderror
        <button class="btn btn-primary block" type="submit"><span class="ms">upload_file</span>Ajouter au dossier</button>
    </form>

    <div class="section-head"><h2>Ordonnances</h2></div>
    <article class="card">
        @forelse ($prescriptions as $p)
            <div class="mrow"><span class="ms">assignment</span><span>{{ $p->title }}<small class="muted" style="display:block">{{ $p->doctor->name }} • jusqu'au {{ $p->valid_until->isoFormat('D MMM YYYY') }}</small></span><b>{{ $p->renewals_done }}/{{ $p->renewals_total }}</b></div>
        @empty
            <p class="muted">Aucune ordonnance.</p>
        @endforelse
    </article>

    <form method="POST" action="{{ route('medecin.prescriptions.store', $patient) }}" class="card form">
        @csrf
        <h3>Nouvelle ordonnance</h3>
        <label>Intitulé<input type="text" name="title" maxlength="120" required placeholder="Ex. : Traitement antihypertenseur"></label>
        <div class="grid2">
            <label>Valable jusqu'au<input type="date" name="valid_until" min="{{ today()->addDay()->toDateString() }}" required></label>
            <label>Renouvellements<input type="number" name="renewals_total" min="1" max="12" value="3" required></label>
        </div>
        @error('valid_until')<p class="field-error">{{ $message }}</p>@enderror
        <button class="btn btn-teal block" type="submit"><span class="ms">prescriptions</span>Créer l'ordonnance</button>
    </form>
</div>
</div>
@endsection
