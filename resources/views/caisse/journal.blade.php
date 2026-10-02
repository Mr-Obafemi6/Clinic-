@extends('layouts.app')
@use('App\Support\Money')
@section('title', 'Journal de caisse')
@section('subtitle', 'Journal de caisse')
@section('content')
<div class="row between wrap no-print">
    <h1>Journal du {{ $date->isoFormat('D MMMM YYYY') }}</h1>
    <div class="row gap-s">
        <a class="btn btn-ghost" href="{{ route('caisse.export', array_filter(['date' => $date->toDateString(), 'method' => $method])) }}"><span class="ms">download</span>Exporter (Excel)</a>
        <button type="button" class="btn btn-ghost" onclick="window.print()"><span class="ms">print</span>Imprimer</button>
    </div>
</div>

<form method="GET" action="{{ route('caisse.journal') }}" class="filters no-print">
    <label>Date<input type="date" name="date" value="{{ $date->toDateString() }}" max="{{ today()->toDateString() }}"></label>
    <label>Mode
        <select name="method">
            <option value="">Tous</option>
            @foreach ($methods as $key => $m)<option value="{{ $key }}" {{ $method === $key ? 'selected' : '' }}>{{ $m['label'] }}</option>@endforeach
        </select>
    </label>
    <button class="btn btn-primary" type="submit">Afficher</button>
</form>

<div class="kpis">
    <div class="kpi main"><small>Total de la journée</small><b>{{ Money::fcfa($totals['grand']) }}</b></div>
    <div class="kpi"><small>À la caisse ({{ $totals['count'] }} paiements)</small><b>{{ Money::fcfa($totals['caisse']) }}</b></div>
    <div class="kpi"><small>Paiements en ligne</small><b>{{ Money::fcfa($totals['online']) }}</b></div>
</div>

<article class="card">
    <h3>Encaissements à la caisse</h3>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead><tr><th>Heure</th><th>Reçu</th><th>Payeur</th><th>Motif</th><th>Mode</th><th>Référence</th><th class="num">Montant</th></tr></thead>
            <tbody>
            @forelse ($entries as $e)
                <tr class="{{ $e->isVoided() ? 'void' : '' }}">
                    <td>{{ $e->created_at->format('H\hi') }}</td>
                    <td><a class="link" href="{{ route('caisse.receipt', $e) }}">{{ $e->receipt_no }}</a>@if ($e->isVoided()) <span class="tag tag-red">Annulé</span>@endif</td>
                    <td>{{ $e->payer_name }}</td>
                    <td>{{ $e->label }}</td>
                    <td><span class="mpill"><span class="ms">{{ $e->method_icon }}</span>{{ $e->method_label }}</span></td>
                    <td>{{ $e->reference ?: '—' }}</td>
                    <td class="num">{{ Money::fcfa($e->amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucun encaissement ce jour-là.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</article>

@if ($online->isNotEmpty())
<article class="card">
    <h3>Paiements en ligne (T-Money / Flooz)</h3>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead><tr><th>Heure</th><th>Référence</th><th>Patient</th><th>Consultation</th><th>Réseau</th><th class="num">Montant</th></tr></thead>
            <tbody>
            @foreach ($online as $p)
                <tr>
                    <td>{{ $p->paid_at->format('H\hi') }}</td><td>{{ $p->identifier }}</td><td>{{ $p->user->name }}</td>
                    <td>{{ $p->appointment->doctor->name }}</td><td>{{ $p->network_label }}</td><td class="num">{{ Money::fcfa($p->amount) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</article>
@endif

@if ($sessions->isNotEmpty())
<article class="card">
    <h3>Sessions de caisse</h3>
    @foreach ($sessions as $s)
        <div class="mrow">
            <span class="ms">point_of_sale</span>
            <span>{{ $s->cashier->name }} — {{ $s->opened_at->format('H\hi') }} à {{ $s->closed_at ? $s->closed_at->format('H\hi') : 'en cours' }}</span>
            <b>@if ($s->closed_at){{ ($s->counted_cash - $s->expected_cash) === 0 ? 'Aucun écart' : 'Écart '.Money::fcfa($s->counted_cash - $s->expected_cash) }}@else Ouverte @endif</b>
        </div>
    @endforeach
</article>
@endif
@endsection
