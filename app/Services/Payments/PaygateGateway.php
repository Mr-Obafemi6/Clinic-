<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * PayGate Global (T-Money et Flooz, Togo).
 * ATTENTION : endpoints et codes de statut écrits d'après la documentation publique connue de PayGate.
 * À VÉRIFIER dans votre espace marchand PayGate et à tester avec un petit montant avant toute mise en production.
 */
class PaygateGateway implements PaymentGateway
{
    private function url(string $path): string
    {
        return rtrim(config('clinique.payments.paygate_base'), '/').$path;
    }

    private function token(): string
    {
        return (string) config('clinique.payments.paygate_token');
    }

    public function charge(Payment $payment): string
    {
        $res = Http::timeout(20)->asJson()->post($this->url('/api/v1/pay'), [
            'auth_token' => $this->token(),
            'phone_number' => $payment->phone,
            'amount' => $payment->amount,
            'description' => 'Consultation - '.config('clinique.name'),
            'identifier' => $payment->identifier,
            'network' => $payment->network, // TMONEY | FLOOZ
        ]);

        // Succès attendu : status = 0. Tout le reste est traité comme un refus.
        if (! $res->successful() || $res->json('status') !== 0) {
            throw new RuntimeException('PayGate a refusé la demande : '.$res->body());
        }

        return (string) $res->json('tx_reference');
    }

    public function check(Payment $payment): string
    {
        $res = Http::timeout(15)->asJson()->post($this->url('/api/v2/status'), [
            'auth_token' => $this->token(),
            'identifier' => $payment->identifier,
        ]);

        $status = $res->json('status');
        if (! $res->successful() || $status === null) {
            return 'pending'; // dans le doute, on ne valide JAMAIS un paiement
        }

        return match ((int) $status) {
            0 => 'paid',
            4, 6 => 'failed', // expiré / annulé
            default => 'pending',
        };
    }
}
