<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return Conversation::where('id', $conversationId)
        ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
        ->exists();
});

// ✅ NEW: presence channel for realtime online/offline (no ping)
Broadcast::channel('presence.chat', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});