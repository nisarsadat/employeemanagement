<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Record a notification for a single user.
     */
    public static function record(
        ?User $user,
        string $module,
        string $action,
        string $title,
        ?string $message = null,
        ?string $routeName = null,
        ?array $routeParams = null,
        string $level = 'info',
        ?array $changes = null,
        ?User $actor = null,  // 👈 optional actor (defaults to receiver)
    ): ?Notification {
        if (! $user) {
            return null;
        }

        $actor = $actor ?? $user;

        return Notification::create([
            'user_id'      => $user->id,         // receiver
            'actor_id'     => $actor?->id,       // actor
            'module'       => $module,
            'action'       => $action,
            'level'        => $level,
            'title'        => $title,
            'message'      => $message,
            'changes'      => $changes,
            'route_name'   => $routeName,
            'route_params' => $routeParams,
            'is_read'      => false,
            'read_at'      => null,
        ]);
    }

    /**
     * Record the same notification for many users at once.
     *
     * @param  iterable<User>  $users
     */
    public static function recordForUsers(
        iterable $users,
        string $module,
        string $action,
        string $title,
        ?string $message = null,
        ?string $routeName = null,
        ?array $routeParams = null,
        string $level = 'info',
        ?array $changes = null,
        ?User $actor = null,
    ): void {
        foreach ($users as $user) {
            if (! $user?->id) {
                continue;
            }

            Notification::create([
                'user_id'      => $user->id,       // receiver
                'actor_id'     => $actor?->id,     // actor (same for all)
                'module'       => $module,
                'action'       => $action,
                'level'        => $level,
                'title'        => $title,
                'message'      => $message,
                'changes'      => $changes,
                'route_name'   => $routeName,
                'route_params' => $routeParams,
                'is_read'      => false,
                'read_at'      => null,
            ]);
        }
    }

    /**
     * Notify ALL users in the system (including the actor).
     */
    public static function recordForAllUsers(
        User $actor,
        string $module,
        string $action,
        string $title,
        ?string $message = null,
        ?string $routeName = null,
        ?array $routeParams = null,
        string $level = 'info',
        ?array $changes = null,
    ): void {
        $users = User::query()->get();

        self::recordForUsers(
            users: $users,
            module: $module,
            action: $action,
            title: $title,
            message: $message,
            routeName: $routeName,
            routeParams: $routeParams,
            level: $level,
            changes: $changes,
            actor: $actor, // 👈 store actor_id
        );
    }

    /**
     * Notify all OTHER users except the actor (kept for future).
     */
    public static function recordForAllExceptActor(
        User $actor,
        string $module,
        string $action,
        string $title,
        ?string $message = null,
        ?string $routeName = null,
        ?array $routeParams = null,
        string $level = 'info',
        ?array $changes = null,
    ): void {
        $users = User::query()
            ->where('id', '!=', $actor->id)
            ->get();

        self::recordForUsers(
            users: $users,
            module: $module,
            action: $action,
            title: $title,
            message: $message,
            routeName: $routeName,
            routeParams: $routeParams,
            level: $level,
            changes: $changes,
            actor: $actor,
        );
    }
}
