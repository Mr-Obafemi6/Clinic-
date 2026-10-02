<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /** Déclenche la demande de paiement sur le téléphone du patient. Retourne la référence côté agrégateur. */
    public function charge(Payment $payment): string;

    /** Interroge l'agrégateur : retourne 'pending', 'paid' ou 'failed'. */
    public function check(Payment $payment): string;
}
