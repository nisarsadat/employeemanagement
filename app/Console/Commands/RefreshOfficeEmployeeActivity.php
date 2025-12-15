<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OfficeEmployee;

class RefreshOfficeEmployeeActivity extends Command
{
    protected $signature = 'office-employees:refresh-activity
        {--days=7 : Window size in days}
        {--min=5  : Minimum events on any resource to be active}';

    protected $description = 'Set فعال/غیر فعال based on: any resource has >= MIN events in last DAYS days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $min  = (int) $this->option('min');

        OfficeEmployee::refreshStatusesByWeeklyRule($min, $days);

        $this->info("Statuses refreshed (rule: any resource has >= {$min} events in last {$days} days).");
        return self::SUCCESS;
    }
}
