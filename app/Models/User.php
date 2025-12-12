<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // ✅ REQUIRED

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasRoles; // ✅ ADD HasRoles

    protected $guard_name = 'web'; // ✅ IMPORTANT for Spatie

    protected $fillable = [
        'name',
        'username',
        'password',
        'image',
        'is_online',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_online'    => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }
}
