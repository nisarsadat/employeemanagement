<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentSeconds = $this->currentLoginDurationSeconds();
        $lastSeconds = (int) ($this->last_session_duration_seconds ?? 0);
        $totalSeconds = (int) ($this->total_login_duration_seconds ?? 0) + $currentSeconds;
        $todaySeconds = $this->todayLoginDurationSeconds();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'image' => $this->image,
            'image_url' => $this->image_url,

            'is_online' => (bool) $this->is_online,
            'last_seen_at' => $this->last_seen_at,
            'last_login_at' => $this->last_login_at,
            'last_logout_at' => $this->last_logout_at,

            'roles' => $this->roles->pluck('name')->values(),

            'current_login_duration_seconds' => $currentSeconds,
            'last_session_duration_seconds' => $lastSeconds,
            'total_login_duration_seconds' => $totalSeconds,
            'today_login_duration_seconds' => $todaySeconds,

            'current_login_duration' => $this->is_online
                ? 'Online for ' . $this->formatDuration($currentSeconds)
                : '-',

            'last_session_duration' => 'Last session: ' . $this->formatDuration($lastSeconds),
            'total_login_duration' => 'Total: ' . $this->formatDuration($totalSeconds),
            'today_login_duration' => $this->formatDuration($todaySeconds),
        ];
    }

    private function currentLoginDurationSeconds(): int
    {
        if (!$this->is_online || !$this->current_session_started_at) {
            return 0;
        }

        return max(0, $this->current_session_started_at->diffInSeconds(now()));
    }

    private function todayLoginDurationSeconds(): int
    {
        $stored = (int) ($this->today_login_duration_seconds ?? 0);
        $storedDate = $this->today_login_duration_date?->toDateString();

        if ($storedDate !== now()->toDateString()) {
            $stored = 0;
        }

        if (!$this->is_online || !$this->current_session_started_at) {
            return $stored;
        }

        if ($this->current_session_started_at->toDateString() === now()->toDateString()) {
            return $stored + max(0, $this->current_session_started_at->diffInSeconds(now()));
        }

        if ($this->current_session_started_at->toDateString() < now()->toDateString()) {
            return $stored + now()->startOfDay()->diffInSeconds(now());
        }

        return $stored;
    }

    private function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}