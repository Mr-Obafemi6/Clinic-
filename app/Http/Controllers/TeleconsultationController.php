<?php

namespace App\Http\Controllers;

use App\Models\Appointment;

class TeleconsultationController extends Controller
{
    public function show(?Appointment $appointment = null)
    {
        $user = auth()->user();

        if ($appointment) {
            abort_unless($appointment->user_id === $user->id || $appointment->doctor?->user_id === $user->id, 403);
        } else {
            $appointment = $user->appointments()->with('doctor.specialty')
                ->where('visio', true)->where('status', 'confirmed')
                ->where('starts_at', '>=', now()->subHour())->orderBy('starts_at')->first();
        }

        return view('teleconsultation', [
            'appointment' => $appointment?->load('doctor.specialty'),
            'room' => $appointment ? 'saint-luc-'.$appointment->uuid : null,
            'domain' => config('clinique.jitsi_domain'),
        ]);
    }
}
