<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly refresh of OfficeEmployee activity
// Rule: ACTIVE if any resource has >= 5 events in last 7 days
Schedule::command('office-employees:refresh-activity --days=7 --min=5')
    ->dailyAt('01:30');

    
Schedule::command('informations:check-missing-results')
    ->dailyAt('09:00')
    ->withoutOverlapping();