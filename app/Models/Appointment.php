<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $guarded = [];
    protected $casts = ['starts_at' => 'datetime', 'visio' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function cashEntries() { return $this->hasMany(CashEntry::class); }

    public function amountPaid(): int
    {
        return (int) $this->payments()->where('status', 'paid')->sum('amount')
            + (int) $this->cashEntries()->where('status', 'paid')->sum('amount');
    }

    public function balance(): int
    {
        return max(0, (int) $this->doctor->price - $this->amountPaid());
    }

    public function isPaid(): bool
    {
        return $this->balance() <= 0;
    }
}
