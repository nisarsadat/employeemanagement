<?php

namespace App\Services;

use App\Models\User;

class UserSessionTracker
{
    public function login(User $user): void
    {
        $now = now();

        $todayStoredDate = $user->today_login_duration_date?->toDateString();
        $todaySeconds = (int) ($user->today_login_duration_seconds ?? 0);

        if ($todayStoredDate !== $now->toDateString()) {
            $todaySeconds = 0;
        }

        $user->forceFill([
            'is_online' => true,
            'last_seen_at' => $now,
            'last_login_at' => $now,
            'current_session_started_at' => $now,
            'today_login_duration_seconds' => $todaySeconds,
            'today_login_duration_date' => $now->toDateString(),
        ])->save();
    }

    public function heartbeat(User $user): void
    {
        $now = now();

        $todayStoredDate = $user->today_login_duration_date?->toDateString();
        $todaySeconds = (int) ($user->today_login_duration_seconds ?? 0);

        if ($todayStoredDate !== $now->toDateString()) {
            $todaySeconds = 0;
        }

        if (
            $user->is_online &&
            $user->current_session_started_at &&
            $user->current_session_started_at->toDateString() < $now->toDateString()
        ) {
            $todaySeconds = 0;
            $user->current_session_started_at = $now->copy()->startOfDay();
        }

        $user->forceFill([
            'last_seen_at' => $now,
            'today_login_duration_seconds' => $todaySeconds,
            'today_login_duration_date' => $now->toDateString(),
            'current_session_started_at' => $user->current_session_started_at,
        ])->save();
    }

    public function logout(User $user): void
    {
        $now = now();

        $sessionSeconds = 0;
        if ($user->current_session_started_at) {
            $sessionSeconds = max(0, $user->current_session_started_at->diffInSeconds($now));
        }

        $todayStoredDate = $user->today_login_duration_date?->toDateString();
        $todaySeconds = (int) ($user->today_login_duration_seconds ?? 0);

        if ($todayStoredDate !== $now->toDateString()) {
            $todaySeconds = 0;
        }

        if ($user->current_session_started_at) {
            if ($user->current_session_started_at->toDateString() === $now->toDateString()) {
                $todaySeconds += $sessionSeconds;
            } elseif ($user->current_session_started_at->toDateString() < $now->toDateString()) {
                $todaySeconds += $now->copy()->startOfDay()->diffInSeconds($now);
            }
        }

        $user->forceFill([
            'is_online' => false,
            'last_seen_at' => $now,
            'last_logout_at' => $now,
            'last_session_duration_seconds' => $sessionSeconds,
            'total_login_duration_seconds' => (int) ($user->total_login_duration_seconds ?? 0) + $sessionSeconds,
            'today_login_duration_seconds' => $todaySeconds,
            'today_login_duration_date' => $now->toDateString(),
            'current_session_started_at' => null,
        ])->save();
    }
}