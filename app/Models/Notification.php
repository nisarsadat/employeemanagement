<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',     // receiver
        'actor_id',    // 👈 who did the action
        'module',
        'action',
        'level',
        'title',
        'message',
        'changes',
        'route_name',
        'route_params',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read'      => 'boolean',
        'read_at'      => 'datetime',
        'route_params' => 'array',
        'changes'      => 'array',
    ];

    // Receiver (notification owner)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Actor (who performed the action)
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
