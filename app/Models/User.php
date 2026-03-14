<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasRoles;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'username',
        'password',
        'image',
        'is_online',
        'last_seen_at',
        'last_login_at',
        'last_logout_at',
        'current_session_started_at',
        'last_session_duration_seconds',
        'total_login_duration_seconds',
        'today_login_duration_seconds',
        'today_login_duration_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'current_session_started_at' => 'datetime',
        'last_session_duration_seconds' => 'integer',
        'total_login_duration_seconds' => 'integer',
        'today_login_duration_seconds' => 'integer',
        'today_login_duration_date' => 'date',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }
}