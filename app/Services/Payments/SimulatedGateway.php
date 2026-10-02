<?php

namespace App\Services\Payments;

use App\Models\Payment;

/**
 * Mode démonstration : aucun argent n'est débité.
 * Le paiement réussit 5 secondes après la demande ; un numéro finissant par 0 simule un échec.
 */
class SimulatedGateway implements PaymentGateway
{
    public function charge(Payment $payment): string
    {
        return 'SIM-'.$payment->identifier;
    }

    public function check(Payment $payment): string
    {
        if (now()->timestamp - $payment->created_at->timestamp < 5) {
            return 'pending';
        }

        return str_ends_with($payment->phone, '0') ? 'failed' : 'paid';
    }
}
