<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ✅ Put broadcasting auth under /api (Bearer token friendly)
        Broadcast::routes([
            'middleware' => ['api', 'auth:sanctum'],
            'prefix'     => 'api', // ✅ /api/broadcasting/auth
        ]);

        require base_path('routes/channels.php');
    }
}
