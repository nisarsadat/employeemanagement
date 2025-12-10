<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the broadcasting routes (/broadcasting/auth)
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        // Load your channels definitions
        require base_path('routes/channels.php');
    }
}
