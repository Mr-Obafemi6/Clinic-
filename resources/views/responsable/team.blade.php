@extends('layouts.app')
@section('title', 'Équipe')
@section('subtitle', 'Équipe')
@section('wide', '1')
@section('content')
<h1>Équipe</h1>
<div class="split rec">
<div class="col">
    <form method="POST" action="{{ route('responsable.team.store') }}" class="card form" id="staffform">
        @csrf
        <h2>Créer un compte</h2>
        <label>Nom complet<input type="text" name="name" maxlength="120" required value="{{ old('name') }}"></label>
        <label>Adresse e-mail<input type="email" name="email" required value="{{ old('email') }}"></label>
        <label>Mot de passe provisoire (8 caractères minimum)<input type="text" name="password" minlength="8" required autocomplete="off"></label>
        <label>Fonction
            <select name="role" id="role">@foreach ($roles as $key => $label)<option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
        </label>
        <div id="doctor-fields" class="form" hidden>
            <label>Spécialité<select name="specialty_id">@foreach ($specialties as $s)<option value="{{ $s->id }}" {{ (int) old('specialty_id') === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach</select></label>
            <label>Prix de la consultation (FCFA)<input type="number" name="price" min="0" step="500" value="{{ old('price', 5000) }}"></label>
            <label>Intitulé (facultatif)<input type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="Ex. : Cardiologie & Médecine préventive"></label>
            <label>Cabinet (facultatif)<input type="text" name="location" maxlength="120" value="{{ old('location') }}" placeholder="Ex. : Bâtiment B - Étage 2"></label>
        </div>
        @if ($errors->any())@foreach ($errors->all() as $e)<p class="field-error">{{ $e }}</p>@endforeach @endif
        <button class="btn btn-primary block" type="submit"><span class="ms">person_add</span>Créer le compte</button>
    </form>
</div>
<div class="col">
    <article class="card">
        <h3>Comptes du personnel ({{ $staff->count() }})</h3>
        @foreach ($staff as $u)
            <div class="entry">
                <span class="avatar">{{ $u->initials }}</span>
                <span class="info"><b>{{ $u->name }}</b><small class="muted">{{ $u->email }}@if ($u->doctor) • {{ $u->doctor->specialty->name }}@endif</small></span>
                <span class="tag tag-blue">{{ $roles[$u->role] ?? $u->role }}</span>
            </div>
        @endforeach
    </article>
</div>
</div>
@endsection

@push('scripts')
<script>
    const role = document.getElementById('role'), box = document.getElementById('doctor-fields');
    const sync = () => { box.hidden = role.value !== 'medecin'; };
    role.addEventListener('change', sync); sync();
</script>
@endpush
