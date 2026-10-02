<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallbackRequest extends Model
{
    protected $guarded = [];
    protected $casts = ['handled_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
}
