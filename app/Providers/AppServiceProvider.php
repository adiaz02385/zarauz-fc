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
        // Force HTTPS-generated links on Railway in production as a safeguard.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
