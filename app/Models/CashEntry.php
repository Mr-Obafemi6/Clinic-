<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashEntry extends Model
{
    protected $guarded = [];
    protected $casts = ['voided_at' => 'datetime'];

    public function session() { return $this->belongsTo(CashSession::class, 'cash_session_id'); }
    public function cashier() { return $this->belongsTo(User::class, 'cashier_id'); }
    public function appointment() { return $this->belongsTo(Appointment::class); }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function getMethodLabelAttribute(): string
    {
        return config("clinique.methods.{$this->method}.label", $this->method);
    }

    public function getMethodIconAttribute(): string
    {
        return config("clinique.methods.{$this->method}.icon", 'payments');
    }

    public function getChangeAttribute(): ?int
    {
        return $this->received !== null ? max(0, $this->received - $this->amount) : null;
    }
}
