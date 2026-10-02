<?php

namespace App\Support;

class Money
{
    public static function fcfa(int|float|null $amount): string
    {
        return number_format((float) ($amount ?? 0), 0, ',', ' ').' FCFA';
    }
}
