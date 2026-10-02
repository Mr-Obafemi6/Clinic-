@extends('layouts.app')
@section('title', 'Paiement mobile')
@section('subtitle', 'Paiement mobile')
@section('content')
<div class="pay-wrap">
    <article class="card">
        <div class="row gap"><img class="photo sm" src="{{ asset($appointment->doctor->photo) }}" alt="">
            <div><strong>{{ $appointment->doctor->name }}</strong><small class="muted">{{ $appointment->motif }} • {{ $appointment->starts_at->isoFormat('ddd D MMM [à] HH[h]mm') }}</small></div></div>
        <div class="amount-row"><span class="muted">Consultation à régler</span><span class="amount">{{ $appointment->doctor->price_label }}</span></div>
    </article>

    <form method="POST" action="{{ route('payments.store', $appointment) }}" class="card form" id="payform">
        @csrf
        <h2>Moyen de paiement</h2>
        <div class="grid2 motifs">
            <label class="motif"><input type="radio" name="network" value="TMONEY" {{ old('network') === 'TMONEY' ? 'checked' : '' }}><span class="net-logo">T</span><b>T-Money</b><small class="muted">Togocom</small><i class="radio"></i></label>
            <label class="motif"><input type="radio" name="network" value="FLOOZ" {{ old('network') === 'FLOOZ' ? 'checked' : '' }}><span class="net-logo alt">F</span><b>Flooz</b><small class="muted">Moov Africa</small><i class="radio"></i></label>
        </div>
        <label>Numéro Mobile Money
            <span class="input-ico"><span class="ms">call</span><span class="pre">+228</span><input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="90 12 34 56" inputmode="numeric" autocomplete="tel-national" required></span>
        </label>
        @error('network')<p class="field-error">{{ $message }}</p>@enderror
        @error('phone')<p class="field-error">{{ $message }}</p>@enderror
        <p class="muted small"><span class="ms">phonelink_lock</span> Vous recevrez une demande de confirmation sur ce numéro. Validez avec votre code secret, qui ne nous est jamais communiqué.</p>
        @if ($demo)
            <p class="demo"><b>Mode démonstration :</b> aucun argent n'est débité. Le paiement réussit après 5 secondes ; un numéro finissant par 0 simule un échec.</p>
        @endif
        <button class="btn btn-primary block" type="submit">Payer {{ $appointment->doctor->price_label }}</button>
    </form>
    <p class="center"><a class="link" href="{{ route('appointments.mine') }}">Payer plus tard, à la clinique</a></p>
</div>
@endsection

@push('scripts')
<script>
    // Présélection indicative du réseau d'après le préfixe (modifiable par le patient).
    const phone = document.getElementById('phone');
    phone.addEventListener('input', () => {
        const d = phone.value.replace(/\D/g, '').replace(/^228/, '');
        if (d.length < 2) return;
        const p = d.slice(0, 2);
        const net = ['90','91','92','93','70','71','72'].includes(p) ? 'TMONEY' : (['96','97','98','99','79'].includes(p) ? 'FLOOZ' : null);
        if (net) document.querySelector(`input[name=network][value=${net}]`).checked = true;
    });
</script>
@endpush
