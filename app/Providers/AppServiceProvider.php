<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Derrière un tunnel HTTPS (ngrok, Cloudflare Tunnel…) ou un proxy d'hébergement :
        // générer les liens, le CSS et le JS en https pour éviter le « contenu mixte » bloqué.
        if (str_contains((string) request()->header('X-Forwarded-Proto'), 'https')) {
            URL::forceScheme('https');
        }
    }
}
