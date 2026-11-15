<?php
namespace App\Providers;

use App\Models\Event;
use App\Models\Information;
use App\Observers\EventObserver;
use App\Observers\InformationObserver;
use App\Support\ReverbProcess;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Default string length
        Schema::defaultStringLength(191);

        // Observer
        Information::observe(InformationObserver::class);
        Event::observe(EventObserver::class);

        if ($this->app->environment('local')) {
            ReverbProcess::ensureRunning();
        }
    }
}
