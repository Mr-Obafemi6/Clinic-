<?php

namespace App\Services\Payments;

class PaymentManager
{
    public static function driver(): PaymentGateway
    {
        return config('clinique.payments.driver') === 'paygate'
            ? new PaygateGateway()
            : new SimulatedGateway();
    }

    public static function isDemo(): bool
    {
        return config('clinique.payments.driver') !== 'paygate';
    }
}
