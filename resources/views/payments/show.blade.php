@extends('layouts.app')
@section('title', 'Paiement')
@section('subtitle', 'Paiement mobile')
@section('content')
<div class="pay-wrap">
    <article class="card center pay-state" id="state" data-status="{{ $payment->status }}">
        @if ($payment->status === 'paid')
            <span class="state-ico ok"><span class="ms fill">check_circle</span></span>
            <h2>Paiement reçu</h2>
            <p class="muted">Merci. Votre consultation est réglée.</p>
        @elseif ($payment->status === 'failed')
            <span class="state-ico bad"><span class="ms fill">cancel</span></span>
            <h2>Paiement non abouti</h2>
            <p class="muted">La demande a expiré, a été refusée ou annulée. Aucun montant n'a été conservé.</p>
            <a class="btn btn-primary" href="{{ route('payments.create', $payment->appointment_id) }}">Réessayer</a>
        @else
            <span class="spinner" aria-hidden="true"></span>
            <h2>Validez sur votre téléphone</h2>
            <p class="muted">Une demande de <b>{{ $payment->amount_label }}</b> a été envoyée au <b>+228 {{ $payment->phone }}</b> ({{ $payment->network_label }}). Saisissez votre code secret pour confirmer.</p>
            <p class="muted small" id="wait-note">En attente de confirmation…</p>
        @endif
    </article>

    <article class="card receipt">
        <h3>Détails</h3>
        <dl>
            <div><dt>Consultation</dt><dd>{{ $payment->appointment->doctor->name }}</dd></div>
            <div><dt>Rendez-vous</dt><dd>{{ $payment->appointment->starts_at->isoFormat('ddd D MMM [à] HH[h]mm') }}</dd></div>
            <div><dt>Montant</dt><dd>{{ $payment->amount_label }}</dd></div>
            <div><dt>Moyen de paiement</dt><dd>{{ $payment->network_label }}</dd></div>
            <div><dt>Référence</dt><dd>{{ $payment->identifier }}</dd></div>
            @if ($payment->paid_at)<div><dt>Payé le</dt><dd>{{ $payment->paid_at->isoFormat('D MMM YYYY [à] HH[h]mm') }}</dd></div>@endif
        </dl>
    </article>
    <p class="center"><a class="btn btn-ghost" href="{{ route('appointments.mine') }}">Mes rendez-vous</a></p>
</div>
@endsection

@if ($payment->status === 'pending')
@push('scripts')
<script>
    (() => {
        const url = @json(route('payments.status', $payment));
        const note = document.getElementById('wait-note');
        const started = Date.now();
        const tick = async () => {
            try {
                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                const { status } = await res.json();
                if (status !== 'pending') return location.reload();
            } catch (e) { /* on réessaie */ }
            if (Date.now() - started > 10 * 60 * 1000) { note.textContent = 'La demande a expiré. Rechargez la page.'; return; }
            setTimeout(tick, 3000);
        };
        setTimeout(tick, 3000);
    })();
</script>
@endpush
@endif
