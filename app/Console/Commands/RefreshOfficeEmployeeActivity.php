<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OfficeEmployee;

class RefreshOfficeEmployeeActivity extends Command
{
    protected $signature = 'office-employees:refresh-activity {--mode=month}';
    protected $description = 'Set فعال/غیر فعال based on activity in current month (or rolling N days)';

    public function handle(): int
    {
        $since = $this->option('mode') === 'month'
            ? now()->startOfMonth()               // current calendar month
            : now()->subDays((int)$this->option('mode') ?: 30); // rolling N days

        OfficeEmployee::refreshStatusesSince($since);

        $this->info("Statuses refreshed since {$since->toDateTimeString()}");
        return self::SUCCESS;
    }
}
