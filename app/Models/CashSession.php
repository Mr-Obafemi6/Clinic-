<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    protected $guarded = [];
    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime'];

    public function cashier() { return $this->belongsTo(User::class, 'cashier_id'); }
    public function entries() { return $this->hasMany(CashEntry::class); }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }

    public function cashTotal(): int
    {
        return (int) $this->entries()->where('status', 'paid')->where('method', 'cash')->sum('amount');
    }

    /** Espèces qui doivent se trouver dans le tiroir : fond de caisse + encaissements en espèces. */
    public function expectedCash(): int
    {
        return $this->opening_float + $this->cashTotal();
    }
}
