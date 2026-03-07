<?php

namespace App\Providers;

use App\Models\Information;
use App\Observers\InformationObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(80);

        Information::observe(InformationObserver::class);
    }
}