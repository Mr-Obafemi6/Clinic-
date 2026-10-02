<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $guarded = [];
    protected $casts = ['valid_until' => 'date', 'sent_at' => 'datetime'];

    public function doctor() { return $this->belongsTo(Doctor::class); }

    public function getProgressAttribute(): int
    {
        return $this->renewals_total > 0 ? (int) round($this->renewals_done / $this->renewals_total * 100) : 0;
    }
}
