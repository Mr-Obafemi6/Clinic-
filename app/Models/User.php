<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function appointments() { return $this->hasMany(Appointment::class); }
    public function documents() { return $this->hasMany(Document::class); }
    public function prescriptions() { return $this->hasMany(Prescription::class); }

    public function doctor() { return $this->hasOne(Doctor::class, 'user_id'); }

    public function isDoctor(): bool
    {
        return $this->role === 'medecin';
    }

    public function isManager(): bool
    {
        return $this->role === 'responsable';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['caissier', 'medecin', 'responsable'], true);
    }

    /** Page d'accueil du personnel ; null pour un patient. */
    public function staffHomeUrl(): ?string
    {
        return match ($this->role) {
            'caissier' => route('caisse.index'),
            'medecin' => route('medecin.index'),
            'responsable' => route('responsable.index'),
            default => null,
        };
    }

    public function isCashier(): bool
    {
        return $this->role === 'caissier';
    }

    public function getFirstNameAttribute(): string
    {
        return explode(' ', trim($this->name))[0];
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->name)))->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }
}
