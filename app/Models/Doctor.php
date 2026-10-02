<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $guarded = [];
    protected $casts = ['available' => 'boolean', 'rating' => 'float'];

    public const BASE_SLOTS = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00'];

    public function specialty() { return $this->belongsTo(Specialty::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function getPriceLabelAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ').' FCFA';
    }
    public function appointments() { return $this->hasMany(Appointment::class); }

    /** Créneaux libres pour une date donnée (heures déjà prises et passées exclues). */
    public function slotsFor(Carbon $date): array
    {
        $taken = $this->appointments()
            ->whereDate('starts_at', $date->toDateString())
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(fn ($a) => $a->starts_at->format('H:i'))
            ->all();

        return collect(self::BASE_SLOTS)
            ->reject(fn ($t) => in_array($t, $taken, true))
            ->filter(fn ($t) => Carbon::parse($date->toDateString().' '.$t)->isFuture())
            ->values()
            ->all();
    }
}
