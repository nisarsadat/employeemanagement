<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemProfile extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'default_language',
        'admin_email',
        'phone',
        'address',
    ];
}
