@extends('layouts.app')
@use('App\Support\Money')
@section('title', 'Supervision des caisses')
@section('subtitle', 'Supervision')
@section('wide', '1')
@section('content')
<div class="row between wrap"><div><p class="eyebrow"><i class="dot"></i> Espace responsable</p><h1>Supervision des caisses</h1></div>
    <a class="btn btn-ghost" href="{{ route('caisse.journal', ['date' => $from->toDateString()]) }}"><span class="ms">receipt_long</span>Journal détaillé</a></div>

<form method="GET" action="{{ route('responsable.index') }}" class="filters">
    <label>Du<input type="date" name="from" value="{{ $from->toDateString() }}" max="{{ today()->toDateString() }}"></label>
    <label>Au<input type="date" name="to" value="{{ $to->toDateString() }}" max="{{ today()->toDateString() }}"></label>
    <button class="btn btn-primary" type="submit">Afficher</button>
</form>

<div class="kpis kpis4">
    <div class="kpi main"><small>Total encaissé ({{ $count }} paiements à la caisse)</small><b>{{ Money::fcfa($grand) }}</b></div>
    <div class="kpi"><small>À la caisse</small><b>{{ Money::fcfa($caisse) }}</b></div>
    <div class="kpi"><small>Paiements en ligne</small><b>{{ Money::fcfa($online) }}</b></div>
    <div class="kpi"><small>Écart cumulé des clôtures</small><b class="{{ $gapTotal < 0 ? 'neg' : '' }}">{{ $gapTotal > 0 ? '+' : '' }}{{ Money::fcfa($gapTotal) }}</b></div>
</div>

<div class="split caisse-split">
<div class="col">
    <article class="card">
        <h3>Par mode de paiement</h3>
        @foreach ($methods as $key => $m)
            <div class="mrow"><span class="ms">{{ $m['icon'] }}</span><span>{{ $m['label'] }}</span><b>{{ Money::fcfa($byMethod[$key]) }}</b></div>
        @endforeach
        <div class="mrow"><span class="ms">public</span><span>En ligne (T-Money / Flooz)</span><b>{{ Money::fcfa($online) }}</b></div>
    </article>

    <article class="card">
        <h3>Par caissier</h3>
        @forelse ($byCashier as $c)
            <div class="mrow"><span class="ms">person</span><span>{{ $c['name'] }} <small class="muted">({{ $c['count'] }} paiements)</small></span><b>{{ Money::fcfa($c['total']) }}</b></div>
        @empty
            <p class="muted">Aucun encaissement sur la période.</p>
        @endforelse
    </article>
</div>

<div class="col">
    <article class="card">
        <h3>Sessions de caisse</h3>
        <div class="tbl-wrap"><table class="tbl">
            <thead><tr><th>Caissier</th><th>Ouverture</th><th>Clôture</th><th class="num">Attendu</th><th class="num">Compté</th><th class="num">Écart</th></tr></thead>
            <tbody>
            @forelse ($sessions as $s)
                @php $gap = $s->closed_at ? $s->counted_cash - $s->expected_cash : null; @endphp
                <tr>
                    <td>{{ $s->cashier->name }}</td>
                    <td>{{ $s->opened_at->isoFormat('D MMM HH[h]mm') }}</td>
                    <td>{{ $s->closed_at ? $s->closed_at->isoFormat('D MMM HH[h]mm') : 'En cours' }}</td>
                    <td class="num">{{ $s->closed_at ? Money::fcfa($s->expected_cash) : '—' }}</td>
                    <td class="num">{{ $s->closed_at ? Money::fcfa($s->counted_cash) : '—' }}</td>
                    <td class="num {{ $gap !== null && $gap !== 0 ? 'neg' : '' }}">{{ $gap === null ? '—' : (($gap > 0 ? '+' : '').Money::fcfa($gap)) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Aucune session sur la période.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </article>

    <article class="card">
        <h3>Paiements annulés ({{ $voided->count() }})</h3>
        @forelse ($voided as $e)
            <a class="entry" href="{{ route('caisse.receipt', $e) }}">
                <span class="svc-ico grey"><span class="ms">undo</span></span>
                <span class="info"><b>{{ $e->receipt_no }} — {{ $e->payer_name }}</b><small class="muted">{{ $e->void_reason }} • {{ $e->cashier->name }}</small></span>
                <span class="amt">{{ Money::fcfa($e->amount) }}</span>
            </a>
        @empty
            <p class="muted">Aucune annulation sur la période.</p>
        @endforelse
    </article>
</div>
</div>
@endsection
