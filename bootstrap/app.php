<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',           // ✅ Make sure API routes are registered
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ Add Sanctum stateful middleware to the API group
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // (Optional) You can register route middleware aliases here too, e.g.:
        // $middleware->alias([
        //     'auth' => \App\Http\Middleware\Authenticate::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Here you can customize global exception handling if needed
    })
    ->create();
