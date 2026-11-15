<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformationCategory extends Model
{
    protected $fillable = [
        'information_category',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
    ];
}