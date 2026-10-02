<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCashier
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isCashier(), 403, "Espace réservé au personnel de caisse.");

        return $next($request);
    }
}
