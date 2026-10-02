@extends('layouts.app')
@section('title', 'Téléconsultation')
@section('subtitle', 'Téléconsultation')
@section('content')
@if (! $appointment)
    <article class="card center">
        <span class="svc-ico blue"><span class="ms">videocam</span></span>
        <h2>Aucune téléconsultation prévue</h2>
        <p class="muted">Réservez un rendez-vous en cochant « Consultation en visio ».</p>
        <a href="{{ route('appointments.index') }}" class="btn btn-primary">Prendre rendez-vous</a>
    </article>
@else
    <article class="card row between">
        <div class="row gap"><img class="photo sm" src="{{ asset($appointment->doctor->photo) }}" alt=""><div><strong>{{ $appointment->doctor->name }} | {{ $appointment->doctor->specialty->name }}</strong><small class="muted">{{ $appointment->starts_at->isoFormat('dddd D MMMM [à] HH[h]mm') }}</small></div></div>
        <span class="tag tag-red"><i class="dot live"></i>Live</span>
    </article>
    <div id="visio" class="video" aria-label="Salle de téléconsultation"></div>
    <p class="muted small center"><span class="ms">lock</span> Échange chiffré. Autorisez la caméra et le micro quand votre navigateur le demande.</p>
    <p class="center"><a class="btn btn-ghost" href="{{ route('home') }}"><span class="ms">call_end</span>Quitter la salle</a></p>
@endif
@endsection

@if ($appointment)
@push('scripts')
<script src="https://{{ $domain }}/external_api.js"></script>
<script>
    new JitsiMeetExternalAPI(@json($domain), {
        roomName: @json($room),
        parentNode: document.getElementById('visio'),
        width: '100%', height: '100%', lang: 'fr',
        userInfo: { displayName: @json(auth()->user()->name) },
        configOverwrite: { prejoinPageEnabled: true, disableDeepLinking: true },
        interfaceConfigOverwrite: { MOBILE_APP_PROMO: false, SHOW_JITSI_WATERMARK: false },
    }).addListener('readyToClose', () => { window.location = @json(route('home')); });
</script>
@endpush
@endif
