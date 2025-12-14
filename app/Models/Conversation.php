<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Message;

class Conversation extends Model
{
    protected $fillable = [
        'type',
        'created_by',
    ];

    /**
     * Conversation participants (pivot: conversation_participants)
     */
    public function participants()
    {
        return $this->belongsToMany(
                User::class,
                'conversation_participants',   // pivot table
                'conversation_id',
                'user_id'
            )
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    /**
     * All messages in this conversation
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Last message (used for conversations list)
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
