@extends('layouts.app')
@use('App\Support\Money')
@section('title', 'Reçu '.$entry->receipt_no)
@section('subtitle', 'Reçu de paiement')
@section('content')
<article class="ticket">
    <div class="ticket-head">
        <span class="logo"><span class="ms">add</span></span>
        <h2>{{ config('clinique.name') }}</h2>
        <small>{{ config('clinique.address') }}</small>
        <small>Tél. {{ config('clinique.phone') }}</small>
    </div>
    <p class="center caps ticket-title">Reçu de paiement</p>
    @if ($entry->isVoided())<p class="void-stamp">ANNULÉ</p>@endif
    <dl>
        <div><dt>N° de reçu</dt><dd>{{ $entry->receipt_no }}</dd></div>
        <div><dt>Date</dt><dd>{{ $entry->created_at->isoFormat('D MMM YYYY [à] HH[h]mm') }}</dd></div>
        <div><dt>Reçu de</dt><dd>{{ $entry->payer_name }}</dd></div>
        <div><dt>Motif</dt><dd>{{ $entry->label }}</dd></div>
        <div><dt>Mode</dt><dd>{{ $entry->method_label }}</dd></div>
        @if ($entry->reference)<div><dt>Référence</dt><dd>{{ $entry->reference }}</dd></div>@endif
        @if ($entry->received !== null)
            <div><dt>Espèces remises</dt><dd>{{ Money::fcfa($entry->received) }}</dd></div>
            <div><dt>Monnaie rendue</dt><dd>{{ Money::fcfa($entry->change) }}</dd></div>
        @endif
        <div class="total"><dt>Montant payé</dt><dd>{{ Money::fcfa($entry->amount) }}</dd></div>
        @if ($entry->appointment)<div><dt>Reste à payer</dt><dd>{{ Money::fcfa($entry->appointment->balance()) }}</dd></div>@endif
        <div><dt>Caissier</dt><dd>{{ $entry->cashier->name }}</dd></div>
    </dl>
    @if ($entry->isVoided())<p class="center small">Annulé le {{ $entry->voided_at->isoFormat('D MMM [à] HH[h]mm') }} — {{ $entry->void_reason }}</p>@endif
    <p class="center muted small">Merci de votre confiance.</p>
</article>

<div class="actions-bar no-print">
    <button type="button" class="btn btn-primary" onclick="window.print()"><span class="ms">print</span>Imprimer</button>
    @if (auth()->user()->isCashier())
        <a class="btn btn-ghost" href="{{ route('caisse.create') }}"><span class="ms">add_card</span>Nouvel encaissement</a>
        <a class="btn btn-ghost" href="{{ route('caisse.index') }}">Retour à la caisse</a>
    @else
        <a class="btn btn-ghost" href="{{ route('caisse.journal') }}">Retour au journal</a>
    @endif
</div>

@if (auth()->user()->isCashier() && ! $entry->isVoided() && $entry->session && $entry->session->isOpen())
    <form method="POST" action="{{ route('caisse.void', $entry) }}" class="card form no-print ticket-void" data-confirm="Annuler définitivement ce paiement ?">
        @csrf
        <h3>Annuler ce paiement</h3>
        <label>Motif de l'annulation<input type="text" name="reason" maxlength="200" required placeholder="Ex. : erreur de montant"></label>
        @error('reason')<p class="field-error">{{ $message }}</p>@enderror
        <button class="btn btn-ghost block" type="submit"><span class="ms">undo</span>Annuler le paiement</button>
    </form>
@endif
@endsection
