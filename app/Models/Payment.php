<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];
    protected $casts = ['paid_at' => 'datetime'];

    public function appointment() { return $this->belongsTo(Appointment::class); }

    public function getAmountLabelAttribute(): string
    {
        return number_format($this->amount, 0, ',', ' ').' FCFA';
    }

    public function getNetworkLabelAttribute(): string
    {
        return $this->network === 'FLOOZ' ? 'Flooz (Moov Africa)' : 'T-Money (Togocom)';
    }
}
