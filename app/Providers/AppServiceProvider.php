<?php

namespace App\Providers;

use App\Models\Information;
use App\Observers\InformationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Information::observe(InformationObserver::class);
    }
}