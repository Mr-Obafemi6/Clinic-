@extends('layouts.app')
@use('App\Support\Money')
@section('title', 'Nouvel encaissement')
@section('subtitle', 'Nouvel encaissement')
@section('content')
@if (! $session)
    <article class="card center pay-wrap">
        <span class="svc-ico blue"><span class="ms">lock</span></span>
        <h2>La caisse est fermée</h2>
        <p class="muted">Ouvrez la caisse avec le fond de caisse avant d'encaisser.</p>
        <a class="btn btn-primary" href="{{ route('caisse.index') }}">Aller à la caisse</a>
    </article>
@else
<form method="POST" action="{{ route('caisse.store') }}" id="cashform">
    @csrf
    <div class="split">
        <div class="col">
            <article class="card form">
                <h2>1. Qui paie ?</h2>
                <div class="seg">
                    <label><input type="radio" name="source" value="appointment" {{ old('source', 'appointment') === 'appointment' ? 'checked' : '' }}>Rendez-vous</label>
                    <label><input type="radio" name="source" value="walkin" {{ old('source') === 'walkin' ? 'checked' : '' }}>Passage libre</label>
                </div>
                <div data-panel="appointment" class="form">
                    <label>Rendez-vous à régler
                        <select name="appointment_id" id="appt">
                            <option value="">— Choisir —</option>
                            @foreach ($appointments as $a)
                                <option value="{{ $a->id }}" data-balance="{{ $a->balance() }}" {{ (string) old('appointment_id') === (string) $a->id ? 'selected' : '' }}>{{ $a->user->name }} — {{ $a->doctor->name }} — {{ $a->starts_at->isoFormat('ddd D MMM HH[h]mm') }} (reste {{ Money::fcfa($a->balance()) }})</option>
                            @endforeach
                        </select>
                    </label>
                    @if ($appointments->isEmpty())<p class="muted small">Aucun rendez-vous à régler pour le moment. Utilisez « Passage libre ».</p>@endif
                </div>
                <div data-panel="walkin" class="form" hidden>
                    <label>Nom du payeur<input type="text" name="payer_name" id="payer" maxlength="120" value="{{ old('payer_name') }}"></label>
                    <label>Motif
                        <select name="label">@foreach ($services as $s)<option {{ old('label') === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select>
                    </label>
                </div>
            </article>
        </div>

        <div class="col">
            <article class="card form">
                <h2>2. Montant et mode de paiement</h2>
                <label>Montant à encaisser (FCFA)
                    <input type="number" name="amount" id="amount" min="1" step="1" inputmode="numeric" value="{{ old('amount') }}" required>
                </label>
                <div class="methods">
                    @foreach ($methods as $key => $m)
                        <label class="motif"><input type="radio" name="method" value="{{ $key }}" data-ref="{{ $m['ref'] }}" {{ old('method', 'cash') === $key ? 'checked' : '' }}><span class="ms">{{ $m['icon'] }}</span><b>{{ $m['label'] }}</b><i class="radio"></i></label>
                    @endforeach
                </div>
                <label id="ref-box" hidden><span id="ref-label">Référence</span><input type="text" name="reference" id="reference" maxlength="120" value="{{ old('reference') }}"></label>
                <div id="cash-box" class="form">
                    <label>Montant remis par le patient (FCFA)<input type="number" name="received" id="received" min="0" step="1" inputmode="numeric" value="{{ old('received') }}"></label>
                    <p class="change" id="change" hidden></p>
                </div>
                @error('amount')<p class="field-error">{{ $message }}</p>@enderror
                @error('reference')<p class="field-error">{{ $message }}</p>@enderror
                @error('received')<p class="field-error">{{ $message }}</p>@enderror
                <button type="submit" class="btn btn-primary block"><span class="ms">task_alt</span>Encaisser</button>
            </article>
        </div>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
(() => {
    const f = document.getElementById('cashform');
    if (!f) return;
    const $ = s => f.querySelector(s);
    const appt = $('#appt'), payer = $('#payer'), amount = $('#amount'), received = $('#received');
    const refBox = $('#ref-box'), ref = $('#reference'), cashBox = $('#cash-box'), change = $('#change');

    const calc = () => {
        const a = +amount.value || 0, r = +received.value || 0;
        const show = $('[name=method]:checked').value === 'cash' && r > 0 && r >= a;
        change.hidden = !show;
        if (show) change.textContent = 'Monnaie à rendre : ' + (r - a).toLocaleString('fr-FR') + ' FCFA';
    };
    const syncSource = () => {
        const src = $('[name=source]:checked').value;
        f.querySelectorAll('[data-panel]').forEach(p => (p.hidden = p.dataset.panel !== src));
        appt.required = src === 'appointment';
        payer.required = src === 'walkin';
    };
    const syncMethod = () => {
        const r = $('[name=method]:checked');
        refBox.hidden = !r.dataset.ref;
        ref.required = !!r.dataset.ref;
        $('#ref-label').textContent = r.dataset.ref || 'Référence';
        cashBox.hidden = r.value !== 'cash';
        calc();
    };

    f.querySelectorAll('[name=source]').forEach(r => r.addEventListener('change', syncSource));
    f.querySelectorAll('[name=method]').forEach(r => r.addEventListener('change', syncMethod));
    appt.addEventListener('change', () => { const o = appt.selectedOptions[0]; if (o && o.dataset.balance) amount.value = o.dataset.balance; calc(); });
    amount.addEventListener('input', calc);
    received.addEventListener('input', calc);
    syncSource(); syncMethod();
})();
</script>
@endpush
