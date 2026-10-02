<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Usage : EnsureRole::class.':caissier,responsable' */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        abort_unless($request->user() && in_array($request->user()->role, $roles, true), 403, "Cet espace est réservé au personnel autorisé.");

        return $next($request);
    }
}
