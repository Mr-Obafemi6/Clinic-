<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private const EXPIRES_AFTER_MINUTES = 10;

    public function create(Appointment $appointment)
    {
        abort_unless($appointment->user_id === auth()->id() && in_array($appointment->status, ['confirmed', 'done'], true), 403);

        if ($appointment->isPaid()) {
            return redirect()->route('appointments.mine')->with('status', 'Ce rendez-vous est déjà réglé.');
        }

        return view('payments.create', [
            'appointment' => $appointment->load('doctor.specialty'),
            'demo' => PaymentManager::isDemo(),
        ]);
    }

    public function store(Request $r, Appointment $appointment)
    {
        abort_unless($appointment->user_id === auth()->id() && in_array($appointment->status, ['confirmed', 'done'], true), 403);

        if ($appointment->isPaid()) {
            return redirect()->route('appointments.mine')->with('status', 'Ce rendez-vous est déjà réglé.');
        }

        $data = $r->validate([
            'network' => 'required|in:TMONEY,FLOOZ',
            'phone' => ['required', 'regex:/^(?:\+?228)?[ .-]?\d{2}(?:[ .-]?\d{2}){3}$/'],
        ], [
            'network.required' => 'Choisissez T-Money ou Flooz.',
            'phone.required' => 'Indiquez votre numéro Mobile Money.',
            'phone.regex' => 'Numéro invalide. Exemple : 90 12 34 56.',
        ]);

        // Évite de relancer une demande déjà en cours pour le même rendez-vous
        $existing = $appointment->payments()->where('status', 'pending')
            ->where('created_at', '>', now()->subMinutes(self::EXPIRES_AFTER_MINUTES))->latest()->first();
        if ($existing) {
            return redirect()->route('payments.show', $existing);
        }

        $phone = preg_replace('/\D/', '', $data['phone']);
        if (strlen($phone) === 11 && str_starts_with($phone, '228')) {
            $phone = substr($phone, 3);
        }

        $payment = Payment::create([
            'user_id' => auth()->id(),
            'appointment_id' => $appointment->id,
            'amount' => $appointment->doctor->price, // toujours calculé côté serveur
            'network' => $data['network'],
            'phone' => $phone,
            'identifier' => 'SL-'.strtoupper(Str::random(10)),
        ]);

        try {
            $payment->update(['tx_reference' => PaymentManager::driver()->charge($payment)]);
        } catch (\Throwable $e) {
            report($e);
            $payment->update(['status' => 'failed']);

            return back()->withErrors(['phone' => 'La demande de paiement a échoué. Vérifiez le numéro puis réessayez.'])->withInput();
        }

        return redirect()->route('payments.show', $payment);
    }

    public function show(Payment $payment)
    {
        abort_unless($payment->user_id === auth()->id(), 403);

        return view('payments.show', ['payment' => $payment->load('appointment.doctor')]);
    }

    public function status(Payment $payment)
    {
        abort_unless($payment->user_id === auth()->id(), 403);

        if ($payment->status === 'pending') {
            if ($payment->created_at->lt(now()->subMinutes(self::EXPIRES_AFTER_MINUTES))) {
                $payment->update(['status' => 'failed']);
            } else {
                try {
                    $state = PaymentManager::driver()->check($payment);
                } catch (\Throwable $e) {
                    report($e);
                    $state = 'pending';
                }
                if ($state === 'paid') {
                    $payment->update(['status' => 'paid', 'paid_at' => now()]);
                } elseif ($state === 'failed') {
                    $payment->update(['status' => 'failed']);
                }
            }
        }

        return response()->json(['status' => $payment->status]);
    }
}
