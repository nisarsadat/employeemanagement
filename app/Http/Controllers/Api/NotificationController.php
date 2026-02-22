<?php

// app/Http/Controllers/Api/NotificationController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Events\NotificationRead;
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $perPage = (int) $request->input('perPage', 10); // 👈 max per page from FE (default 10)

        $query = Notification::where('user_id', $user->id)
            ->with('user') // 👈 eager-load user so Resource can access it
            ->latest();

        if ($request->boolean('only_unread')) {
            $query->where('is_read', false);
        }

        $notifications = $query->paginate($perPage);

        return NotificationResource::collection($notifications);
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

   public function markAsRead(Request $request, Notification $notification)
    {
        $user = $request->user();

        abort_if($notification->user_id !== $user->id, 403, 'Not allowed.');

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            // ✅ NEW: realtime decrement for other open tabs/devices
            event(new NotificationRead($user->id, $notification->id));
        }

        return new NotificationResource($notification->loadMissing('user'));
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $ids = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->pluck('id');

        Notification::whereIn('id', $ids)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        // ✅ NEW: broadcast each id (simple + correct)
        foreach ($ids as $id) {
            event(new NotificationRead($user->id, (int)$id));
        }

        return response()->json(['status' => 'ok']);
    }
    public function clearAll(Request $request)
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'cleared']);
    }
}

