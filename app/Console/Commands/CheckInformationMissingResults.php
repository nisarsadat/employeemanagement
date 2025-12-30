<?php

namespace App\Console\Commands;

use App\Models\Information;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckInformationMissingResults extends Command
{
    protected $signature = 'informations:check-missing-results';
    protected $description = 'Notify when Information.result stays null for 7 days; repeat once after 3 months; stop afterwards';

    public function handle(): int
    {
        $now = Carbon::now();

        // ✅ We rely on created_at to avoid issues with information_saved_date being string in DB
        // Rule:
        // - after 7 days => notify once
        // - after 3 months from first notify => notify once more
        // - after that => stop forever

        $recipients = $this->resolveRecipients();

        if ($recipients->isEmpty()) {
            $this->info('No recipients found (no users matched your recipient rule).');
            return self::SUCCESS;
        }

        // -------------------------------
        // 1) FIRST notification (>= 7 days, never notified)
        // -------------------------------
        $firstBatch = Information::query()
            ->whereNull('result')
            ->whereNotNull('created_at')
            ->where('created_at', '<=', $now->copy()->subDays(7))
            ->where('result_missing_notify_count', 0)
            ->orderBy('id')
            ->limit(500)
            ->get();

        foreach ($firstBatch as $info) {
            foreach ($recipients as $user) {
                NotificationService::record(
                    $user,
                    module: 'اطلاع',
                    action: 'missing_result',
                    title: 'نتیجه اطلاع ثبت نشده است',
                    message: 'این اطلاع بعد از ۷ روز هنوز نتیجه ندارد: ' . ($info->name ?? ('ID: ' . $info->id)),
                    routeName: 'informations',
                    routeParams: ['id' => $info->id],
                    level: 'warning',
                    changes: null
                );
            }

            $info->update([
                'result_missing_first_notified_at' => $now,
                'result_missing_last_notified_at'  => $now,
                'result_missing_notify_count'      => 1,
            ]);
        }

        // -------------------------------
        // 2) SECOND notification (still null, 3 months after first notify)
        // -------------------------------
        $secondBatch = Information::query()
            ->whereNull('result')
            ->where('result_missing_notify_count', 1)
            ->whereNotNull('result_missing_first_notified_at')
            ->where('result_missing_first_notified_at', '<=', $now->copy()->subMonths(3))
            ->orderBy('id')
            ->limit(500)
            ->get();

        foreach ($secondBatch as $info) {
            foreach ($recipients as $user) {
                NotificationService::record(
                    $user,
                    module: 'اطلاع',
                    action: 'missing_result_reminder',
                    title: 'یادآوری: نتیجه اطلاع هنوز ثبت نشده است',
                    message: '۳ ماه گذشت و هنوز نتیجه ندارد: ' . ($info->name ?? ('ID: ' . $info->id)),
                    routeName: 'informations',
                    routeParams: ['id' => $info->id],
                    level: 'warning',
                    changes: null
                );
            }

            // ✅ after this: STOP forever
            $info->update([
                'result_missing_last_notified_at' => $now,
                'result_missing_notify_count'     => 2,
            ]);
        }

        $this->info("Done. First sent: {$firstBatch->count()}, Second sent: {$secondBatch->count()}");

        return self::SUCCESS;
    }

    /**
     * ✅ Recipient selection (robust):
     * - If users table has 'is_admin' => notify admins
     * - Else if spatie roles exists => notify role('admin') or role('manager') if you want
     * - Else => notify ALL users (fallback)
     */
    private function resolveRecipients()
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        if (!class_exists($userModel)) {
            return collect();
        }

        $q = $userModel::query();

        // 1) If "users.is_admin" exists
        try {
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_admin')) {
                return $q->where('is_admin', 1)->get();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // 2) If Spatie roles available (role() method exists)
        try {
            if (method_exists($q->getModel(), 'roles') && method_exists($q, 'role')) {
                // Adjust roles as you like
                return $q->role(['admin', 'manager'])->get();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // 3) Fallback: notify everyone
        return $q->get();
    }
}
