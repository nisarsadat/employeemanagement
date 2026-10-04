<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OfficeEmployee;

class RefreshOfficeEmployeeActivity extends Command
{
    protected $signature = 'office-employees:refresh-activity';

    protected $description = 'Set فعال/غیر فعال based on: administrative job type OR any active resource';

    public function handle(): int
    {
        $changed = OfficeEmployee::refreshAllActivity();

        $this->info("Statuses refreshed (rule: administrative job type OR any active resource). {$changed} employee(s) changed.");

        return self::SUCCESS;
    }
}
