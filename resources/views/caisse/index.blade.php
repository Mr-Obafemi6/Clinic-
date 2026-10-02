@extends('layouts.app')
@use('App\Support\Money')
@section('title', 'Caisse')
@section('subtitle', 'Caisse')
@section('content')
<div class="row between wrap">
    <div><p class="eyebrow"><i class="dot"></i> Espace caisse</p><h1>{{ now()->isoFormat('dddd D MMMM') }}</h1></div>
    @if ($session)<a class="btn btn-primary" href="{{ route('caisse.create') }}"><span class="ms">add_card</span>Nouvel encaissement</a>@endif
</div>

<div class="split caisse-split">
<div class="col">
    @if ($session)
        <article class="card form">
            <div class="row between"><h2>Caisse ouverte</h2><span class="tag tag-green"><i class="dot"></i>Ouverte</span></div>
            <p class="muted">Depuis {{ $session->opened_at->format('H\hi') }} • Fond de caisse {{ Money::fcfa($session->opening_float) }}</p>
            <div class="info-cell"><span class="ms">payments</span><div><small>Espèces attendues dans le tiroir</small><b>{{ Money::fcfa($session->expectedCash()) }}</b></div></div>
            <form method="POST" action="{{ route('caisse.close') }}" class="form" data-confirm="Clôturer la caisse ? Plus aucun encaissement ne sera possible jusqu'à la réouverture.">
                @csrf
                <label>Espèces comptées dans le tiroir (FCFA)
                    <input type="number" name="counted_cash" min="0" step="1" inputmode="numeric" required>
                </label>
                <label>Note (facultatif)<input type="text" name="note" maxlength="200" placeholder="Ex. : écart expliqué par un rendu de monnaie"></label>
                <button class="btn btn-ghost block" type="submit"><span class="ms">lock</span>Clôturer la caisse</button>
            </form>
        </article>
    @else
        <article class="card form">
            <div class="row between"><h2>Caisse fermée</h2><span class="tag tag-grey">Fermée</span></div>
            <p class="muted">Comptez les espèces du tiroir avant d'ouvrir : c'est le fond de caisse.</p>
            <form method="POST" action="{{ route('caisse.open') }}" class="form">
                @csrf
                <label>Fond de caisse (FCFA)
                    <input type="number" name="opening_float" min="0" step="1" inputmode="numeric" value="0" required>
                </label>
                <button class="btn btn-primary block" type="submit"><span class="ms">lock_open</span>Ouvrir la caisse</button>
            </form>
        </article>
    @endif

    @if ($last)
        <article class="card">
            <h3>Dernière clôture</h3>
            <p class="muted small">{{ $last->closed_at->isoFormat('ddd D MMM [à] HH[h]mm') }}</p>
            @php $gap = $last->counted_cash - $last->expected_cash; @endphp
            <div class="mrow"><span>Espèces attendues</span><b>{{ Money::fcfa($last->expected_cash) }}</b></div>
            <div class="mrow"><span>Espèces comptées</span><b>{{ Money::fcfa($last->counted_cash) }}</b></div>
            <div class="mrow"><span>Écart</span><b class="{{ $gap === 0 ? 'ok' : ($gap < 0 ? 'neg' : '') }}">{{ $gap > 0 ? '+' : '' }}{{ Money::fcfa($gap) }}</b></div>
        </article>
    @endif
</div>

<div class="col">
    <div class="kpis">
        <div class="kpi main"><small>Total encaissé aujourd'hui</small><b>{{ Money::fcfa($totals['grand']) }}</b></div>
        <div class="kpi"><small>À la caisse</small><b>{{ Money::fcfa($totals['caisse']) }}</b></div>
        <div class="kpi"><small>Paiements en ligne</small><b>{{ Money::fcfa($totals['online']) }}</b></div>
    </div>

    <article class="card">
        <div class="row between"><h3>Par mode de paiement</h3><a class="link" href="{{ route('caisse.journal') }}">Journal complet</a></div>
        @foreach ($methods as $key => $m)
            <div class="mrow"><span class="ms">{{ $m['icon'] }}</span><span>{{ $m['label'] }}</span><b>{{ Money::fcfa($totals['by_method'][$key]) }}</b></div>
        @endforeach
        <div class="mrow"><span class="ms">public</span><span>En ligne (T-Money / Flooz)</span><b>{{ Money::fcfa($totals['online']) }}</b></div>
    </article>

    <article class="card">
        <div class="row between"><h3>Derniers encaissements</h3><small class="muted">{{ $totals['count'] }} payé(s) • {{ $totals['voided'] }} annulé(s)</small></div>
        @forelse ($entries as $e)
            <a class="entry {{ $e->isVoided() ? 'void' : '' }}" href="{{ route('caisse.receipt', $e) }}">
                <span class="svc-ico mint"><span class="ms">{{ $e->method_icon }}</span></span>
                <span class="info"><b>{{ $e->payer_name }}</b><small class="muted">{{ $e->label }} • {{ $e->created_at->format('H\hi') }} • {{ $e->receipt_no }}</small></span>
                <span class="amt">{{ Money::fcfa($e->amount) }}</span>
            </a>
        @empty
            <p class="muted">Aucun encaissement pour le moment.</p>
        @endforelse
    </article>
</div>
</div>
@endsection
